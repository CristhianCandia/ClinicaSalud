<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        // Los IDs son explícitos (1 a 4), así que la secuencia debe ajustarse después
        DB::table('rol')->insert([
            ['id_rol' => 1, 'nombre' => 'ADMINISTRADOR', 'descripcion' => 'Configura y supervisa el sistema'],
            ['id_rol' => 2, 'nombre' => 'RECEPCIONISTA', 'descripcion' => 'Gestiona pacientes y citas'],
            ['id_rol' => 3, 'nombre' => 'MEDICO', 'descripcion' => 'Consulta su agenda de atención'],
            ['id_rol' => 4, 'nombre' => 'PACIENTE', 'descripcion' => 'Gestiona su perfil y reserva sus citas'],
        ]);

        DB::select("SELECT setval(pg_get_serial_sequence('rol', 'id_rol'), (SELECT MAX(id_rol) FROM rol))");
    }
}
