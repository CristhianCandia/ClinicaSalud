<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // La clave primaria de usuario es id_usuario, no id
        $id = $this->route('usuario')->id_usuario;

        return [
            'nombre_usuario' => ['required', 'string', 'max:50', "unique:usuario,nombre_usuario,{$id},id_usuario"],
            'correo' => ['required', 'email', 'max:120', "unique:usuario,correo,{$id},id_usuario"],
            'contrasena' => ['nullable', 'string', 'min:8'],
            'id_rol' => ['required', 'exists:rol,id_rol'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_usuario.unique' => 'Ese nombre de usuario ya está registrado.',
            'correo.unique' => 'Ese correo ya está registrado.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'id_rol.exists' => 'El rol seleccionado no es válido.',
        ];
    }
}
