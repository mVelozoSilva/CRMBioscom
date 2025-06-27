<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campania;

class CampaniaSeeder extends Seeder
{
    public function run()
    {
        Campania::create([
            'nombre' => 'Campaña de prueba',
            'descripcion' => 'Campaña automática para clientes seleccionados',
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addDays(10)
        ]);
    }
}