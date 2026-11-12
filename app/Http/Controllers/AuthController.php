<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

// CU-01 Iniciar sesión
class AuthController extends Controller
{
    public function mostrarLogin()
    {
        if (Auth::check()) {
            return redirect()->route('inicio');
        }

        return view('auth.login');
    }

    public function iniciarSesion(Request $request)
    {
        $login = trim((string) $request->input('login', ''));
        $password = (string) $request->input('password', '');

        // Flujo 3 y excepción "Campos vacíos"
        if ($login === '' || $password === '') {
            return back()
                ->withInput(['login' => $login])
                ->withErrors(['general' => 'Complete todos los campos.']);
        }

        // Flujo 4: buscar por nombre de usuario o correo. Solo cuentas activas.
        $texto = mb_strtolower($login);
        $usuario = Usuario::with('rol')
            ->where('estado', true)
            ->where(function ($q) use ($texto) {
                $q->whereRaw('LOWER(nombre_usuario) = ?', [$texto])
                  ->orWhereRaw('LOWER(correo) = ?', [$texto]);
            })
            ->first();

        // Excepción: usuario inexistente, contraseña incorrecta o cuenta inactiva.
        // Se usa el mismo mensaje en los tres casos para no revelar cuál falló.
        if (! $usuario || ! Hash::check($password, $usuario->contrasena_hash)) {
            return back()
                ->withInput(['login' => $login])
                ->withErrors(['general' => 'Usuario o contraseña incorrectos.']);
        }

        // Flujo 5: regenerar el identificador de sesión y guardar id, nombre y rol
        Auth::login($usuario);
        $request->session()->regenerate();
        session([
            'usuario_id' => $usuario->id_usuario,
            'nombre' => $usuario->nombre_usuario,
            'rol' => $usuario->rol->nombre,
        ]);

        $usuario->forceFill(['ultimo_acceso' => now()])->save();

        // Flujo 6: menú principal según el rol
        return redirect()->intended(route('inicio'));
    }

        public function inicio(Request $request)
    {
        $usuario = $request->user()->load('rol');

        // Cada rol con pantalla propia va a su dashboard
        if ($usuario->rol->nombre === 'ADMINISTRADOR') {
            return redirect()->route('admin.dashboard');
        }

        if ($usuario->rol->nombre === 'RECEPCIONISTA') {
            return redirect()->route('recepcionista.dashboard');
        }

        return view('inicio', [
            'usuario' => $usuario,
            'menu' => config('menus.' . $usuario->rol->nombre, []),
        ]);
    }

        public function cerrarSesion(Request $request)
    {
        // Flujo 2: elimina la sesión y sus variables
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Flujo 3: redirige al login con mensaje de confirmación
        return redirect()->route('login')
            ->with('exito', 'Sesión cerrada correctamente.');
    }
}