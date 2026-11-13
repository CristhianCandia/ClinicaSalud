<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use App\Models\HorarioAtencion;
use App\Models\Medico;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// CU-09 Consultar disponibilidad de citas (recepcionista y paciente)
class DisponibilidadController extends Controller
{
    public function index(Request $request)
    {
        $ahora = now('America/La_Paz');
        $hoy = $ahora->copy()->startOfDay();

        $especialidadId = $request->input('especialidad');
        $medicoId = $request->input('medico');
        $fechaTexto = $request->input('fecha');

        $especialidades = Especialidad::where('estado', true)->orderBy('nombre')->get();

        // Flujo 1: solo médicos activos que atienden la especialidad elegida
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
                // Excepción: fecha pasada (RN06)
                $error = 'No se pueden consultar fechas pasadas.';
            } else {
                [$libres, $error] = $this->horariosLibres($medico->id_medico, $fecha, $ahora);
            }
        }

        return view('disponibilidad.index', [
            'layout' => $request->user()->rol->nombre === 'PACIENTE' ? 'layouts.paciente' : 'layouts.recepcionista',
            'especialidades' => $especialidades,
            'medicos' => $medicos,
            'especialidadSeleccionada' => $especialidadId,
            'medicoSeleccionado' => $medico?->id_medico,
            'medico' => $medico,
            'fechaTexto' => $fechaTexto,
            'minFecha' => $hoy->toDateString(),
            'fecha' => $fecha,
            'libres' => $libres,
            'error' => $error,
            'dias' => HorarioAtencion::DIAS,
        ]);
    }

    // Devuelve [horas libres, mensaje de error o null]
    public function horariosLibres(int $idMedico, Carbon $fecha, Carbon $ahora): array
    {
        $fechaSql = $fecha->toDateString();

        // Excepción: fecha bloqueada por feriado, permiso, licencia o vacaciones (CU-14)
        $bloqueada = DB::table('bloqueo_agenda')
            ->where('estado', true)
            ->where(fn ($q) => $q->where('id_medico', $idMedico)->orWhereNull('id_medico'))
            ->where('fecha_inicio', '<=', $fechaSql)
            ->where('fecha_fin', '>=', $fechaSql)
            ->exists();

        if ($bloqueada) {
            return [[], 'El médico no atiende en esta fecha.'];
        }

        $horarios = HorarioAtencion::where('id_medico', $idMedico)
            ->where('dia_semana', $fecha->dayOfWeekIso)
            ->where('estado', true)
            ->get();

        // Citas no canceladas del médico en ese día (RN09)
        $ocupados = DB::table('cita')
            ->where('id_medico', $idMedico)
            ->where('fecha', $fechaSql)
            ->where('estado', '<>', 'CANCELADA')
            ->get(['hora_inicio', 'hora_fin'])
            ->map(fn ($c) => [
                'inicio' => Carbon::parse($fechaSql . ' ' . $c->hora_inicio, 'America/La_Paz'),
                'fin' => Carbon::parse($fechaSql . ' ' . $c->hora_fin, 'America/La_Paz'),
            ]);

        $libres = [];

        foreach ($horarios as $h) {
            $duracion = (int) $h->duracion_cita_min;
            $fin = Carbon::parse($fechaSql . ' ' . $h->hora_fin, 'America/La_Paz');

            // Paso 4: divide el horario en bloques según la duración de la cita
            for ($inicio = Carbon::parse($fechaSql . ' ' . $h->hora_inicio, 'America/La_Paz');
                 $inicio->copy()->addMinutes($duracion)->lte($fin);
                 $inicio->addMinutes($duracion)) {

                $bloqueFin = $inicio->copy()->addMinutes($duracion);

                // Los bloques que ya pasaron no se muestran
                if ($inicio->lte($ahora)) {
                    continue;
                }

                $ocupado = $ocupados->contains(
                    fn ($o) => $o['inicio']->lt($bloqueFin) && $o['fin']->gt($inicio)
                );

                if (! $ocupado) {
                    $libres[] = $inicio->format('H:i');
                }
            }
        }

        $libres = array_values(array_unique($libres));
        sort($libres);

        if (empty($libres)) {
            return [[], 'No hay horarios disponibles para esta fecha.'];
        }

        return [$libres, null];
    }
}