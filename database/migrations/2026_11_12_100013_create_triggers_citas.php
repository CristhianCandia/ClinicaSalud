<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Funciones y triggers de negocio (RF05-RF10, RN04-RN11).
// El SQL está en database/sql/triggers_citas.sql y es el mismo que se probó en PostgreSQL 16.
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(file_get_contents(database_path('sql/triggers_citas.sql')));
    }

    public function down(): void
    {
        DB::unprepared('
            DROP TRIGGER IF EXISTS trg_historial_cita ON cita;
            DROP TRIGGER IF EXISTS trg_auditoria_cita ON cita;
            DROP TRIGGER IF EXISTS trg_no_borrar_cita ON cita;
            DROP TRIGGER IF EXISTS trg_transicion_cita ON cita;
            DROP TRIGGER IF EXISTS trg_validar_cita ON cita;
            DROP FUNCTION IF EXISTS fn_historial_cita();
            DROP FUNCTION IF EXISTS fn_auditar_cambios();
            DROP FUNCTION IF EXISTS fn_bloquear_borrado_cita();
            DROP FUNCTION IF EXISTS fn_validar_transicion_cita();
            DROP FUNCTION IF EXISTS fn_validar_cita();
        ');
    }
};
