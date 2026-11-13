<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

// CU-11 Cambiar contraseña (cada usuario sobre su propia cuenta)
class CuentaController extends Controller
{
    private const LAYOUTS = [
        'ADMINISTRADOR' => 'layouts.admin',
        'RECEPCIONISTA' => 'layouts.recepcionista',
        'MEDICO' => 'layouts.medico',
        'PACIENTE' => 'layouts.paciente',
    ];

    public function index(Request $request)
    {
        $usuario = $request->user();

        return view('cuenta.index', [
            'layout' => self::LAYOUTS[$usuario->rol->nombre],
            'usuario' => $usuario,
        ]);
    }

    public function cambiarContrasena(Request $request)
    {
        $usuario = $request->user();

        // Paso 4: longitud mínima y confirmación
        $datos = $request->validate([
            'contrasena_actual' => ['required', 'string'],
            'contrasena_nueva' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'contrasena_actual.required' => 'Ingrese su contraseña actual.',
            'contrasena_nueva.required' => 'Ingrese la nueva contraseña.',
            'contrasena_nueva.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'contrasena_nueva.confirmed' => 'La confirmación no coincide con la nueva contraseña.',
        ]);

        // Paso 5: verificar la contraseña actual
        if (! Hash::check($datos['contrasena_actual'], $usuario->contrasena_hash)) {
            throw ValidationException::withMessages([
                'contrasena_actual' => 'La contraseña actual no es correcta.',
            ]);
        }

        // Paso 6: guardar el hash nuevo y registrar el cambio en la bitácora
        DB::transaction(function () use ($request, $usuario, $datos) {
            $usuario->forceFill([
                'contrasena_hash' => Hash::make($datos['contrasena_nueva']),
            ])->save();

            DB::table('bitacora')->insert([
                'id_usuario' => $usuario->id_usuario,
                'accion' => 'CAMBIAR_CONTRASENA',
                'tabla_afectada' => 'usuario',
                'detalle' => 'Cambio de contraseña de ' . $usuario->nombre_usuario,
                'ip' => substr((string) $request->ip(), 0, 45),
            ]);
        });

        // Se renueva la sesión para que no quede asociada a la contraseña anterior
        $request->session()->regenerate();

        return redirect()->route('cuenta.index')->with('exito', 'Contraseña actualizada.');
    }
}