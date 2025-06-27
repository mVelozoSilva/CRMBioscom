<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConfiguracionSupervisor;

class ConfiguracionSupervisorSeeder extends Seeder
{
    public function run()
    {
        ConfiguracionSupervisor::create([
            'modo_oscuro' => false,
            'contraste_alto' => false,
            'tamano_fuente' => 'mediana',
            'activar_alertas' => true,
            'orden_prioridad' => 'fecha_desc'
        ]);
    }
}