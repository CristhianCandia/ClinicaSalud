<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('especialidad')->insert([
            ['nombre' => 'Medicina General', 'descripcion' => 'Atención médica primaria'],
            ['nombre' => 'Pediatría', 'descripcion' => 'Atención a niños y adolescentes'],
            ['nombre' => 'Ginecología', 'descripcion' => 'Salud de la mujer'],
            ['nombre' => 'Odontología', 'descripcion' => 'Salud bucal'],
            ['nombre' => 'Cardiología', 'descripcion' => 'Enfermedades del corazón'],
        ]);

        DB::table('tipo_pago')->insert([
            ['nombre' => 'EFECTIVO', 'descripcion' => 'Pago en efectivo en ventanilla'],
            ['nombre' => 'TARJETA_POS', 'descripcion' => 'Pago con tarjeta de débito/crédito'],
            ['nombre' => 'TRANSFERENCIA', 'descripcion' => 'Transferencia bancaria'],
        ]);
    }
}
