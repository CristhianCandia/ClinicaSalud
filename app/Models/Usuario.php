<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    // 1. Le decimos a Laravel el nombre real de nuestra tabla
    protected $table = 'usuarios';
    
    // 2. La clave primaria
    protected $primaryKey = 'id';

    // 3. Campos que se pueden llenar masivamente
    protected $fillable = [
        'rol_id', 
        'nombre_usuario', 
        'correo', 
        'contrasena', 
        'estado',
        'ultimo_acceso',
    ];

    // 4. Campos que NUNCA se deben mostrar (por seguridad)
    protected $hidden = [
        'contrasena', 
    ];

    // 5. Convertir tipos de datos automáticamente
    protected $casts = [
        'estado' => 'boolean',
        'ultimo_acceso' => 'datetime',
    ];

    // 6. ⚠️ CLAVE: Le decimos a Laravel que el campo de contraseña es 'contrasena'
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    // 7. Relación con la tabla roles
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    // 8. Función auxiliar para verificar el rol fácilmente
    public function tieneRol($nombreRol)
    {
        return $this->rol && $this->rol->nombre === $nombreRol;
    }
}