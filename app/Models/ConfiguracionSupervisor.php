<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionSupervisor extends Model
{
    protected $table = 'configuracion_supervisores'; 

    protected $fillable = [
        'modo_oscuro',
        'contraste_alto',
        'tamano_fuente',
        'activar_alertas',
        'orden_prioridad',
    ];
}
