<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol', function (Blueprint $t) {
            $t->increments('id_rol');
            $t->string('nombre', 30)->unique();
            $t->string('descripcion', 150)->nullable();
            $t->boolean('estado')->default(true);
            $t->timestampTz('fecha_creacion')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rol');
    }
};
