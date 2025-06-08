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
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // ID cotización (auto en el diseño, pero aquí manual por ahora)
            $table->string('nombre_institucion');
            $table->string('nombre_contacto');
            $table->string('info_contacto_vendedor')->nullable(); // Información del vendedor
            $table->date('validez_oferta'); // Fecha de validez de la oferta
            $table->string('forma_pago')->nullable();
            $table->string('plazo_entrega')->nullable();
            $table->text('garantia_tecnica')->nullable();
            $table->text('informacion_adicional')->nullable();
            $table->text('descripcion_opcionales')->nullable(); // Descripción opcionales (campo de texto)

            // Relación con el cliente (clave foránea)
            // Esto asume que tienes una tabla 'clientes' y su ID es 'id'.
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');

            // Información de los productos y su descripción corta/precio editable
            // Almacenaremos esto como un array JSON de objetos, cada uno representando un producto en la cotización
            // { id_producto: X, nombre: "...", descripcion_corta: "...", precio_unitario: X, cantidad: X, ... }
            $table->json('productos_cotizados');

            $table->decimal('total_neto', 12, 2)->default(0); // Mayor precisión para totales
            $table->decimal('iva', 12, 2)->default(0);
            $table->decimal('total_con_iva', 12, 2)->default(0);

            $table->string('estado')->default('Pendiente'); // Ej: Pendiente, Enviada, Ganada, Perdida

            $table->timestamps(); // created_at y updated_at
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
