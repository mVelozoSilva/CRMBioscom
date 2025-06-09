<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar la migración - Agregar campo estado a productos
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // Agregar campo estado después de la columna opcionales
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo')->after('opcionales');
        });
    }

    /**
     * Revertir la migración - Eliminar campo estado de productos
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};