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
        Schema::create('cobranzas', function (Blueprint $table) {
            $table->id();
            
            // Identificadores únicos de factura/gestión
            $table->string('id_factura')->unique()->comment('ID único de la gestión/factura');
            $table->string('numero_factura')->index()->comment('Número de factura');
            
            // Relaciones
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('usuario_asignado_id')->constrained('users')->onDelete('cascade')->comment('Usuario encargado de la cobranza');
            
            // Información financiera
            $table->decimal('monto_adeudado', 12, 2)->comment('Monto pendiente de pago');
            $table->decimal('monto_original', 12, 2)->nullable()->comment('Monto original de la factura');
            $table->decimal('monto_pagado', 12, 2)->default(0)->comment('Monto ya pagado');
            
            // Fechas clave
            $table->date('fecha_emision')->comment('Fecha de emisión de la factura');
            $table->date('fecha_vencimiento')->index()->comment('Fecha de vencimiento de pago');
            $table->datetime('ultima_gestion')->nullable()->comment('Fecha y hora de la última gestión realizada');
            $table->date('proxima_gestion')->nullable()->index()->comment('Fecha programada para próxima gestión');
            
            // Estado y control
            $table->enum('estado', [
                'pendiente',
                'en_gestion', 
                'pagada',
                'vencida',
                'parcialmente_pagada',
                'en_disputa',
                'incobrable',
                'renegociada'
            ])->default('pendiente')->index()->comment('Estado actual de la cobranza');
            
            $table->enum('prioridad', [
                'baja',
                'media', 
                'alta',
                'urgente'
            ])->default('media')->comment('Prioridad de gestión');
            
            // Información adicional de órdenes
            $table->string('numero_orden_transporte')->nullable()->comment('Número de orden de transporte');
            $table->string('numero_orden_compra')->nullable()->comment('Número de orden de compra del cliente');
            
            // Gestión y seguimiento
           
            $table->integer('intentos_gestion')->default(0)->comment('Número de intentos de gestión realizados');
            $table->integer('max_intentos_gestion')->default(5)->comment('Máximo número de intentos permitidos');
            
            // Notas e historial
            $table->text('notas')->nullable()->comment('Notas adicionales sobre la cobranza');
            $table->json('historial_interacciones')->nullable()->comment('Historial detallado de todas las interacciones');
            $table->text('observaciones_cliente')->nullable()->comment('Observaciones específicas del cliente');
            
            // Información de contacto preferido
            $table->enum('metodo_contacto_preferido', [
                'telefono',
                'email',
                'whatsapp',
                'presencial',
                'carta'
            ])->default('telefono')->comment('Método de contacto preferido para gestiones');
            
            // Campos de auditoría adicionales
            $table->string('motivo_estado')->nullable()->comment('Motivo del estado actual');
            $table->datetime('fecha_ultimo_pago')->nullable()->comment('Fecha del último pago recibido');
            $table->string('referencia_pago')->nullable()->comment('Referencia del último pago');
            
            // Timestamps estándar
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['estado', 'fecha_vencimiento'], 'idx_estado_vencimiento');
            $table->index(['usuario_asignado_id', 'estado'], 'idx_usuario_estado');
            $table->index(['cliente_id', 'estado'], 'idx_cliente_estado');
            $table->index(['proxima_gestion', 'estado'], 'idx_proxima_gestion_estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobranzas');
    }
};