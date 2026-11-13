<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Tabla "cita" del esquema v3 (no usa created_at ni updated_at).
class Cita extends Model
{
    protected $table = 'cita';
    protected $primaryKey = 'id_cita';
    public $timestamps = false;

    protected $fillable = [
        'codigo', 'id_paciente', 'id_medico', 'id_especialidad', 'fecha',
        'hora_inicio', 'hora_fin', 'motivo', 'estado', 'id_usuario_registro',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_creacion' => 'datetime',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente');
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'id_medico', 'id_medico');
    }

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class, 'id_especialidad', 'id_especialidad');
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_registro', 'id_usuario');
    }
}