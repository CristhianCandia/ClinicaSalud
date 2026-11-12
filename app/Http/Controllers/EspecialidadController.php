<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EspecialidadController extends Controller
{
    /**
     * Mostrar lista de especialidades
     */
    public function index()
    {
        $especialidades = DB::table('especialidades')
            ->select('especialidades.*', 
                     DB::raw('(SELECT COUNT(*) FROM especialidad_medico WHERE especialidad_id = especialidades.id) as total_medicos'))
            ->orderBy('especialidades.nombre', 'asc')
            ->get();

        return view('admin.especialidades.index', compact('especialidades'));
    }

    /**
     * Registrar nueva especialidad
     */
    public function store(Request $request)
    {
        // Validación
        $request->validate([
            'nombre' => 'required|string|max:60',
            'descripcion' => 'nullable|string|max:200',
        ], [
            'nombre.required' => 'El nombre de la especialidad es obligatorio.',
        ]);

        // Normalizar nombre (minúsculas y sin espacios extras)
        $nombreNormalizado = strtolower(trim($request->nombre));

        // Verificar si ya existe (ignorando mayúsculas/minúsculas)
        $existe = DB::table('especialidades')
            ->whereRaw('LOWER(TRIM(nombre)) = ?', [$nombreNormalizado])
            ->first();

        if ($existe) {
            return back()
                ->withInput()
                ->with('error', 'La especialidad ya está registrada.');
        }

        // Insertar especialidad
        DB::table('especialidades')->insert([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('exito', 'Especialidad registrada.');
    }

    /**
     * Cargar datos para editar
     */
    public function edit($id)
    {
        $especialidad = DB::table('especialidades')->where('id', $id)->first();

        if (!$especialidad) {
            return back()->with('error', 'Especialidad no encontrada.');
        }

        $especialidades = DB::table('especialidades')
            ->select('especialidades.*', 
                     DB::raw('(SELECT COUNT(*) FROM especialidad_medico WHERE especialidad_id = especialidades.id) as total_medicos'))
            ->orderBy('especialidades.nombre', 'asc')
            ->get();

        $especialidadEditando = $especialidad;

        return view('admin.especialidades.index', compact('especialidades', 'especialidadEditando'));
    }

    /**
     * Actualizar especialidad
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:60',
            'descripcion' => 'nullable|string|max:200',
        ]);

        // Normalizar nombre
        $nombreNormalizado = strtolower(trim($request->nombre));

        // Verificar si existe otro registro con el mismo nombre (excluyendo el actual)
        $existe = DB::table('especialidades')
            ->where('id', '!=', $id)
            ->whereRaw('LOWER(TRIM(nombre)) = ?', [$nombreNormalizado])
            ->first();

        if ($existe) {
            return back()
                ->withInput()
                ->with('error', 'La especialidad ya está registrada.');
        }

        // Actualizar
        DB::table('especialidades')->where('id', $id)->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.especialidades.index')
            ->with('exito', 'Especialidad actualizada.');
    }

    /**
     * Activar/Desactivar especialidad
     */
    public function toggleEstado($id)
    {
        $especialidad = DB::table('especialidades')->where('id', $id)->first();

        if (!$especialidad) {
            return back()->with('error', 'Especialidad no encontrada.');
        }

        $nuevoEstado = !$especialidad->estado;

        DB::table('especialidades')->where('id', $id)->update([
            'estado' => $nuevoEstado,
            'updated_at' => now(),
        ]);

        $accion = $nuevoEstado ? 'activada' : 'desactivada';
        return back()->with('exito', "Especialidad {$accion}.");
    }
}