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
            // Agregar la columna vendedor_id después de cliente_id
            $table->unsignedBigInteger('vendedor_id')->nullable()->after('cliente_id');
            
            // Establecer la relación con la tabla users
            $table->foreign('vendedor_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null'); // Si se elimina el usuario, no eliminar la cotización
            
            // Agregar índice para mejorar performance en consultas
            $table->index('vendedor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            // Eliminar la foreign key constraint primero
            $table->dropForeign(['vendedor_id']);
            
            // Eliminar el índice
            $table->dropIndex(['vendedor_id']);
            
            // Eliminar la columna
            $table->dropColumn('vendedor_id');
        });
    }
};