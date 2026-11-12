<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegistroController extends Controller
{
    /**
     * Mostrar formulario de registro
     */
    public function showRegistro()
    {
        return view('auth.registro');
    }

    /**
     * Procesar registro de paciente
     */
    public function store(Request $request)
    {
        // Validación
        $request->validate([
            'ci' => 'required|string|max:15|unique:pacientes,ci',
            'nombres' => 'required|string|max:60',
            'apellidos' => 'required|string|max:60',
            'fecha_nacimiento' => 'required|date|before_or_equal:today',
            'sexo' => 'required|in:M,F,O',
            'telefono' => 'required|string|max:15',
            'correo' => 'required|email|max:120|unique:usuarios,correo',
            'direccion' => 'nullable|string|max:150',
            'contrasena' => 'required|string|min:8|confirmed',
        ], [
            'ci.unique' => 'Ya existe un paciente registrado con el CI: ' . $request->ci,
            'correo.unique' => 'Ya existe una cuenta registrada con el correo: ' . $request->correo,
            'contrasena.confirmed' => 'Las contraseñas no coinciden.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
        ]);

        DB::transaction(function () use ($request) {
            // Crear usuario con rol PACIENTE
            $rolPaciente = DB::table('roles')->where('nombre', 'PACIENTE')->first();

            $usuarioId = DB::table('usuarios')->insertGetId([
                'rol_id' => $rolPaciente->id,
                'nombre_usuario' => $request->ci,
                'correo' => $request->correo,
                'contrasena' => Hash::make($request->contrasena),
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
                'usuario_id' => $usuarioId,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('login')
            ->with('exito', 'Cuenta creada correctamente. Ahora puedes iniciar sesión.');
    }
}