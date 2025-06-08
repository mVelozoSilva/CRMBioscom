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
        Schema::table('clientes', function (Blueprint $table) {
            // Añade la nueva columna 'nombre_contacto'
            // La hacemos nullable porque puede que no siempre se tenga un contacto principal.
            // Puedes ajustar 'after' según donde quieras que aparezca en tu DB.
            $table->string('nombre_contacto')->nullable()->after('nombre_institucion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Para revertir, simplemente elimina la columna
            $table->dropColumn('nombre_contacto');
        });
    }
};
