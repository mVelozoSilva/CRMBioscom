<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // MANTENER: Tu usuario de prueba
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        
        // TODOS TUS SEEDERS en el orden correcto:
        $this->call([
            // 1. Seeders de usuarios y roles (base del sistema)
            UserSeeder::class,
            RolesSeeder::class,
            
            // 2. Seeders de datos básicos del negocio
            ClienteSeeder::class,
            ProductoSeeder::class,
            
            // 3. Configuraciones del sistema - COMENTADO TEMPORALMENTE
            // ConfiguracionesSeguimientoSeeder::class,  // Comentado por error de método
            
            // 4. Seeders que dependen de datos anteriores
            CotizacionSeeder::class,        // Necesita clientes y productos
            SeguimientosSeeder::class,      // Necesita clientes y cotizaciones
            
            // 5. Finalmente el nuevo seeder de tareas
            TareaSeeder::class,             // Necesita usuarios, clientes, cotizaciones, seguimientos

            // 6. Seeders de módulos cobranzas
             CobranzaSeeder::class,
        ]);
    }
}

/*
NOTA: ConfiguracionesSeguimientoSeeder está comentado porque tiene un error:
- Llama al método crearConfiguracionesPorDefecto() que no existe en el modelo
- Lo arreglaremos después o lo omitiremos si no es crítico
*/