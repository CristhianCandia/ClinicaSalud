<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administrador', function (Blueprint $t) {
            $t->increments('id_administrador');
            $t->integer('id_usuario')->unique();
            $t->string('nombres', 60);
            $t->string('apellidos', 60);
            $t->string('telefono', 15)->nullable();
            $t->date('fecha_contratacion')->nullable();
            $t->boolean('estado')->default(true);
            $t->timestampTz('fecha_registro')->nullable()->useCurrent();

            $t->foreign('id_usuario')->references('id_usuario')->on('usuario');
        });

        Schema::create('recepcionista', function (Blueprint $t) {
            $t->increments('id_recepcionista');
            $t->integer('id_usuario')->unique();
            $t->string('nombres', 60);
            $t->string('apellidos', 60);
            $t->string('telefono', 15)->nullable();
            $t->date('fecha_contratacion')->nullable();
            $t->boolean('estado')->default(true);
            $t->timestampTz('fecha_registro')->nullable()->useCurrent();

            $t->foreign('id_usuario')->references('id_usuario')->on('usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recepcionista');
        Schema::dropIfExists('administrador');
    }
};
