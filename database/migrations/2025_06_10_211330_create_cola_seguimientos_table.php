<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cola_seguimientos', function (Blueprint $table) {
            $table->id();
            
            // Referencia a la cotización
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->onDelete('cascade');
            
            // Programación del seguimiento
            $table->timestamp('programado_para');
            $table->string('estado')->default('pendiente'); // pendiente, procesando, completado, fallido
            $table->string('prioridad')->default('normal'); // alta, normal, baja
            
            // Información del triaje
            $table->json('metadata_triaje')->nullable();
            $table->string('tipo_accion')->nullable(); // email, llamada, visita, etc.
            
            // Control de procesamiento
            $table->boolean('procesado')->default(false);
            $table->timestamp('procesado_en')->nullable();
            $table->foreignId('procesado_por')->nullable()->constrained('users');
            
            // Resultado del seguimiento
            $table->text('notas')->nullable();
            $table->string('resultado')->nullable(); // contactado, no_contactado, cerrado, etc.
            
            $table->timestamps();
            
            // Índices para optimizar consultas del triaje
            $table->index(['programado_para', 'procesado']);
            $table->index(['prioridad', 'programado_para']);
            $table->index(['estado', 'programado_para']);
            $table->index(['cotizacion_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cola_seguimientos');
    }
};