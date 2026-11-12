<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $table = 'cita';
    protected $primaryKey = 'id_cita';
    public $timestamps = false;

    protected $fillable = [
        'codigo', 'id_paciente', 'id_medico', 'id_especialidad',
        'fecha', 'hora_inicio', 'hora_fin', 'motivo', 'estado',
        'motivo_cancelacion', 'observacion_consulta', 'id_usuario_registro',
        'id_usuario_actualiza', 'id_pago'
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function paciente() { return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente'); }
    public function medico() { return $this->belongsTo(Medico::class, 'id_medico', 'id_medico'); }
    public function especialidad() { return $this->belongsTo(Especialidad::class, 'id_especialidad', 'id_especialidad'); }
    public function usuarioRegistro() { return $this->belongsTo(Usuario::class, 'id_usuario_registro', 'id_usuario'); }
    public function historial() { return $this->hasMany(CitaHistorial::class, 'id_cita', 'id_cita')->orderBy('fecha_registro', 'desc'); }
}