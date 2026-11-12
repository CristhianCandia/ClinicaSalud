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
        $usuarioId = $this->route('usuario')->id;

        return [
            'nombre_usuario' => [
                'required',
                'string',
                'max:50',
                'unique:usuarios,nombre_usuario,' . $usuarioId,
            ],
            'correo' => [
                'required',
                'email',
                'max:120',
                'unique:usuarios,correo,' . $usuarioId,
            ],
            'contrasena' => [
                'nullable',
                'string',
                'min:8',
            ],
            'rol_id' => 'required|exists:roles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_usuario.unique' => 'Ese nombre de usuario ya está registrado.',
            'correo.unique' => 'Ese correo ya está registrado.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }
}
