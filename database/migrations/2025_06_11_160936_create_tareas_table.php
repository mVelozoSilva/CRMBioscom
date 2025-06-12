<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            
            // Información básica de la tarea
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            
            // Tipo y origen de la tarea
            $table->enum('tipo', [
                'seguimiento',
                'cotizacion', 
                'mantencion',
                'cobranza',
                'reunion',
                'llamada',
                'email',
                'visita',
                'administrativa',
                'personal'
            ])->default('administrativa');
            
            $table->enum('origen', [
                'manual',
                'automatica_seguimiento',
                'automatica_cotizacion',
                'automatica_triaje',
                'distribucion_masiva'
            ])->default('manual');
            
            // Referencias a otros módulos (nullable para flexibilidad)
            $table->foreignId('seguimiento_id')->nullable()->constrained('seguimientos')->onDelete('cascade');
            $table->foreignId('cotizacion_id')->nullable()->constrained('cotizaciones')->onDelete('cascade');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            
            // Fecha y tiempo
            $table->date('fecha_tarea');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->integer('duracion_estimada')->nullable(); // en minutos
            
            // Estado y prioridad
            $table->enum('estado', [
                'pendiente',
                'en_progreso', 
                'completada',
                'cancelada',
                'pospuesta'
            ])->default('pendiente');
            
            $table->enum('prioridad', [
                'baja',
                'media', 
                'alta',
                'urgente'
            ])->default('media');
            
            // Configuración de recordatorios
            $table->boolean('tiene_recordatorio')->default(false);
            $table->timestamp('recordatorio_en')->nullable();
            $table->enum('tipo_recordatorio', ['email', 'sistema', 'popup'])->nullable();
            
            // Metadatos para distribución automática
            $table->json('metadata_distribucion')->nullable(); // Para el algoritmo de distribución
            $table->boolean('es_distribuida_automaticamente')->default(false);
            $table->integer('intentos_completar')->default(0);
            
            // Resultados y notas
            $table->text('notas')->nullable();
            $table->text('resultado')->nullable();
            $table->timestamp('completada_en')->nullable();
            
            // Configuración por rol
            $table->json('configuracion_rol')->nullable(); // Para diferentes comportamientos por rol
            
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['usuario_id', 'fecha_tarea', 'estado']);
            $table->index(['tipo', 'estado']);
            $table->index(['origen', 'es_distribuida_automaticamente']);
            $table->index(['fecha_tarea', 'hora_inicio']);
            $table->index(['prioridad', 'estado']);
            $table->index(['cliente_id', 'fecha_tarea']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};