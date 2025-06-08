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
        Schema::table('clientes', function (Blueprint $table) {
            // 1. Renombrar 'nombre' a 'nombre_institucion'
            //    Esto asume que el campo 'nombre' actual ya guarda el nombre de la institución.
            $table->renameColumn('nombre', 'nombre_institucion');

            // 2. Añadir 'rut'
            //    Suele ser único para la institución y puede ser nulo inicialmente si no siempre se tiene.
            $table->string('rut')->unique()->nullable()->after('id');

            // 3. Añadir 'tipo_cliente'
            //    Con opciones predefinidas. Lo hacemos nullable para permitir actualizar clientes existentes.
            //    Si un campo VARCHAR no tiene valores predeterminados, al agregar clientes, debería tener un valor por defecto.
            $table->enum('tipo_cliente', ['Cliente Público', 'Cliente Privado', 'Revendedor'])->nullable()->after('rut');

            // 4. Añadir 'vendedores_a_cargo' (para asignar a varios vendedores)
            //    Usaremos un campo JSON para almacenar los IDs de los vendedores asignados.
            $table->json('vendedores_a_cargo')->nullable()->after('tipo_cliente');

            // 5. Añadir 'informacion_adicional'
            //    Campo de texto largo, puede ser nulo.
            $table->text('informacion_adicional')->nullable()->after('vendedores_a_cargo');

            // Mantendremos 'email' y 'telefono' tal cual, asumiendo que son los contactos principales.
            // La funcionalidad de "agregar nuevos contactos a un mismo cliente" la abordaremos después con una tabla separada si lo necesitas.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Revertir el orden es importante:
            // 1. Eliminar las columnas nuevas
            $table->dropColumn('informacion_adicional');
            $table->dropColumn('vendedores_a_cargo');
            $table->dropColumn('tipo_cliente');
            $table->dropColumn('rut');

            // 2. Renombrar 'nombre_institucion' de vuelta a 'nombre'
            $table->renameColumn('nombre_institucion', 'nombre');
        });
    }
};