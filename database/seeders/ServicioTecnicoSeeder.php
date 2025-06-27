<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServicioTecnico;

class ServicioTecnicoSeeder extends Seeder
{
    public function run()
    {
        ServicioTecnico::create([
            'titulo' => 'Revisión inicial',
            'estado' => 'pendiente',
            'descripcion' => 'Se debe revisar equipo ingresado.',
            'cliente_id' => 1
        ]);
    }
}