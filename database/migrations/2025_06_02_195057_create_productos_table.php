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
        Schema::create('productos', function (Blueprint $table) {
            $table->id(); // ID del producto
            $table->string('nombre');
            $table->text('descripcion')->nullable(); // Descripción detallada, puede ser más larga
            $table->decimal('precio_neto', 10, 2); // Precio neto, 10 dígitos totales, 2 decimales
            $table->string('categoria')->nullable(); // Ej. Equipamiento médico, Insumo, etc.
            // Para 'imagenes', 'accesorios', 'opcionales' podríamos usar JSON en la DB
            // Laravel tiene un tipo 'json' para la base de datos MySQL 5.7+
            $table->json('imagenes')->nullable(); // Almacenar rutas de imágenes como JSON
            $table->json('accesorios')->nullable(); // Almacenar accesorios como JSON
            $table->json('opcionales')->nullable(); // Almacenar opcionales como JSON
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
