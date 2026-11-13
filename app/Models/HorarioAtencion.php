<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Tabla "horario_atencion" del esquema v3 (no usa created_at ni updated_at).
class HorarioAtencion extends Model
{
    public const DIAS = [
        1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
        5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo',
    ];

    // Turnos fijos de la clínica: 6 horas cada uno
    public const TURNOS = [
        'MANANA' => ['nombre' => 'Turno mañana (07:00 – 13:00)', 'inicio' => '07:00', 'fin' => '13:00'],
        'TARDE' => ['nombre' => 'Turno tarde (13:00 – 19:00)', 'inicio' => '13:00', 'fin' => '19:00'],
    ];

    // Duración fija de cada cita
    public const DURACION_CITA_MIN = 15;

    protected $table = 'horario_atencion';
    protected $primaryKey = 'id_horario';
    public $timestamps = false;

    protected $fillable = ['id_medico', 'dia_semana', 'hora_inicio', 'hora_fin', 'duracion_cita_min', 'estado'];

    protected $casts = ['estado' => 'boolean'];

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'id_medico', 'id_medico');
    }

    // Cuántas citas completas caben en el bloque
    public function citasPorBloque(): int
    {
        $minutos = (strtotime($this->hora_fin) - strtotime($this->hora_inicio)) / 60;

        return intdiv((int) $minutos, (int) $this->duracion_cita_min);
    }
}