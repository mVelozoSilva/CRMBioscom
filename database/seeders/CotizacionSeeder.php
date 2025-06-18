<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cotizacion;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;

class CotizacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_CL'); // Usar Faker en español de Chile
        $clientes = Cliente::all(); // Obtener todos los clientes existentes
        $productos = Producto::all(); // Obtener todos los productos existentes
        $vendedores = User::where('id', '>', 0)->get(); // Obtener todos los usuarios (vendedores)

        if ($clientes->isEmpty()) {
            $this->command->warn('No hay clientes en la base de datos. Ejecuta ClienteSeeder primero.');
            return;
        }
        if ($productos->isEmpty()) {
            $this->command->warn('No hay productos en la base de datos. Ejecuta ProductoSeeder primero.');
            return;
        }
        if ($vendedores->isEmpty()) {
            $this->command->warn('No hay vendedores (usuarios) en la base de datos. Ejecuta UserSeeder primero.');
            return;
        }

        $validezOfertaOptions = [
            '30 días corridos', '60 días corridos', '90 días corridos', '180 días corridos'
        ];
        $formaPagoOptions = [
            'Orden de Compra a 30 días', 'Pago al contado con transferencia', 'Leasing Externo'
        ];
        $garantiaTecnicaOptions = [
            '6 meses', '12 meses', '24 meses', 'Sin garantía'
        ];

        // Crear 50 cotizaciones de prueba
        for ($i = 0; $i < 50; $i++) {
            $cliente = $clientes->random();
            $vendedor = $vendedores->random();
            
            $productosCotizados = [];
            $numProductos = $faker->numberBetween(1, 5); // Cada cotización tendrá entre 1 y 5 productos

            $totalNeto = 0;
            for ($j = 0; $j < $numProductos; $j++) {
                $producto = $productos->random();
                $cantidad = $faker->numberBetween(1, 3);
                $precioUnitario = $producto->precio_neto * $faker->randomFloat(2, 0.8, 1.2); // Precio con variación

                $productosCotizados[] = [
                    'id_producto' => $producto->id,
                    'nombre_producto' => $producto->nombre,
                    'descripcion_corta' => $faker->sentence(8),
                    'precio_unitario' => round($precioUnitario, 2),
                    'cantidad' => $cantidad,
                ];
                $totalNeto += ($precioUnitario * $cantidad);
            }

            $ivaRate = 0.19; // 19% de IVA para Chile
            $ivaMonto = round($totalNeto * $ivaRate, 2);
            $totalConIva = round($totalNeto + $ivaMonto, 2);

            Cotizacion::create([
                'nombre_cotizacion' => 'Cotización ' . $faker->unique()->word() . ' ' . $faker->company(),
                'codigo' => $faker->unique()->bothify('COT-###??'),
                'cliente_id' => $cliente->id,
                'vendedor_id' => $vendedor->id,
                'nombre_institucion' => $cliente->nombre_institucion,
                'nombre_contacto' => $cliente->nombre_contacto, // Usar el contacto del cliente
                'productos_cotizados' => json_encode($productosCotizados), // Guardar como JSON
                'total_neto' => $totalNeto,
                'iva' => $ivaMonto,
                'total_con_iva' => $totalConIva,
                'validez_oferta' => $faker->randomElement($validezOfertaOptions),
                'forma_pago' => $faker->randomElement($formaPagoOptions),
                'plazo_entrega' => $faker->numberBetween(5, 30) . ' días hábiles',
                'garantia_tecnica' => $faker->randomElement($garantiaTecnicaOptions),
                'informacion_adicional' => $faker->paragraph(2),
                'descripcion_opcionales' => $faker->boolean(30) ? $faker->paragraph(1) : null,
                'info_contacto_vendedor' => $vendedor->name . ' - ' . $vendedor->email,
                'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                'updated_at' => $faker->dateTimeBetween('-3 months', 'now'),
            ]);
        }

        $this->command->info('✅ Cotizaciones de prueba creadas exitosamente.');
    }
}
