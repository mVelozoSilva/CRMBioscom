<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            // Añade la nueva columna 'nombre_cotizacion'
            // La hacemos NO nullable si es requerida en el frontend,
            // o nullable() si puede estar en blanco.
            // Basado en tu frontend Vue, la tenemos como 'required'.
            $table->string('nombre_cotizacion')->nullable(false)->after('cliente_id'); // Puedes ajustar 'after'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            // Para revertir, simplemente elimina la columna
            $table->dropColumn('nombre_cotizacion');
        });
    }
};
