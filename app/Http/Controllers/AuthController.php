<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirigirSegunRol(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'identificador' => 'required|string',
            'contrasena' => 'required|string',
        ]);

        $usuario = Usuario::where('nombre_usuario', $request->identificador)
                          ->orWhere('correo', $request->identificador)
                          ->first();

        if (!$usuario || !Hash::check($request->contrasena, $usuario->contrasena)) {
            return back()
                ->withErrors(['identificador' => 'Usuario o contraseña incorrectos.'])
                ->withInput($request->only('identificador'));
        }

        if (!$usuario->estado) {
            return back()->withErrors(['identificador' => 'Tu cuenta está inactiva. Contacta al administrador.']);
        }

        Auth::login($usuario);
        $usuario->update(['ultimo_acceso' => now()]);

        return $this->redirigirSegunRol($usuario);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function redirigirSegunRol($usuario)
    {
        return match ($usuario->rol->nombre) {
            'ADMINISTRADOR' => redirect()->route('admin.dashboard'),
            'RECEPCIONISTA' => redirect()->route('recepcionista.dashboard'),
            'MEDICO' => redirect()->route('medico.dashboard'),
            'PACIENTE' => redirect()->route('paciente.dashboard'),
            default => redirect()->route('login')->withErrors(['identificador' => 'Rol no reconocido.']),
        };
    }
}