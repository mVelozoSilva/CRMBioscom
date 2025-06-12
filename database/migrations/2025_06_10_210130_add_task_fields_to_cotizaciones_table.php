<?php
// database/migrations/2025_06_10_000001_add_task_fields_to_cotizaciones_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            // Tipo de cotización (enum stored as string for flexibility)
            $table->string('tipo_cotizacion')->default('COTIZACION_INFORMATIVA');
            
            // Modalidad de seguimiento 
            $table->string('modalidad_seguimiento')->default('REGULAR');
            
            // Días de seguimiento por colores
            $table->unsignedInteger('dias_seguimiento_verde')->default(1);
            $table->unsignedInteger('dias_seguimiento_amarillo')->default(3);
            $table->unsignedInteger('dias_seguimiento_rojo')->default(7);
            
            // Seguimiento personalizado
            $table->boolean('seguimiento_personalizado')->default(false);
            
            // Campos adicionales para configuración
            $table->json('configuracion_seguimiento')->nullable();
            $table->timestamp('proximo_seguimiento')->nullable();
            $table->unsignedInteger('intentos_seguimiento')->default(0);
            $table->unsignedInteger('max_intentos_seguimiento')->default(5);
            
            // Índices para mejorar performance
            $table->index(['tipo_cotizacion', 'modalidad_seguimiento']);
            $table->index(['proximo_seguimiento', 'modalidad_seguimiento']);
            $table->index(['seguimiento_personalizado', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropIndex(['tipo_cotizacion', 'modalidad_seguimiento']);
            $table->dropIndex(['proximo_seguimiento', 'modalidad_seguimiento']);
            $table->dropIndex(['seguimiento_personalizado', 'created_at']);
            
            $table->dropColumn([
                'tipo_cotizacion',
                'modalidad_seguimiento', 
                'dias_seguimiento_verde',
                'dias_seguimiento_amarillo',
                'dias_seguimiento_rojo',
                'seguimiento_personalizado',
                'configuracion_seguimiento',
                'proximo_seguimiento',
                'intentos_seguimiento',
                'max_intentos_seguimiento'
            ]);
        });
    }
};