<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora', function (Blueprint $t) {
            $t->increments('id_bitacora');
            $t->integer('id_usuario')->nullable();
            $t->string('accion', 40);
            $t->string('tabla_afectada', 40)->nullable();
            $t->string('detalle', 300)->nullable();
            $t->string('ip', 45);
            $t->timestampTz('fecha_hora')->nullable()->useCurrent();

            $t->foreign('id_usuario')->references('id_usuario')->on('usuario');
            $t->index('fecha_hora', 'ix_bitacora_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};
