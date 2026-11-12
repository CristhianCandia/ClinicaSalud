<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitaHistorial extends Model
{
    protected $table = 'cita_historial';
    protected $primaryKey = 'id_historial';
    public $timestamps = false;

    protected $fillable = [
        'id_cita', 'accion', 'estado_anterior', 'estado_nuevo',
        'fecha_anterior', 'hora_anterior', 'fecha_nueva', 'hora_nueva',
        'observacion', 'id_usuario'
    ];

    protected $casts = [
        'fecha_anterior' => 'date', 'hora_anterior' => 'datetime:H:i',
        'fecha_nueva' => 'date', 'hora_nueva' => 'datetime:H:i',
        'fecha_registro' => 'datetime'
    ];

    public function usuario() { return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario'); }
}