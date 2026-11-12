<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    protected $table = 'medico';
    protected $primaryKey = 'id_medico';
    public $timestamps = false;

    protected $fillable = [
        'codigo', 'registro_profesional', 'nombres', 'apellidos',
        'telefono', 'correo', 'id_usuario', 'estado'
    ];

    protected $casts = ['estado' => 'boolean', 'fecha_creacion' => 'datetime'];

    public function usuario() { return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario'); }
    
    // Relación muchos a muchos con Especialidad
    public function especialidades() {
        return $this->belongsToMany(Especialidad::class, 'medico_especialidad', 'id_medico', 'id_especialidad')
                    ->withPivot('fecha_asignacion')
                    ->withTimestamps(); // Ojo: si medico_especialidad no tiene timestamps, quita ->withTimestamps()
    }

    public function horarios() {
        return $this->hasMany(HorarioAtencion::class, 'id_medico', 'id_medico');
    }
}