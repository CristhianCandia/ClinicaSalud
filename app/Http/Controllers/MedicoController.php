<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MedicoController extends Controller
{
    /**
     * Mostrar lista de médicos con búsqueda
     */
    public function index(Request $request)
{
    $query = DB::table('medicos')
        ->select('medicos.*', 'usuarios.nombre_usuario', 'usuarios.estado as usuario_estado')
        ->leftJoin('usuarios', 'medicos.usuario_id', '=', 'usuarios.id');

    // Búsqueda por nombre, apellido, código o registro profesional
    if ($request->filled('buscar')) {
        $buscar = $request->buscar;
        $query->where(function($q) use ($buscar) {
            $q->where('medicos.nombres', 'like', "%{$buscar}%")
              ->orWhere('medicos.apellidos', 'like', "%{$buscar}%")
              ->orWhere('medicos.codigo', 'like', "%{$buscar}%")
              ->orWhere('medicos.registro_profesional', 'like', "%{$buscar}%");
        });
    }

    // Filtrar por especialidad
    if ($request->filled('especialidad')) {
        $query->whereHas('especialidades', function($q) use ($request) {
            $q->where('especialidad_id', $request->especialidad);
        });
    }

    $medicos = $query->orderBy('medicos.created_at', 'desc')->get();

    // Obtener especialidades activas para el formulario y filtro
    $especialidades = DB::table('especialidades')
        ->where('estado', true)
        ->orderBy('nombre', 'asc')
        ->get();

    // Obtener usuarios con rol MÉDICO para vincular
    $usuariosMedico = DB::table('usuarios')
        ->join('roles', 'usuarios.rol_id', '=', 'roles.id')
        ->where('roles.nombre', 'MEDICO')
        ->whereNull('usuarios.deleted_at')
        ->select('usuarios.id', 'usuarios.nombre_usuario', 'usuarios.correo')
        ->get();

    // Obtener especialidades de cada médico
    foreach ($medicos as $medico) {
        $medico->especialidades = DB::table('especialidad_medico')
            ->join('especialidades', 'especialidad_medico.especialidad_id', '=', 'especialidades.id')
            ->where('especialidad_medico.medico_id', $medico->id)
            ->pluck('especialidades.nombre')
            ->implode(', ');
    }

    return view('admin.medicos.index', compact('medicos', 'especialidades', 'usuariosMedico'));
}

    /**
     * Registrar nuevo médico
     */
    public function store(Request $request)
    {
        // Validación
        $request->validate([
            'registro_profesional' => 'required|string|max:20|unique:medicos,registro_profesional',
            'nombres' => 'required|string|max:60',
            'apellidos' => 'required|string|max:60',
            'telefono' => 'required|string|max:15',
            'correo' => 'required|email|max:120|unique:medicos,correo',
            'especialidades' => 'required|array|min:1',
            'especialidades.*' => 'exists:especialidades,id',
            'usuario_id' => 'nullable|exists:usuarios,id',
        ], [
            'registro_profesional.unique' => 'Ya existe un médico con el registro profesional: ' . $request->registro_profesional,
            'correo.unique' => 'Ya existe un médico con el correo: ' . $request->correo,
            'especialidades.required' => 'Marque al menos una especialidad.',
            'especialidades.min' => 'Marque al menos una especialidad.',
        ]);

        DB::transaction(function () use ($request) {
            // Insertar médico (el código MED-0001 lo genera el trigger automáticamente)
            $medicoId = DB::table('medicos')->insertGetId([
                'registro_profesional' => $request->registro_profesional,
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'telefono' => $request->telefono,
                'correo' => $request->correo,
                'usuario_id' => $request->usuario_id ?: null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insertar especialidades del médico
            foreach ($request->especialidades as $especialidadId) {
                DB::table('especialidad_medico')->insert([
                    'medico_id' => $medicoId,
                    'especialidad_id' => $especialidadId,
                    'created_at' => now(),
                ]);
            }
        });

        return back()->with('exito', 'Médico registrado.');
    }

    /**
     * Cargar datos para editar
     */
    public function edit($id)
    {
        $medico = DB::table('medicos')
            ->leftJoin('usuarios', 'medicos.usuario_id', '=', 'usuarios.id')
            ->select('medicos.*', 'usuarios.nombre_usuario')
            ->where('medicos.id', $id)
            ->first();

        if (!$medico) {
            return back()->with('error', 'Médico no encontrado.');
        }

        // Obtener especialidades del médico
        $medico->especialidades_seleccionadas = DB::table('especialidad_medico')
            ->where('medico_id', $id)
            ->pluck('especialidad_id')
            ->toArray();

        $medicos = DB::table('medicos')
            ->select('medicos.*', 'usuarios.nombre_usuario', 'usuarios.estado as usuario_estado')
            ->leftJoin('usuarios', 'medicos.usuario_id', '=', 'usuarios.id')
            ->orderBy('medicos.created_at', 'desc')
            ->get();

        // Obtener especialidades de cada médico
        foreach ($medicos as $m) {
            $m->especialidades = DB::table('especialidad_medico')
                ->join('especialidades', 'especialidad_medico.especialidad_id', '=', 'especialidades.id')
                ->where('especialidad_medico.medico_id', $m->id)
                ->pluck('especialidades.nombre')
                ->implode(', ');
        }

        $especialidades = DB::table('especialidades')
            ->where('estado', true)
            ->orderBy('nombre', 'asc')
            ->get();

        $usuariosMedico = DB::table('usuarios')
            ->join('roles', 'usuarios.rol_id', '=', 'roles.id')
            ->where('roles.nombre', 'MEDICO')
            ->whereNull('usuarios.deleted_at')
            ->select('usuarios.id', 'usuarios.nombre_usuario', 'usuarios.correo')
            ->get();

        $medicoEditando = $medico;

        return view('admin.medicos.index', compact('medicos', 'especialidades', 'usuariosMedico', 'medicoEditando'));
    }

    /**
     * Actualizar médico
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'registro_profesional' => 'required|string|max:20|unique:medicos,registro_profesional,' . $id,
            'nombres' => 'required|string|max:60',
            'apellidos' => 'required|string|max:60',
            'telefono' => 'required|string|max:15',
            'correo' => 'required|email|max:120|unique:medicos,correo,' . $id,
            'especialidades' => 'required|array|min:1',
            'especialidades.*' => 'exists:especialidades,id',
            'usuario_id' => 'nullable|exists:usuarios,id',
        ], [
            'registro_profesional.unique' => 'Ya existe un médico con el registro profesional: ' . $request->registro_profesional,
            'correo.unique' => 'Ya existe un médico con el correo: ' . $request->correo,
            'especialidades.required' => 'Marque al menos una especialidad.',
            'especialidades.min' => 'Marque al menos una especialidad.',
        ]);

        DB::transaction(function () use ($request, $id) {
            // Actualizar médico
            DB::table('medicos')->where('id', $id)->update([
                'registro_profesional' => $request->registro_profesional,
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'telefono' => $request->telefono,
                'correo' => $request->correo,
                'usuario_id' => $request->usuario_id ?: null,
                'updated_at' => now(),
            ]);

            // Eliminar especialidades anteriores
            DB::table('especialidad_medico')->where('medico_id', $id)->delete();

            // Insertar nuevas especialidades
            foreach ($request->especialidades as $especialidadId) {
                DB::table('especialidad_medico')->insert([
                    'medico_id' => $id,
                    'especialidad_id' => $especialidadId,
                    'created_at' => now(),
                ]);
            }
        });

        return redirect()->route('admin.medicos.index')
            ->with('exito', 'Médico actualizado.');
    }

    /**
     * Activar/Desactivar médico
     */
    public function toggleEstado($id)
    {
        $medico = DB::table('medicos')->where('id', $id)->first();

        if (!$medico) {
            return back()->with('error', 'Médico no encontrado.');
        }

        $nuevoEstado = !$medico->estado;

        DB::table('medicos')->where('id', $id)->update([
            'estado' => $nuevoEstado,
            'updated_at' => now(),
        ]);

        // Si tiene usuario vinculado, actualizar también su estado
        if ($medico->usuario_id) {
            DB::table('usuarios')->where('id', $medico->usuario_id)->update([
                'estado' => $nuevoEstado,
                'updated_at' => now(),
            ]);
        }

        $accion = $nuevoEstado ? 'activado' : 'desactivado';
        return back()->with('exito', "Médico {$accion}.");
    }
}