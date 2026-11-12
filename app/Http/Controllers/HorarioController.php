<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HorarioController extends Controller
{
    /**
     * Mostrar formulario y lista de horarios por médico
     */
    public function index(Request $request)
    {
        $medicoId = $request->input('medico_id');
        $medicoSeleccionado = null;
        $horarios = collect();

        if ($medicoId) {
            $medicoSeleccionado = DB::table('medicos')
                ->where('id', $medicoId)
                ->first();

            if ($medicoSeleccionado) {
                $horarios = DB::table('horarios_atencion')
                    ->where('medico_id', $medicoId)
                    ->orderBy('dia_semana')
                    ->orderBy('hora_inicio')
                    ->get();

                // Calcular citas por bloque
                foreach ($horarios as $horario) {
                    $duracionMinutos = $horario->duracion_cita_min;
                    $tiempoTotalMinutos = (strtotime($horario->hora_fin) - strtotime($horario->hora_inicio)) / 60;
                    $horario->citas_por_bloque = floor($tiempoTotalMinutos / $duracionMinutos);
                }
            }
        }

        // Obtener todos los médicos activos para el selector
        // Obtener todos los médicos para el selector
        $medicos = DB::table('medicos')
            ->orderBy('nombres')
            ->get();

        return view('admin.horarios.index', compact('medicos', 'medicoSeleccionado', 'horarios'));
    }

    /**
     * Registrar nuevo bloque de horario
     */
    public function store(Request $request)
    {
        $request->validate([
            'medico_id' => 'required|exists:medicos,id',
            'dia_semana' => 'required|integer|between:1,7',
            'hora_inicio' => 'required',
            'hora_fin' => 'required|after:hora_inicio',
            'duracion_cita_min' => 'required|integer|between:10,120',
        ], [
            'hora_fin.after' => 'La hora final debe ser posterior a la hora inicial.',
            'duracion_cita_min.between' => 'La duración debe estar entre 10 y 120 minutos.',
        ]);

        // Verificar que no haya superposición de horarios
        $superposicion = DB::table('horarios_atencion')
            ->where('medico_id', $request->medico_id)
            ->where('dia_semana', $request->dia_semana)
            ->where('estado', true)
            ->where(function($query) use ($request) {
                $query->where(function($q) use ($request) {
                    // El nuevo horario empieza dentro de un horario existente
                    $q->where('hora_inicio', '<=', $request->hora_inicio)
                      ->where('hora_fin', '>', $request->hora_inicio);
                })->orWhere(function($q) use ($request) {
                    // El nuevo horario termina dentro de un horario existente
                    $q->where('hora_inicio', '<', $request->hora_fin)
                      ->where('hora_fin', '>=', $request->hora_fin);
                })->orWhere(function($q) use ($request) {
                    // El nuevo horario envuelve completamente un horario existente
                    $q->where('hora_inicio', '>=', $request->hora_inicio)
                      ->where('hora_fin', '<=', $request->hora_fin);
                });
            })
            ->first();

        if ($superposicion) {
            $diaNombre = $this->getDiaNombre($superposicion->dia_semana);
            $horaInicio = date('H:i', strtotime($superposicion->hora_inicio));
            $horaFin = date('H:i', strtotime($superposicion->hora_fin));
            
            return back()
                ->withInput()
                ->with('error', "El horario se cruza con el bloque {$diaNombre} {$horaInicio} - {$horaFin} ya registrado.");
        }

        // Insertar horario
        DB::table('horarios_atencion')->insert([
            'medico_id' => $request->medico_id,
            'dia_semana' => $request->dia_semana,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'duracion_cita_min' => $request->duracion_cita_min,
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('exito', 'Bloque de horario registrado.');
    }

    /**
     * Cargar datos para editar
     */
    public function edit($id)
    {
        $horario = DB::table('horarios_atencion')
            ->where('id', $id)
            ->first();

        if (!$horario) {
            return back()->with('error', 'Horario no encontrado.');
        }

        $medicoId = $horario->medico_id;
        
        $medicoSeleccionado = DB::table('medicos')
            ->where('id', $medicoId)
            ->first();

        $horarios = DB::table('horarios_atencion')
            ->where('medico_id', $medicoId)
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();

        // Calcular citas por bloque
        foreach ($horarios as $h) {
            $duracionMinutos = $h->duracion_cita_min;
            $tiempoTotalMinutos = (strtotime($h->hora_fin) - strtotime($h->hora_inicio)) / 60;
            $h->citas_por_bloque = floor($tiempoTotalMinutos / $duracionMinutos);
        }

        $medicos = DB::table('medicos')
            ->where('estado', true)
            ->orderBy('nombres')
            ->get();

        $horarioEditando = $horario;

        return view('admin.horarios.index', compact('medicos', 'medicoSeleccionado', 'horarios', 'horarioEditando'));
    }

    /**
     * Actualizar horario
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'medico_id' => 'required|exists:medicos,id',
            'dia_semana' => 'required|integer|between:1,7',
            'hora_inicio' => 'required',
            'hora_fin' => 'required|after:hora_inicio',
            'duracion_cita_min' => 'required|integer|between:10,120',
        ]);

        $horarioActual = DB::table('horarios_atencion')->where('id', $id)->first();

        // Verificar superposición (excluyendo el horario actual)
        $superposicion = DB::table('horarios_atencion')
            ->where('medico_id', $request->medico_id)
            ->where('dia_semana', $request->dia_semana)
            ->where('id', '!=', $id)
            ->where('estado', true)
            ->where(function($query) use ($request) {
                $query->where(function($q) use ($request) {
                    $q->where('hora_inicio', '<=', $request->hora_inicio)
                      ->where('hora_fin', '>', $request->hora_inicio);
                })->orWhere(function($q) use ($request) {
                    $q->where('hora_inicio', '<', $request->hora_fin)
                      ->where('hora_fin', '>=', $request->hora_fin);
                })->orWhere(function($q) use ($request) {
                    $q->where('hora_inicio', '>=', $request->hora_inicio)
                      ->where('hora_fin', '<=', $request->hora_fin);
                });
            })
            ->first();

        if ($superposicion) {
            $diaNombre = $this->getDiaNombre($superposicion->dia_semana);
            $horaInicio = date('H:i', strtotime($superposicion->hora_inicio));
            $horaFin = date('H:i', strtotime($superposicion->hora_fin));
            
            return back()
                ->withInput()
                ->with('error', "El horario se cruza con el bloque {$diaNombre} {$horaInicio} - {$horaFin} ya registrado.");
        }

        DB::table('horarios_atencion')->where('id', $id)->update([
            'medico_id' => $request->medico_id,
            'dia_semana' => $request->dia_semana,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'duracion_cita_min' => $request->duracion_cita_min,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.horarios.index', ['medico_id' => $request->medico_id])
            ->with('exito', 'Horario actualizado.');
    }

    /**
     * Activar/Desactivar horario
     */
    public function toggleEstado($id)
    {
        $horario = DB::table('horarios_atencion')->where('id', $id)->first();

        if (!$horario) {
            return back()->with('error', 'Horario no encontrado.');
        }

        $nuevoEstado = !$horario->estado;

        DB::table('horarios_atencion')->where('id', $id)->update([
            'estado' => $nuevoEstado,
            'updated_at' => now(),
        ]);

        $accion = $nuevoEstado ? 'activado' : 'desactivado';
        return back()->with('exito', "Bloque de horario {$accion}.");
    }

    /**
     * Obtener nombre del día de la semana
     */
    private function getDiaNombre($numero)
    {
        $dias = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo'
        ];
        return $dias[$numero] ?? '';
    }
}