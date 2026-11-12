<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

// Tabla "usuario" del esquema v3 (no usa id, created_at ni updated_at).
class Usuario extends Authenticatable
{
    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = ['id_rol', 'nombre_usuario', 'correo', 'contrasena_hash', 'estado'];
    protected $hidden = ['contrasena_hash'];
    protected $casts = [
        'estado' => 'boolean',
        'ultimo_acceso' => 'datetime',
    ];

    // Laravel toma la contraseña de este método al autenticar.
    public function getAuthPassword()
    {
        return $this->contrasena_hash;
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }
}