<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notificacion;

class NotificacionSeeder extends Seeder
{
    public function run()
    {
        Notificacion::create([
            'titulo' => 'Revisión de cotización pendiente',
            'mensaje' => 'Tienes una cotización que requiere aprobación.',
            'tipo' => 'tarea',
            'urgente' => true,
            'usuario_id' => 1
        ]);
    }
}