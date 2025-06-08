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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Nombre del cliente (cadena de texto)
            $table->string('email')->unique(); // Email del cliente (cadena de texto, debe ser único)
            $table->string('telefono')->nullable(); // Teléfono (cadena de texto, puede ser nulo)
            $table->string('direccion')->nullable(); // Dirección (cadena de texto, puede ser nulo)
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
