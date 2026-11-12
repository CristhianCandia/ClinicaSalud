<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PacienteController extends Controller
{
    /**
     * Mostrar lista de pacientes con búsqueda
     */
    public function index(Request $request)
    {
        $query = DB::table('pacientes')
            ->select('pacientes.*', 'usuarios.correo', 'usuarios.estado as usuario_estado')
            ->leftJoin('usuarios', 'pacientes.usuario_id', '=', 'usuarios.id');

        // Búsqueda por CI, nombre o apellido
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('pacientes.ci', 'like', "%{$buscar}%")
                  ->orWhere('pacientes.nombres', 'like', "%{$buscar}%")
                  ->orWhere('pacientes.apellidos', 'like', "%{$buscar}%");
            });
        }

        $pacientes = $query->orderBy('pacientes.created_at', 'desc')->get();

        return view('recepcionista.pacientes.index', compact('pacientes'));
    }

    /**
     * Registrar nuevo paciente
     */
    public function store(Request $request)
    {
        // Validación con mensajes personalizados
        $request->validate([
            'ci' => 'required|string|max:15|unique:pacientes,ci',
            'nombres' => 'required|string|max:60',
            'apellidos' => 'required|string|max:60',
            'fecha_nacimiento' => 'required|date|before_or_equal:today',
            'sexo' => 'required|in:M,F,O',
            'telefono' => 'required|string|max:15',
            'correo' => 'required|email|max:120|unique:usuarios,correo',
            'direccion' => 'nullable|string|max:150',
            'tipo_sangre' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
        ], [
            'ci.unique' => 'Ya existe un paciente registrado con el CI: ' . $request->ci,
            'correo.unique' => 'Ya existe un usuario registrado con el correo: ' . $request->correo,
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento no es válida.',
            'ci.required' => 'El CI es obligatorio.',
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'sexo.required' => 'El sexo es obligatorio.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo no es válido.',
        ]);

        DB::transaction(function () use ($request) {
            // Crear usuario con rol PACIENTE
            $rolPaciente = DB::table('roles')->where('nombre', 'PACIENTE')->first();

            $usuarioId = DB::table('usuarios')->insertGetId([
                'rol_id' => $rolPaciente->id,
                'nombre_usuario' => $request->ci,
                'correo' => $request->correo,
                'contrasena' => Hash::make('paciente123'),
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Crear paciente (el código PAC-0001 se genera automáticamente por el trigger)
            DB::table('pacientes')->insert([
                'ci' => $request->ci,
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'sexo' => $request->sexo,
                'telefono' => $request->telefono,
                'correo' => $request->correo,
                'direccion' => $request->direccion,
                'tipo_sangre' => $request->tipo_sangre,
                'usuario_id' => $usuarioId,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('exito', 'Paciente registrado correctamente.');
    }

    /**
     * Cargar datos para editar
     */
    public function edit($id)
    {
        $paciente = DB::table('pacientes')
            ->leftJoin('usuarios', 'pacientes.usuario_id', '=', 'usuarios.id')
            ->select('pacientes.*', 'usuarios.correo', 'usuarios.estado as usuario_estado')
            ->where('pacientes.id', $id)
            ->first();

        $pacientes = DB::table('pacientes')
            ->leftJoin('usuarios', 'pacientes.usuario_id', '=', 'usuarios.id')
            ->select('pacientes.*', 'usuarios.correo', 'usuarios.estado as usuario_estado')
            ->orderBy('pacientes.created_at', 'desc')
            ->get();

        $pacienteEditando = $paciente;

        return view('recepcionista.pacientes.index', compact('pacientes', 'pacienteEditando'));
    }

    /**
     * Actualizar paciente
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'ci' => 'required|string|max:15|unique:pacientes,ci,' . $id,
            'nombres' => 'required|string|max:60',
            'apellidos' => 'required|string|max:60',
            'fecha_nacimiento' => 'required|date|before_or_equal:today',
            'sexo' => 'required|in:M,F,O',
            'telefono' => 'required|string|max:15',
            'correo' => 'required|email|max:120|unique:usuarios,correo,' . DB::table('pacientes')->where('id', $id)->value('usuario_id') . ',id',
            'direccion' => 'nullable|string|max:150',
            'tipo_sangre' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
        ], [
            'ci.unique' => 'Ya existe un paciente registrado con el CI: ' . $request->ci,
            'correo.unique' => 'Ya existe un usuario registrado con el correo: ' . $request->correo,
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
        ]);

        DB::transaction(function () use ($request, $id) {
            $paciente = DB::table('pacientes')->where('id', $id)->first();

            // Actualizar paciente
            DB::table('pacientes')->where('id', $id)->update([
                'ci' => $request->ci,
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'sexo' => $request->sexo,
                'telefono' => $request->telefono,
                'correo' => $request->correo,
                'direccion' => $request->direccion,
                'tipo_sangre' => $request->tipo_sangre,
                'updated_at' => now(),
            ]);

            // Actualizar usuario asociado
            if ($paciente->usuario_id) {
                DB::table('usuarios')->where('id', $paciente->usuario_id)->update([
                    'nombre_usuario' => $request->ci,
                    'correo' => $request->correo,
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('recepcionista.pacientes.index')
            ->with('exito', 'Paciente actualizado correctamente.');
    }

    /**
     * Activar/Desactivar paciente (sin eliminar)
     */
    public function toggleEstado($id)
    {
        $paciente = DB::table('pacientes')->where('id', $id)->first();

        if ($paciente->usuario_id) {
            $estadoActual = DB::table('usuarios')->where('id', $paciente->usuario_id)->value('estado');
            $nuevoEstado = !$estadoActual;

            DB::table('usuarios')->where('id', $paciente->usuario_id)->update([
                'estado' => $nuevoEstado,
                'updated_at' => now(),
            ]);

            DB::table('pacientes')->where('id', $id)->update([
                'estado' => $nuevoEstado,
                'updated_at' => now(),
            ]);

            $accion = $nuevoEstado ? 'activado' : 'desactivado';
            return back()->with('exito', "Paciente {$accion} correctamente.");
        }

        return back()->with('error', 'No se pudo cambiar el estado del paciente.');
    }
}