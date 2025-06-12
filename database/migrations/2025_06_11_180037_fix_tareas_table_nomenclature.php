<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            // Renombrar columnas para seguir nomenclatura establecida
            $table->renameColumn('usuario_id', 'usuario_asignado_id');
            $table->renameColumn('fecha_tarea', 'fecha_vencimiento');
            $table->renameColumn('hora_inicio', 'hora_estimada');
            $table->renameColumn('duracion_estimada', 'duracion_estimada_minutos');
            $table->renameColumn('completada_en', 'fecha_completada');
            $table->renameColumn('metadata_distribucion', 'metadatos');
            
            // Agregar columna faltante para usuario creador
            $table->foreignId('usuario_creador_id')->after('usuario_asignado_id')
                  ->constrained('users')->onDelete('cascade');
            
            // Quitar campos que no están en la nomenclatura original
            $table->dropColumn([
                'hora_fin',
                'tiene_recordatorio', 
                'recordatorio_en',
                'tipo_recordatorio',
                'intentos_completar',
                'configuracion_rol'
            ]);
            
            // Ajustar enum de origen para coincidir con nomenclatura
            DB::statement("ALTER TABLE tareas MODIFY COLUMN origen ENUM(
                'manual', 
                'distribucion_masiva', 
                'distribucion_automatica', 
                'sistema', 
                'integracion', 
                'distribucion_masiva_fase1a'
            ) DEFAULT 'manual'");
            
            // Ajustar enum de tipo para incluir seguimiento como default
            DB::statement("ALTER TABLE tareas MODIFY COLUMN tipo ENUM(
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
            ) DEFAULT 'seguimiento'");
            
            // Agregar estado 'vencida' que faltaba
            DB::statement("ALTER TABLE tareas MODIFY COLUMN estado ENUM(
                'pendiente',
                'en_progreso', 
                'completada', 
                'cancelada', 
                'pospuesta',
                'vencida'
            ) DEFAULT 'pendiente'");
        });
    }

    public function down(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            // Revertir los cambios
            $table->renameColumn('usuario_asignado_id', 'usuario_id');
            $table->renameColumn('fecha_vencimiento', 'fecha_tarea');
            $table->renameColumn('hora_estimada', 'hora_inicio');
            $table->renameColumn('duracion_estimada_minutos', 'duracion_estimada');
            $table->renameColumn('fecha_completada', 'completada_en');
            $table->renameColumn('metadatos', 'metadata_distribucion');
            
            // Eliminar columna usuario_creador_id
            $table->dropForeign(['usuario_creador_id']);
            $table->dropColumn('usuario_creador_id');
            
            // Restaurar campos eliminados
            $table->time('hora_fin')->nullable();
            $table->boolean('tiene_recordatorio')->default(false);
            $table->timestamp('recordatorio_en')->nullable();
            $table->enum('tipo_recordatorio', ['email', 'sistema', 'popup'])->nullable();
            $table->integer('intentos_completar')->default(0);
            $table->longText('configuracion_rol')->nullable();
        });
    }
};