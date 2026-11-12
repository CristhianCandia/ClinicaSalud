<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifa', function (Blueprint $t) {
            $t->increments('id_tarifa');
            $t->integer('id_especialidad');
            $t->string('tipo_consulta', 20);
            $t->string('descripcion', 100)->nullable();
            $t->decimal('monto', 10, 2);
            $t->boolean('estado')->default(true);
            $t->date('fecha_vigencia')->nullable()->default(DB::raw('CURRENT_DATE'));
            $t->timestampTz('fecha_creacion')->nullable()->useCurrent();

            $t->foreign('id_especialidad')->references('id_especialidad')->on('especialidad');
        });
        DB::statement("ALTER TABLE tarifa ADD CONSTRAINT ck_tarifa_tipo CHECK (tipo_consulta IN ('PRIMERA_VEZ','CONTROL','EMERGENCIA'))");
        DB::statement('ALTER TABLE tarifa ADD CONSTRAINT ck_tarifa_monto CHECK (monto > 0)');

        Schema::create('pago', function (Blueprint $t) {
            $t->increments('id_pago');
            $t->string('codigo', 10)->unique();
            $t->integer('id_paciente');
            $t->integer('id_cita')->nullable();
            $t->integer('id_tipo_pago');
            $t->decimal('monto_total', 10, 2);
            $t->decimal('monto_recibido', 10, 2);
            $t->decimal('monto_cambio', 10, 2)->default(0);
            $t->string('estado', 15)->default('COMPLETADO');
            $t->string('referencia', 50)->nullable();
            $t->integer('id_usuario_caja');
            $t->timestampTz('fecha_pago')->nullable()->useCurrent();

            $t->foreign('id_paciente')->references('id_paciente')->on('paciente');
            $t->foreign('id_cita')->references('id_cita')->on('cita');
            $t->foreign('id_tipo_pago')->references('id_tipo_pago')->on('tipo_pago');
            $t->foreign('id_usuario_caja')->references('id_usuario')->on('usuario');
            $t->index('fecha_pago', 'ix_pago_fecha');
        });
        DB::statement('ALTER TABLE pago ADD CONSTRAINT ck_pago_total CHECK (monto_total >= 0)');
        DB::statement('ALTER TABLE pago ADD CONSTRAINT ck_pago_recibido CHECK (monto_recibido >= 0)');
        DB::statement('ALTER TABLE pago ADD CONSTRAINT ck_pago_cambio CHECK (monto_cambio >= 0)');
        DB::statement("ALTER TABLE pago ADD CONSTRAINT ck_pago_estado CHECK (estado IN ('COMPLETADO','PENDIENTE','ANULADO'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('pago');
        Schema::dropIfExists('tarifa');
    }
};
