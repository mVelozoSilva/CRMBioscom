<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Archivo;

class ArchivoSeeder extends Seeder
{
    public function run()
    {
        Archivo::create([
            'nombre' => 'documento_prueba.pdf',
            'ruta' => 'archivos/documento_prueba.pdf',
            'tipo' => 'application/pdf'
        ]);
    }
}