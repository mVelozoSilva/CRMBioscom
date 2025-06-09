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
        Schema::create('seguimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('cotizacion_id')->nullable()->constrained('cotizaciones')->onDelete('set null');
            $table->foreignId('vendedor_id')->constrained('users')->onDelete('cascade');
            $table->enum('estado', ['pendiente', 'en_proceso', 'completado', 'vencido', 'reprogramado'])->default('pendiente');
            $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])->default('media');
            $table->date('ultima_gestion')->nullable();
            $table->date('proxima_gestion');
            $table->text('notas')->nullable();
            $table->text('resultado_ultima_gestion')->nullable();
            $table->timestamps();
            
            // Índices para optimizar consultas frecuentes
            $table->index(['vendedor_id', 'estado']);
            $table->index(['proxima_gestion', 'estado']);
            $table->index(['estado', 'prioridad']);
            $table->index('cliente_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguimientos');
    }
};