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
        Schema::create('contactos', function (Blueprint $table) {
            $table->id(); // ID autoincremental para el contacto

            // Clave foránea para vincular el contacto con un cliente
            // on update cascade: si el ID del cliente cambia, actualiza aquí.
            // on delete cascade: si el cliente es eliminado, elimina sus contactos.
            $table->foreignId('cliente_id')->constrained('clientes')->onUpdate('cascade')->onDelete('cascade');

            $table->string('nombre'); // Nombre completo del contacto
            $table->string('cargo')->nullable(); // Cargo del contacto (ej. "Jefe de Compras")
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->string('area')->nullable(); // Área a la que pertenece (ej. "Urgencias", "Finanzas")
            $table->text('notas')->nullable(); // Notas adicionales sobre este contacto

            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contactos'); // Elimina la tabla si se revierte la migración
    }
};