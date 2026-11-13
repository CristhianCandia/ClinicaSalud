<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Tabla "especialidad" del esquema v3 (no usa created_at ni updated_at).
class Especialidad extends Model
{
    protected $table = 'especialidad';
    protected $primaryKey = 'id_especialidad';
    public $timestamps = false;

    protected $fillable = ['nombre', 'descripcion', 'estado'];

    protected $casts = [
        'estado' => 'boolean',
        'fecha_creacion' => 'datetime',
    ];
}