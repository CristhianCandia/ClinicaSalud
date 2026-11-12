<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recibo', function (Blueprint $t) {
            $t->increments('id_recibo');
            $t->string('codigo', 15)->unique();
            $t->integer('correlativo_fiscal')->unique();
            $t->integer('id_pago');
            $t->integer('id_paciente');
            $t->string('tipo_comprobante', 20);
            $t->string('nit_ci', 20)->nullable();
            $t->string('razon_social', 150)->nullable();
            $t->decimal('monto_total', 10, 2);
            $t->text('detalle')->nullable();
            $t->string('estado', 15)->default('EMITIDO');
            $t->string('motivo_anulacion', 200)->nullable();
            $t->integer('id_usuario');
            $t->timestampTz('fecha_emision')->nullable()->useCurrent();

            $t->foreign('id_pago')->references('id_pago')->on('pago');
            $t->foreign('id_paciente')->references('id_paciente')->on('paciente');
            $t->foreign('id_usuario')->references('id_usuario')->on('usuario');
        });
        DB::statement("ALTER TABLE recibo ADD CONSTRAINT ck_recibo_tipo CHECK (tipo_comprobante IN ('BOLETA','FACTURA','NOTA_CREDITO'))");
        DB::statement("ALTER TABLE recibo ADD CONSTRAINT ck_recibo_estado CHECK (estado IN ('EMITIDO','ANULADO'))");

        Schema::create('recibo_detalle', function (Blueprint $t) {
            $t->increments('id_detalle');
            $t->integer('id_recibo');
            $t->string('concepto', 100);
            $t->integer('cantidad')->default(1);
            $t->decimal('precio_unitario', 10, 2);
            $t->decimal('subtotal', 10, 2);

            $t->foreign('id_recibo')->references('id_recibo')->on('recibo')->onDelete('cascade');
        });
        DB::statement('ALTER TABLE recibo_detalle ADD CONSTRAINT ck_detalle_cantidad CHECK (cantidad > 0)');
        DB::statement('ALTER TABLE recibo_detalle ADD CONSTRAINT ck_detalle_precio CHECK (precio_unitario >= 0)');
        DB::statement('ALTER TABLE recibo_detalle ADD CONSTRAINT ck_detalle_subtotal CHECK (subtotal >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('recibo_detalle');
        Schema::dropIfExists('recibo');
    }
};
