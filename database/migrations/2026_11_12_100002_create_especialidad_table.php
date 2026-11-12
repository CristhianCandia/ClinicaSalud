<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('especialidad', function (Blueprint $t) {
            $t->increments('id_especialidad');
            $t->string('nombre', 60);
            $t->string('descripcion', 200)->nullable();
            $t->boolean('estado')->default(true);
            $t->timestampTz('fecha_creacion')->nullable()->useCurrent();
        });

        // RF04: no se permiten dos especialidades con el mismo nombre normalizado
        DB::statement('CREATE UNIQUE INDEX ux_especialidad_nombre ON especialidad (LOWER(TRIM(nombre)))');
    }

    public function down(): void
    {
        Schema::dropIfExists('especialidad');
    }
};
