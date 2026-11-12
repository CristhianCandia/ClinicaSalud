<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cita', function (Blueprint $t) {
            $t->increments('id_cita');
            $t->string('codigo', 10)->unique();
            $t->integer('id_paciente');
            $t->integer('id_medico');
            $t->integer('id_especialidad');
            $t->date('fecha');
            $t->time('hora_inicio');
            $t->time('hora_fin');
            $t->string('motivo', 200)->nullable();
            $t->string('estado', 15)->default('PROGRAMADA');
            $t->string('motivo_cancelacion', 200)->nullable();
            $t->string('observacion_consulta', 500)->nullable();
            $t->integer('id_usuario_registro');
            $t->integer('id_usuario_actualiza')->nullable();
            $t->integer('id_pago')->nullable();          // FK se agrega en la migración de pagos
            $t->timestampTz('fecha_creacion')->nullable()->useCurrent();
            $t->timestampTz('fecha_actualizacion')->nullable();

            $t->foreign('id_paciente')->references('id_paciente')->on('paciente');
            $t->foreign('id_medico')->references('id_medico')->on('medico');
            $t->foreign('id_especialidad')->references('id_especialidad')->on('especialidad');
            $t->foreign('id_usuario_registro')->references('id_usuario')->on('usuario');
            $t->foreign('id_usuario_actualiza')->references('id_usuario')->on('usuario');

            $t->index(['id_medico', 'fecha'], 'ix_cita_medico_fecha');
            $t->index('id_paciente', 'ix_cita_paciente');
            $t->index('estado', 'ix_cita_estado');
        });

        DB::statement('ALTER TABLE cita ADD CONSTRAINT ck_cita_rango CHECK (hora_fin > hora_inicio)');
        DB::statement("ALTER TABLE cita ADD CONSTRAINT ck_cita_estado CHECK (estado IN ('PROGRAMADA','CONFIRMADA','ATENDIDA','CANCELADA','INASISTENCIA'))");
        DB::statement('ALTER TABLE cita ADD CONSTRAINT fk_cita_medico_esp FOREIGN KEY (id_medico, id_especialidad) REFERENCES medico_especialidad(id_medico, id_especialidad)');
        // RF07 / RN07: un médico no puede tener citas activas superpuestas
        DB::statement("ALTER TABLE cita ADD CONSTRAINT ex_cita_superpuesta EXCLUDE USING gist (
            id_medico WITH =, tsrange(fecha + hora_inicio, fecha + hora_fin) WITH &&
        ) WHERE (estado <> 'CANCELADA')");

        Schema::create('cita_historial', function (Blueprint $t) {
            $t->increments('id_historial');
            $t->integer('id_cita');
            $t->string('accion', 20);
            $t->string('estado_anterior', 15)->nullable();
            $t->string('estado_nuevo', 15);
            $t->date('fecha_anterior')->nullable();
            $t->time('hora_anterior')->nullable();
            $t->date('fecha_nueva')->nullable();
            $t->time('hora_nueva')->nullable();
            $t->string('observacion', 200)->nullable();
            $t->integer('id_usuario');
            $t->timestampTz('fecha_registro')->nullable()->useCurrent();

            $t->foreign('id_cita')->references('id_cita')->on('cita')->onDelete('cascade');
            $t->foreign('id_usuario')->references('id_usuario')->on('usuario');
            $t->index('id_cita', 'ix_cita_historial_cita');
        });
        DB::statement("ALTER TABLE cita_historial ADD CONSTRAINT ck_historial_accion CHECK (accion IN ('REGISTRO','REPROGRAMACION','CONFIRMACION','ATENDIDA','CANCELACION','INASISTENCIA','OBSERVACION'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('cita_historial');
        Schema::dropIfExists('cita');
    }
};
