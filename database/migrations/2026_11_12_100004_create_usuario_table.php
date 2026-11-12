<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $t) {
            $t->increments('id_usuario');
            $t->integer('id_rol');
            $t->string('nombre_usuario', 50)->unique();
            $t->string('correo', 120)->unique();
            $t->string('contrasena_hash', 255);
            $t->boolean('estado')->default(true);
            $t->timestampTz('fecha_creacion')->nullable()->useCurrent();
            $t->timestampTz('ultimo_acceso')->nullable();

            $t->foreign('id_rol')->references('id_rol')->on('rol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
