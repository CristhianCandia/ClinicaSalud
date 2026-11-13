<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

// CU-05 (flujos 4 a 6): el paciente consulta y actualiza sus datos de contacto
class PacientePerfilController extends Controller
{
    public function edit(Request $request)
    {
        return view('paciente.perfil', [
            'paciente' => $this->pacienteActual($request),
        ]);
    }

    public function update(Request $request)
    {
        $paciente = $this->pacienteActual($request);
        $usuario = $request->user();

        $request->merge([
            'correo' => mb_strtolower(trim((string) $request->input('correo', ''))),
        ]);

        // El CI no se recibe: no se puede cambiar desde el perfil (CU-05, excepción)
        $datos = $request->validate([
            'telefono' => ['required', 'string', 'max:15'],
            'correo' => ['required', 'email', 'max:120',
                Rule::unique('usuario', 'correo')->ignore($usuario->id_usuario, 'id_usuario'),
                Rule::unique('paciente', 'correo')->ignore($paciente->id_paciente, 'id_paciente')],
            'direccion' => ['nullable', 'string', 'max:150'],
        ], [
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.max' => 'El teléfono no puede tener más de 15 caracteres.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'Ingrese un correo electrónico válido.',
            'correo.unique' => 'Ya existe una cuenta registrada con este correo.',
            'direccion.max' => 'La dirección no puede tener más de 150 caracteres.',
        ]);

        DB::transaction(function () use ($paciente, $usuario, $datos) {
            $paciente->forceFill([
                'telefono' => $datos['telefono'],
                'correo' => $datos['correo'],
                'direccion' => $datos['direccion'] ?? null,
                'fecha_actualizacion' => now(),
            ])->save();

            // El correo también es el usuario de acceso, por eso se mantiene sincronizado
            $usuario->forceFill(['correo' => $datos['correo']])->save();
        });

        return redirect()->route('paciente.perfil')
            ->with('exito', 'Datos personales actualizados.');
    }

    private function pacienteActual(Request $request): Paciente
    {
        return Paciente::where('id_usuario', $request->user()->id_usuario)->firstOrFail();
    }
}