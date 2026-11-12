<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

// CU-03 Gestionar usuarios
class UsuarioController extends Controller
{
    public function index()
    {
        return $this->vista();
    }

    public function store(StoreUsuarioRequest $request)
    {
        Usuario::create([
            'id_rol' => $request->id_rol,
            'nombre_usuario' => $request->nombre_usuario,
            'correo' => $request->correo,
            'contrasena_hash' => Hash::make($request->contrasena),
            'estado' => true,
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('exito', 'Usuario registrado correctamente.');
    }

    public function edit(Usuario $usuario)
    {
        return $this->vista(['usuarioEditando' => $usuario]);
    }

    public function update(UpdateUsuarioRequest $request, Usuario $usuario)
    {
        $datos = [
            'id_rol' => $request->id_rol,
            'nombre_usuario' => $request->nombre_usuario,
            'correo' => $request->correo,
        ];

        // Solo cambia la contraseña si se escribió una nueva
        if ($request->filled('contrasena')) {
            $datos['contrasena_hash'] = Hash::make($request->contrasena);
        }

        $usuario->update($datos);

        return redirect()->route('admin.usuarios.index')
            ->with('exito', 'Usuario actualizado correctamente.');
    }

    public function toggleEstado(Usuario $usuario)
    {
        // Excepción: el administrador no puede desactivar su propia cuenta
        if ($usuario->id_usuario === auth()->id()) {
            return back()->with('error', 'No puede desactivar su propia cuenta.');
        }

        $usuario->update(['estado' => ! $usuario->estado]);

        $accion = $usuario->estado ? 'activado' : 'desactivado';
        return back()->with('exito', "Usuario {$accion} correctamente.");
    }

    private function vista(array $extra = [])
    {
        return view('admin.usuarios.index', array_merge([
            'usuarios' => Usuario::with('rol')->orderByDesc('id_usuario')->get(),
            'roles' => Rol::orderBy('id_rol')->get(),
        ], $extra));
    }
}