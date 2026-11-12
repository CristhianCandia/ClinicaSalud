<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $table = 'paciente';
    protected $primaryKey = 'id_paciente';
    public $timestamps = false;

    protected $fillable = [
        'codigo', 'ci', 'nombres', 'apellidos', 'fecha_nacimiento', 'sexo',
        'telefono', 'correo', 'direccion', 'tipo_sangre', 'alergias',
        'enfermedades_cronicas', 'medicamentos', 'antecedentes', 'id_usuario', 'estado'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'estado' => 'boolean',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function usuario() {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function contactosEmergencia() {
        return $this->hasMany(ContactoEmergencia::class, 'id_paciente', 'id_paciente');
    }
}