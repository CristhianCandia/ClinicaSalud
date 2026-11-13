<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\HorarioAtencion;
use App\Models\Medico;
use App\Models\Paciente;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// CU-10 Programar cita médica (recepcionista y paciente)
class ProgramarCitaController extends Controller
{
    public function create(Request $request)
    {
        $usuario = $request->user();
        $esPaciente = $usuario->rol->nombre === 'PACIENTE';
        $ahora = now('America/La_Paz');
        $hoy = $ahora->copy()->startOfDay();

        $especialidadId = $request->input('especialidad');
        $medicoId = $request->input('medico');
        $fechaTexto = $request->input('fecha');

        $medicos = $especialidadId
            ? Medico::where('estado', true)
                ->whereHas('especialidades', fn ($q) => $q->where('especialidad.id_especialidad', $especialidadId))
                ->orderBy('apellidos')->orderBy('nombres')->get()
            : collect();

        $medico = $medicoId ? $medicos->firstWhere('id_medico', (int) $medicoId) : null;

        $fecha = null;
        $libres = [];
        $error = null;

        if ($medico && $fechaTexto) {
            $fecha = Carbon::createFromFormat('Y-m-d', $fechaTexto, 'America/La_Paz')->startOfDay();

            if ($fecha->lt($hoy)) {
                // RN06: no se programan citas en fechas pasadas
                $error = 'No se pueden programar citas en fechas pasadas.';
            } else {
                [$libres, $error] = app(DisponibilidadController::class)
                    ->horariosLibres($medico->id_medico, $fecha, $ahora);
            }
        }

        return view('citas.programar', [
            'layout' => $esPaciente ? 'layouts.paciente' : 'layouts.recepcionista',
            'esPaciente' => $esPaciente,
            'pacienteFijo' => $esPaciente ? Paciente::where('id_usuario', $usuario->id_usuario)->first() : null,
            'pacientes' => $esPaciente ? collect() : Paciente::where('estado', true)
                ->orderBy('apellidos')->orderBy('nombres')->get(),
            'pacienteSeleccionado' => $request->input('paciente'),
            'especialidades' => Especialidad::where('estado', true)->orderBy('nombre')->get(),
            'especialidadSeleccionada' => $especialidadId,
            'medicos' => $medicos,
            'medico' => $medico,
            'fecha' => $fecha,
            'fechaTexto' => $fechaTexto,
            'minFecha' => $hoy->toDateString(),
            'libres' => $libres,
            'error' => $error,
            'dias' => HorarioAtencion::DIAS,
        ]);
    }

