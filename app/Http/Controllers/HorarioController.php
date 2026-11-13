<?php

namespace App\Http\Controllers;

use App\Models\HorarioAtencion;
use App\Models\Medico;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

// CU-08 Gestionar horarios de atención
class HorarioController extends Controller
{
    public function index(Request $request)
    {
        return $this->vista($request, null);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request, null);

        HorarioAtencion::create($datos + ['estado' => true]);

        return redirect()->route('admin.horarios.index', ['medico' => $datos['id_medico']])
            ->with('exito', 'Horario guardado.');
    }

    public function edit(Request $request, $id)
    {
        return $this->vista($request, HorarioAtencion::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $horario = HorarioAtencion::findOrFail($id);
        $datos = $this->validar($request, $horario->id_horario);

        $horario->update($datos);

        return redirect()->route('admin.horarios.index', ['medico' => $horario->id_medico])
            ->with('exito', 'Horario actualizado.');
    }

    public function toggleEstado($id)
    {
        $horario = HorarioAtencion::findOrFail($id);
        $nuevoEstado = ! $horario->estado;

        // No se reactiva un bloque que ahora se cruza con otro activo del mismo día
        if ($nuevoEstado && $this->bloqueCruzado($horario->id_medico, $horario->dia_semana,
                $horario->hora_inicio, $horario->hora_fin, $horario->id_horario)) {
            return back()->with('error', 'No se puede activar: el bloque se cruza con otro activo del mismo día.');
        }

        $horario->update(['estado' => $nuevoEstado]);

        return back()->with('exito', $nuevoEstado ? 'Horario activado.' : 'Horario desactivado.');
    }

    private function vista(Request $request, ?HorarioAtencion $editando)
    {
        $idMedico = $editando?->id_medico ?? $request->input('medico');
        $medico = $idMedico ? Medico::find($idMedico) : null;

        $bloques = $medico
            ? HorarioAtencion::where('id_medico', $medico->id_medico)
                ->orderBy('dia_semana')->orderBy('hora_inicio')->get()
            : collect();

        return view('admin.horarios.index', [
            'medicos' => Medico::orderBy('apellidos')->orderBy('nombres')->get(),
            'medico' => $medico,
            'bloques' => $bloques,
            'editando' => $editando,
            'dias' => HorarioAtencion::DIAS,
            'turnos' => HorarioAtencion::TURNOS,
        ]);
    }

    // Convierte el turno elegido en horas fijas y aplica las validaciones
    private function validar(Request $request, ?int $idHorario): array
    {
        $entrada = $request->validate([
            'id_medico' => ['required', 'integer', 'exists:medico,id_medico'],
            'dia_semana' => ['required', 'integer', 'between:1,7'],
            'turno' => ['required', Rule::in(array_keys(HorarioAtencion::TURNOS))],
        ], [
            'id_medico.required' => 'Seleccione un médico.',
            'id_medico.exists' => 'El médico seleccionado no existe.',
            'dia_semana.required' => 'Seleccione un día.',
            'turno.required' => 'Seleccione un turno.',
            'turno.in' => 'Seleccione un turno válido.',
        ]);

        $turno = HorarioAtencion::TURNOS[$entrada['turno']];

        $datos = [
            'id_medico' => $entrada['id_medico'],
            'dia_semana' => $entrada['dia_semana'],
            'hora_inicio' => $turno['inicio'],
            'hora_fin' => $turno['fin'],
            'duracion_cita_min' => HorarioAtencion::DURACION_CITA_MIN,
        ];

        // Excepción: el bloque se cruza con otro del mismo día
        $cruce = $this->bloqueCruzado($datos['id_medico'], $datos['dia_semana'],
            $datos['hora_inicio'], $datos['hora_fin'], $idHorario);

        if ($cruce) {
            throw ValidationException::withMessages([
                'turno' => 'El horario se cruza con el bloque '
                    . HorarioAtencion::DIAS[$cruce->dia_semana] . ' '
                    . substr($cruce->hora_inicio, 0, 5) . ' – '
                    . substr($cruce->hora_fin, 0, 5) . ' ya registrado.',
            ]);
        }

        // Un médico atiende un solo turno por día
        $otroTurno = HorarioAtencion::where('id_medico', $datos['id_medico'])
            ->where('dia_semana', $datos['dia_semana'])
            ->where('estado', true)
            ->where('hora_inicio', '!=', $datos['hora_inicio'])
            ->when($idHorario, fn ($q) => $q->where('id_horario', '!=', $idHorario))
            ->exists();

        if ($otroTurno) {
            throw ValidationException::withMessages([
                'turno' => 'Este médico ya tiene otro turno ese día. Un médico atiende un solo turno por día.',
            ]);
        }

        return $datos;
    }

    // Dos bloques se cruzan si uno empieza antes de que termine el otro y termina después de que empieza
    private function bloqueCruzado(int $idMedico, int $dia, string $inicio, string $fin, ?int $ignorarId): ?HorarioAtencion
    {
        return HorarioAtencion::where('id_medico', $idMedico)
            ->where('dia_semana', $dia)
            ->where('estado', true)
            ->when($ignorarId, fn ($q) => $q->where('id_horario', '!=', $ignorarId))
            ->where('hora_inicio', '<', $fin)
            ->where('hora_fin', '>', $inicio)
            ->first();
    }
}