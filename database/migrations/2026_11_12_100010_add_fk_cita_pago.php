<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// cita.id_pago y pago.id_cita se referencian entre sí; la FK se agrega después de crear ambas tablas.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cita', function (Blueprint $t) {
            $t->foreign('id_pago', 'fk_cita_pago')->references('id_pago')->on('pago');
        });
    }

    public function down(): void
    {
        Schema::table('cita', function (Blueprint $t) {
            $t->dropForeign('fk_cita_pago');
        });
    }
};
