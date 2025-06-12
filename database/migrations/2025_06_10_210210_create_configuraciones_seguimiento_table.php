<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones_seguimiento', function (Blueprint $table) {
            $table->id();
            
            // Configuración por tipo de cotización
            $table->string('tipo_cotizacion');
            $table->string('tipo_cliente'); // 'publico', 'privado', 'revendedor'
            $table->string('modalidad_seguimiento');
            
            // Configuración de días
            $table->unsignedInteger('dias_verde')->default(1);
            $table->unsignedInteger('dias_amarillo')->default(3); 
            $table->unsignedInteger('dias_rojo')->default(7);
            
            // Configuración de intentos
            $table->unsignedInteger('max_intentos')->default(5);
            $table->unsignedInteger('dias_entre_intentos')->default(2);
            
            // Configuración avanzada
            $table->json('reglas_especiales')->nullable();
            $table->boolean('activo')->default(true);
            
            // Prioridad para triaje inteligente
            $table->unsignedInteger('prioridad_triaje')->default(50);
            
            // Metadatos
            $table->text('descripcion')->nullable();
            $table->timestamps();
            
            // Índices y constraints
            $table->unique(['tipo_cotizacion', 'tipo_cliente', 'modalidad_seguimiento'], 'unique_config_seguimiento');
            $table->index(['tipo_cotizacion', 'activo']);
            $table->index(['prioridad_triaje', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones_seguimiento');
    }
};