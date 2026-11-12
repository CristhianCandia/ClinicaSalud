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
            'nombre_usuario' => [
                'required',
                'string',
                'max:50',
                'unique:usuarios,nombre_usuario', // Excepción: Usuario duplicado
            ],
            'correo' => [
                'required',
                'email',
                'max:120',
                'unique:usuarios,correo', // Excepción: Correo duplicado
            ],
            'contrasena' => [
                'required',
                'string',
                'min:8', // Excepción: Contraseña menor a 8 caracteres
            ],
            'rol_id' => 'required|exists:roles,id',
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
            'rol_id.required' => 'El rol es obligatorio.',
            'rol_id.exists' => 'El rol seleccionado no es válido.',
        ];
    }
}
