<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /**
     * Mostrar lista de usuarios (Flujo de Suceso Paso 1)
     */
    public function index()
    {
        $usuarios = Usuario::with('rol')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $roles = Rol::where('estado', true)->get();
        
        return view('admin.usuarios.index', compact('usuarios', 'roles'));
    }

    /**
     * Registrar nuevo usuario (Flujo de Suceso Pasos 2-6)
     */
    public function store(StoreUsuarioRequest $request)
    {
        Usuario::create([
            'rol_id' => $request->rol_id,
            'nombre_usuario' => $request->nombre_usuario,
            'correo' => $request->correo,
            'contrasena' => Hash::make($request->contrasena),
            'estado' => true,
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('exito', 'Usuario registrado correctamente.');
    }

    /**
     * Cargar datos del usuario en el formulario para editar (PASO 6)
     */
    public function edit(Usuario $usuario)
    {
        $usuarios = Usuario::with('rol')->orderBy('created_at', 'desc')->get();
        $roles = Rol::where('estado', true)->get();
        $usuarioEditando = $usuario;
        
        return view('admin.usuarios.index', compact('usuarios', 'roles', 'usuarioEditando'));
    }

    /**
     * Actualizar usuario existente
     */
    public function update(UpdateUsuarioRequest $request, Usuario $usuario)
    {
        $datos = [
            'rol_id' => $request->rol_id,
            'nombre_usuario' => $request->nombre_usuario,
            'correo' => $request->correo,
        ];

        // Solo actualizar contraseña si se proporcionó una nueva
        if ($request->filled('contrasena')) {
            $datos['contrasena'] = Hash::make($request->contrasena);
        }

        $usuario->update($datos);

        return redirect()->route('admin.usuarios.index')
            ->with('exito', 'Usuario actualizado correctamente.');
    }

    /**
     * Activar/Desactivar usuario (Excepción: no puede desactivarse a sí mismo)
     */
    public function toggleEstado(Usuario $usuario)
    {
        // Excepción: El administrador no puede desactivar su propia cuenta
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $usuario->update(['estado' => !$usuario->estado]);

        $accion = $usuario->estado ? 'activado' : 'desactivado';
        return back()->with('exito', "Usuario {$accion} correctamente.");
    }
}