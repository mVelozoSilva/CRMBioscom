<?php
// database/seeders/ConfiguracionesSeguimientoSeeder.php

namespace Database\Seeders;

use App\Models\ConfiguracionSeguimiento;
use Illuminate\Database\Seeder;

class ConfiguracionesSeguimientoSeeder extends Seeder
{
    public function run(): void
    {
        // Crear todas las configuraciones por defecto
        ConfiguracionSeguimiento::crearConfiguracionesPorDefecto();
        
        $this->command->info('Configuraciones de seguimiento creadas exitosamente.');
    }
}