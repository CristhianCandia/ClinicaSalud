<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medico', function (Blueprint $t) {
            $t->increments('id_medico');
            $t->string('codigo', 10)->unique();
            $t->string('registro_profesional', 20)->unique();
            $t->string('nombres', 60);
            $t->string('apellidos', 60);
            $t->string('telefono', 15);
            $t->string('correo', 120)->unique();
            $t->integer('id_usuario')->nullable()->unique();
            $t->boolean('estado')->default(true);
            $t->timestampTz('fecha_creacion')->nullable()->useCurrent();
            $t->timestampTz('fecha_actualizacion')->nullable();

            $t->foreign('id_usuario')->references('id_usuario')->on('usuario');
        });

        Schema::create('medico_especialidad', function (Blueprint $t) {
            $t->integer('id_medico');
            $t->integer('id_especialidad');
            $t->timestampTz('fecha_asignacion')->nullable()->useCurrent();

            $t->primary(['id_medico', 'id_especialidad']);
            $t->foreign('id_medico')->references('id_medico')->on('medico')->onDelete('cascade');
            $t->foreign('id_especialidad')->references('id_especialidad')->on('especialidad')->onDelete('cascade');
        });

        Schema::create('horario_atencion', function (Blueprint $t) {
            $t->increments('id_horario');
            $t->integer('id_medico');
            $t->smallInteger('dia_semana');          // 1 = Lunes ... 7 = Domingo
            $t->time('hora_inicio');
            $t->time('hora_fin');
            $t->smallInteger('duracion_cita_min')->default(30);
            $t->boolean('estado')->default(true);
            $t->timestampTz('fecha_creacion')->nullable()->useCurrent();

            $t->foreign('id_medico')->references('id_medico')->on('medico')->onDelete('cascade');
        });
        DB::statement('ALTER TABLE horario_atencion ADD CONSTRAINT ck_horario_dia CHECK (dia_semana BETWEEN 1 AND 7)');
        DB::statement('ALTER TABLE horario_atencion ADD CONSTRAINT ck_horario_duracion CHECK (duracion_cita_min BETWEEN 10 AND 120)');
        DB::statement('ALTER TABLE horario_atencion ADD CONSTRAINT ck_horario_rango CHECK (hora_fin > hora_inicio)');
        // RF05: no se permiten horarios superpuestos para un mismo médico y día
        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist;');
        DB::statement("ALTER TABLE horario_atencion ADD CONSTRAINT ex_horario_superpuesto EXCLUDE USING gist (
            id_medico WITH =, dia_semana WITH =,
            tsrange('2000-01-01'::date + hora_inicio, '2000-01-01'::date + hora_fin) WITH &&
        ) WHERE (estado)");

        Schema::create('bloqueo_agenda', function (Blueprint $t) {
            $t->increments('id_bloqueo');
            $t->integer('id_medico')->nullable();   // null solo para FERIADO global
            $t->string('tipo', 12);
            $t->date('fecha_inicio');
            $t->date('fecha_fin');
            $t->string('motivo', 150);
            $t->boolean('estado')->default(true);
            $t->integer('id_usuario_registro');
            $t->timestampTz('fecha_creacion')->nullable()->useCurrent();

            $t->foreign('id_medico')->references('id_medico')->on('medico')->onDelete('cascade');
            $t->foreign('id_usuario_registro')->references('id_usuario')->on('usuario');
            $t->index(['id_medico', 'fecha_inicio', 'fecha_fin'], 'ix_bloqueo_medico');
        });
        DB::statement("ALTER TABLE bloqueo_agenda ADD CONSTRAINT ck_bloqueo_tipo CHECK (tipo IN ('FERIADO','PERMISO','LICENCIA','VACACIONES'))");
        DB::statement('ALTER TABLE bloqueo_agenda ADD CONSTRAINT ck_bloqueo_rango CHECK (fecha_fin >= fecha_inicio)');
        DB::statement('ALTER TABLE bloqueo_agenda ADD CONSTRAINT ck_bloqueo_feriado CHECK (id_medico IS NOT NULL OR tipo = \'FERIADO\')');
    }

    public function down(): void
    {
        Schema::dropIfExists('bloqueo_agenda');
        Schema::dropIfExists('horario_atencion');
        Schema::dropIfExists('medico_especialidad');
        Schema::dropIfExists('medico');
    }
};
