<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paciente', function (Blueprint $t) {
            $t->increments('id_paciente');
            $t->string('codigo', 10)->unique();
            $t->string('ci', 15)->unique();
            $t->string('nombres', 60);
            $t->string('apellidos', 60);
            $t->date('fecha_nacimiento');
            $t->char('sexo', 1);
            $t->string('telefono', 15);
            $t->string('correo', 120)->nullable();
            $t->string('direccion', 150)->nullable();
            $t->string('tipo_sangre', 3)->nullable();
            $t->text('alergias')->nullable();
            $t->text('enfermedades_cronicas')->nullable();
            $t->text('medicamentos')->nullable();
            $t->text('antecedentes')->nullable();
            $t->integer('id_usuario')->nullable()->unique();
            $t->boolean('estado')->default(true);
            $t->timestampTz('fecha_creacion')->nullable()->useCurrent();
            $t->timestampTz('fecha_actualizacion')->nullable();

            $t->foreign('id_usuario')->references('id_usuario')->on('usuario');
            $t->index(['apellidos', 'nombres'], 'ix_paciente_nombre');
        });

        DB::statement("ALTER TABLE paciente ADD CONSTRAINT ck_paciente_fecha_nacimiento CHECK (fecha_nacimiento <= CURRENT_DATE)");
        DB::statement("ALTER TABLE paciente ADD CONSTRAINT ck_paciente_sexo CHECK (sexo IN ('M','F','O'))");
        DB::statement("ALTER TABLE paciente ADD CONSTRAINT ck_paciente_tipo_sangre CHECK (tipo_sangre IN ('A+','A-','B+','B-','AB+','AB-','O+','O-'))");

        Schema::create('contacto_emergencia', function (Blueprint $t) {
            $t->increments('id_contacto');
            $t->integer('id_paciente');
            $t->string('nombre', 120);
            $t->string('parentesco', 30);
            $t->string('telefono', 15);
            $t->timestampTz('fecha_registro')->nullable()->useCurrent();

            $t->foreign('id_paciente')->references('id_paciente')->on('paciente')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacto_emergencia');
        Schema::dropIfExists('paciente');
    }
};
