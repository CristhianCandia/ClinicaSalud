<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\HorarioAtencion;
use App\Models\Paciente;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// CU-15 Reprogramar cita médica (recepcionista y paciente)
class ReprogramarCitaController extends Controller
{
    private const LAYOUTS = [
        'RECEPCIONISTA' => 'layouts.recepcionista',
        'PACIENTE' => 'layouts.paciente',
    ];

    public function edit(Request $request, $id)
    {
        $usuario = $request->user();
        $cita = $this->citaAccesible($usuario, $id);

        if (! $this->reprogramable($cita)) {
            return redirect()->route('citas.comprobante', $cita->id_cita)
                ->with('error', 'Solo se pueden reprogramar citas programadas o confirmadas. Para otra cita, registre una nueva.');
        }

        $request->validate(['fecha' => ['nullable', 'date_format:Y-m-d']]);

        $ahora = now('America/La_Paz');
        $hoy = $ahora->copy()->startOfDay();
        $fechaTexto = $request->input('fecha');
        $fecha = null;
        $libres = [];
        $error = null;

        if ($fechaTexto) {
            $fecha = Carbon::createFromFormat('Y-m-d', $fechaTexto, 'America/La_Paz')->startOfDay();

            if ($fecha->lt($hoy)) {
                $error = 'No se pueden programar citas en fechas pasadas.';
            } else {
                [$libres, $error] = app(DisponibilidadController::class)
                    ->horariosLibres($cita->id_medico, $fecha, $ahora);
            }
        }

        return view('citas.reprogramar', [
            'layout' => self::LAYOUTS[$usuario->rol->nombre],
            'esPaciente' => $usuario->rol->nombre === 'PACIENTE',
            'cita' => $cita,
            'fecha' => $fecha,
            'fechaTexto' => $fechaTexto,
            'minFecha' => $hoy->toDateString(),
            'libres' => $libres,
            'error' => $error,
            'dias' => HorarioAtencion::DIAS,
        ]);
    }

    public function update(Request $request, $id)
    {
        $usuario = $request->user();
        $cita = $this->citaAccesible($usuario, $id);

        $datos = $request->validate([
            'fecha' => ['required', 'date_format:Y-m-d'],
            'hora' => ['required', 'date_format:H:i'],
            'observacion' => ['required', 'string', 'min:5', 'max:200'],
        ], [
            'fecha.required' => 'Seleccione una fecha.',
            'hora.required' => 'Seleccione un horario disponible.',
            'observacion.required' => 'Escriba una observación para la reprogramación.',
            'observacion.min' => 'La observación debe tener al menos 5 caracteres.',
            'observacion.max' => 'La observación no puede tener más de 200 caracteres.',
        ]);

        if (! $this->reprogramable($cita)) {
            return redirect()->route('citas.comprobante', $cita->id_cita)
                ->with('error', 'Solo se pueden reprogramar citas programadas o confirmadas.');
        }

        $ahora = now('America/La_Paz');
        $fecha = Carbon::createFromFormat('Y-m-d', $datos['fecha'], 'America/La_Paz')->startOfDay();

        if ($fecha->lt($ahora->copy()->startOfDay())) {
            return back()->withInput()->with('error', 'No se pueden programar citas en fechas pasadas.');
        }

        // RN13: se vuelve a verificar que el bloque siga libre
        [$libres] = app(DisponibilidadController::class)
            ->horariosLibres($cita->id_medico, $fecha, $ahora);

        if (! in_array($datos['hora'], $libres, true)) {
            return back()->withInput()->with('error', 'El horario seleccionado no está disponible.');
        }

        $horario = HorarioAtencion::where('id_medico', $cita->id_medico)
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
            $mensaje = DB::transaction(function () use ($cita, $fecha, $inicio, $fin, $datos, $usuario) {
                // Se bloquea la cita para que dos cambios simultáneos no se pisen
                $actual = Cita::lockForUpdate()->findOrFail($cita->id_cita);

                if (! $this->reprogramable($actual)) {
                    return 'Solo se pueden reprogramar citas programadas o confirmadas.';
                }

                $actual->forceFill([
                    'fecha' => $fecha->toDateString(),
                    'hora_inicio' => $inicio,
                    'hora_fin' => $fin,
                    'id_usuario_actualiza' => $usuario->id_usuario,
                    'fecha_actualizacion' => now('America/La_Paz'),
                ])->save();

                // El trigger ya registró el cambio como REPROGRAMACION;
                // la observación se guarda en ese mismo registro del historial
                DB::update(
                    "UPDATE cita_historial SET observacion = ?
                     WHERE id_historial = (
                         SELECT MAX(id_historial) FROM cita_historial
                         WHERE id_cita = ? AND accion = 'REPROGRAMACION'
                     )",
                    [mb_substr($datos['observacion'], 0, 200), $actual->id_cita]
                );

                return null;
            });
        } catch (QueryException $e) {
            // 23P01: EXCLUDE de citas superpuestas. 23514: reglas de los triggers
            if ($e->getCode() === '23P01') {
                return back()->withInput()->with('error', 'El horario seleccionado no está disponible.');
            }

            if ($e->getCode() === '23514' && preg_match('/ERROR:\s+([^\n]+)/', $e->getMessage(), $m)) {
                return back()->withInput()->with('error', trim($m[1]));
            }

            throw $e;
        }

        if ($mensaje) {
            return back()->withInput()->with('error', $mensaje);
        }

        return redirect()->route('citas.comprobante', $cita->id_cita)
            ->with('exito', "La cita {$cita->codigo} se reprogramó correctamente.");
    }

    private function citaAccesible($usuario, $id): Cita
    {
        $cita = Cita::findOrFail($id);

        // El paciente solo puede reprogramar sus propias citas
        if ($usuario->rol->nombre === 'PACIENTE') {
            $miFicha = Paciente::where('id_usuario', $usuario->id_usuario)->value('id_paciente');
            abort_unless((string) $cita->id_paciente === (string) $miFicha, 403, 'No puede modificar esta cita.');
        }

        return $cita;
    }

    private function reprogramable(Cita $cita): bool
    {
        // RF08: solo citas programadas o confirmadas
        return in_array($cita->estado, ['PROGRAMADA', 'CONFIRMADA'], true);
    }
}