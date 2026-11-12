<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_usuario' => ['required', 'string', 'max:50', 'unique:usuario,nombre_usuario'],
            'correo' => ['required', 'email', 'max:120', 'unique:usuario,correo'],
            'contrasena' => ['required', 'string', 'min:8'],
            'id_rol' => ['required', 'exists:rol,id_rol'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_usuario.required' => 'El nombre de usuario es obligatorio.',
            'nombre_usuario.unique' => 'Ese nombre de usuario ya está registrado.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'El correo no es válido.',
            'correo.unique' => 'Ese correo ya está registrado.',
            'contrasena.required' => 'La contraseña es obligatoria.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'id_rol.required' => 'El rol es obligatorio.',
            'id_rol.exists' => 'El rol seleccionado no es válido.',
        ];
    }
}