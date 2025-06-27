<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Formulario;

class FormularioSeeder extends Seeder
{
    public function run()
    {
        Formulario::create([
            'nombre' => 'Formulario Básico',
            'descripcion' => 'Plantilla inicial de prueba'
        ]);
    }
}