<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

// CU-05 (flujos 1 a 3): el paciente crea su propia cuenta desde el login
class PacienteRegistroController extends Controller
{
    public function create()
    {
        return view('auth.registro');
    }

    public function store(Request $request)
    {
        $request->merge([
            'ci' => trim((string) $request->input('ci', '')),
            'correo' => mb_strtolower(trim((string) $request->input('correo', ''))),
        ]);

        $datos = $request->validate([
            'ci' => ['required', 'string', 'max:15',
                Rule::unique('paciente', 'ci'),
                Rule::unique('usuario', 'nombre_usuario')],
            'nombres' => ['required', 'string', 'max:60'],
            'apellidos' => ['required', 'string', 'max:60'],
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:today'],
            'sexo' => ['required', 'in:M,F,O'],
            'telefono' => ['required', 'string', 'max:15'],
            'correo' => ['required', 'email', 'max:120',
                Rule::unique('usuario', 'correo'),
                Rule::unique('paciente', 'correo')],
            'direccion' => ['nullable', 'string', 'max:150'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], $this->mensajes());

        DB::transaction(function () use ($datos) {
            $usuario = Usuario::create([
                'id_rol' => Rol::where('nombre', 'PACIENTE')->value('id_rol'),
                'nombre_usuario' => $datos['ci'],
                'correo' => $datos['correo'],
                'contrasena_hash' => Hash::make($datos['password']),
                'estado' => true,
            ]);

            // Mismo criterio de código que CU-04: PAC-0001, PAC-0002, ...
            $id = (int) DB::selectOne("SELECT nextval(pg_get_serial_sequence('paciente','id_paciente')) AS id")->id;

            $paciente = new Paciente();
            $paciente->forceFill([
                'id_paciente' => $id,
                'codigo' => 'PAC-' . str_pad($id, 4, '0', STR_PAD_LEFT),
                'ci' => $datos['ci'],
                'nombres' => $datos['nombres'],
                'apellidos' => $datos['apellidos'],
                'fecha_nacimiento' => $datos['fecha_nacimiento'],
                'sexo' => $datos['sexo'],
                'telefono' => $datos['telefono'],
                'correo' => $datos['correo'],
                'direccion' => $datos['direccion'] ?? null,
                'id_usuario' => $usuario->id_usuario,
                'estado' => true,
                'fecha_creacion' => now(),
            ])->save();
        });

        return redirect()->route('login')
            ->with('exito', 'Cuenta creada. Ya puede iniciar sesión con su CI o su correo electrónico.');
    }

    private function mensajes(): array
    {
        return [
            'ci.required' => 'El CI es obligatorio.',
            'ci.max' => 'El CI no puede tener más de 15 caracteres.',
            'ci.unique' => 'Ya existe una cuenta o ficha registrada con este CI.',
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'sexo.required' => 'Seleccione el sexo.',
            'sexo.in' => 'Seleccione un sexo válido.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'Ingrese un correo electrónico válido.',
            'correo.unique' => 'Ya existe una cuenta registrada con este correo.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}