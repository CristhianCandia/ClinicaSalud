<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerfilPacienteController extends Controller
{
    /**
     * Mostrar perfil del paciente
     */
    public function show()
    {
        $paciente = DB::table('pacientes')
            ->where('usuario_id', auth()->id())
            ->first();

        if (!$paciente) {
            return back()->with('error', 'No se encontró el perfil del paciente.');
        }

        return view('paciente.perfil', compact('paciente'));
    }

    /**
     * Actualizar perfil del paciente
     */
    public function update(Request $request)
    {
        $request->validate([
            'telefono' => 'required|string|max:15',
            'correo' => 'required|email|max:120|unique:usuarios,correo,' . auth()->id(),
            'direccion' => 'nullable|string|max:150',
        ], [
            'correo.unique' => 'Ya existe una cuenta registrada con el correo: ' . $request->correo,
        ]);

        DB::transaction(function () use ($request) {
            // Actualizar paciente
            DB::table('pacientes')
                ->where('usuario_id', auth()->id())
                ->update([
                    'telefono' => $request->telefono,
                    'correo' => $request->correo,
                    'direccion' => $request->direccion,
                    'updated_at' => now(),
                ]);

            // Actualizar usuario
            DB::table('usuarios')
                ->where('id', auth()->id())
                ->update([
                    'correo' => $request->correo,
                    'updated_at' => now(),
                ]);
        });

        return back()->with('exito', 'Datos personales actualizados correctamente.');
    }
}