    public function store(Request $request)
    {
        $usuario = $request->user();
        $esPaciente = $usuario->rol->nombre === 'PACIENTE';

        $datos = $request->validate([
            'paciente' => [$esPaciente ? 'nullable' : 'required', 'integer', 'exists:paciente,id_paciente'],
            'id_especialidad' => ['required', 'integer', 'exists:especialidad,id_especialidad'],
            'id_medico' => ['required', 'integer', 'exists:medico,id_medico'],
            'fecha' => ['required', 'date_format:Y-m-d'],
            'hora' => ['required', 'date_format:H:i'],
            'motivo' => ['nullable', 'string', 'max:200'],
        ], [
            'paciente.required' => 'Seleccione un paciente.',
            'id_especialidad.required' => 'Seleccione una especialidad.',
            'id_medico.required' => 'Seleccione un médico.',
            'fecha.required' => 'Seleccione una fecha.',
            'hora.required' => 'Seleccione un horario disponible.',
            'motivo.max' => 'El motivo no puede tener más de 200 caracteres.',
        ]);

        // Flujo 1: el paciente usa su propia ficha; la recepcionista la elige
        $paciente = $esPaciente
            ? Paciente::where('id_usuario', $usuario->id_usuario)->first()
            : Paciente::find($datos['paciente']);

        if (! $paciente) {
            return back()->withInput()->with('error', 'Su cuenta no tiene una ficha de paciente.');
        }

        if (! $paciente->estado) {
            return back()->withInput()->with('error', 'El paciente no está activo.');
        }

        $atiende = DB::table('medico_especialidad')
            ->where('id_medico', $datos['id_medico'])
            ->where('id_especialidad', $datos['id_especialidad'])
            ->exists();

        if (! $atiende) {
            return back()->withInput()->with('error', 'El médico no atiende esa especialidad.');
        }

        $ahora = now('America/La_Paz');
        $fecha = Carbon::createFromFormat('Y-m-d', $datos['fecha'], 'America/La_Paz')->startOfDay();

        if ($fecha->lt($ahora->copy()->startOfDay())) {
            return back()->withInput()->with('error', 'No se pueden programar citas en fechas pasadas.');
        }

        // Flujo 5: se vuelve a verificar que el bloque siga libre
        [$libres, $error] = app(DisponibilidadController::class)
            ->horariosLibres($datos['id_medico'], $fecha, $ahora);

        if (! in_array($datos['hora'], $libres, true)) {
            return back()->withInput()->with('error', $error ?? 'El horario seleccionado no está disponible.');
        }

        $horario = HorarioAtencion::where('id_medico', $datos['id_medico'])
            ->where('dia_semana', $fecha->dayOfWeekIso)
            ->where('estado', true)
            ->where('hora_inicio', '<=', $datos['hora'])
            ->where('hora_fin', '>', $datos['hora'])
            ->firstOrFail();

        $inicio = $datos['hora'];
        $fin = Carbon::createFromFormat('H:i', $datos['hora'])
            ->addMinutes($horario->duracion_cita_min)
            ->format('H:i');

        try {
            $cita = DB::transaction(function () use ($datos, $paciente, $fecha, $inicio, $fin, $usuario) {
                // Flujo 6: código CM-0001, CM-0002, ... (mismo criterio que los demás módulos)
                $id = (int) DB::selectOne("SELECT nextval(pg_get_serial_sequence('cita','id_cita')) AS id")->id;

                $cita = new Cita();
                $cita->forceFill([
                    'id_cita' => $id,
                    'codigo' => 'CM-' . str_pad($id, 4, '0', STR_PAD_LEFT),
                    'id_paciente' => $paciente->id_paciente,
                    'id_medico' => $datos['id_medico'],
                    'id_especialidad' => $datos['id_especialidad'],
                    'fecha' => $fecha->toDateString(),
                    'hora_inicio' => $inicio,
                    'hora_fin' => $fin,
                    'motivo' => $datos['motivo'] ?: 'Consulta general',
                    'estado' => 'PROGRAMADA',
                    'id_usuario_registro' => $usuario->id_usuario,
                ])->save();

                return $cita;
            });
        } catch (QueryException $e) {
            // 23P01: EXCLUDE de citas superpuestas. 23514: reglas de los triggers
            if (in_array($e->getCode(), ['23P01', '23514'], true)) {
                $mensaje = 'El horario seleccionado no está disponible.';

                if ($e->getCode() === '23514' && preg_match('/ERROR:\s+([^\n]+)/', $e->getMessage(), $m)) {
                    $mensaje = trim($m[1]);
                }

                return back()->withInput()->with('error', $mensaje);
            }

            throw $e;
        }

        return redirect()->route('citas.comprobante', $cita->id_cita)
            ->with('exito', "La cita {$cita->codigo} se registró correctamente.");
    }

    public function comprobante(Request $request, $id)
    {
        $usuario = $request->user();
        $esPaciente = $usuario->rol->nombre === 'PACIENTE';

        $cita = Cita::with(['paciente', 'medico', 'especialidad', 'registrador'])->findOrFail($id);

        // El paciente solo puede ver sus propias citas
        if ($esPaciente) {
            $miFicha = Paciente::where('id_usuario', $usuario->id_usuario)->value('id_paciente');
            abort_unless((string) $cita->id_paciente === (string) $miFicha, 403, 'No puede ver esta cita.');
        }

        $historial = DB::table('cita_historial as h')
            ->join('usuario as u', 'u.id_usuario', '=', 'h.id_usuario')
            ->where('h.id_cita', $cita->id_cita)
            ->orderBy('h.id_historial')
            ->get(['h.fecha_registro', 'h.accion', 'h.estado_nuevo', 'h.fecha_nueva',
                'h.hora_nueva', 'h.observacion', 'u.nombre_usuario']);

        return view('citas.comprobante', [
            'layout' => $esPaciente ? 'layouts.paciente' : 'layouts.recepcionista',
            'cita' => $cita,
            'historial' => $historial,
            'dias' => HorarioAtencion::DIAS,
        ]);
    }
}