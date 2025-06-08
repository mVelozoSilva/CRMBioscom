<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    use HasFactory;

    protected $table = 'cotizaciones';
    protected $primaryKey = 'id'; // Especificar explícitamente la clave primaria
    public $incrementing = true; // Indicar que la clave primaria es auto-incremental
    protected $keyType = 'int'; // Especificar el tipo de dato de la clave primaria (para bigint es 'int' o 'integer')
    protected $routeKeyName = 'id'; 


    protected $fillable = [
        'nombre_cotizacion',
        'codigo',
        'nombre_institucion',
        'nombre_contacto',
        'info_contacto_vendedor',
        'validez_oferta',
        'forma_pago',
        'plazo_entrega',
        'garantia_tecnica',
        'informacion_adicional',
        'descripcion_opcionales',
        'cliente_id',
        'productos_cotizados',
        'total_neto',
        'iva',
        'total_con_iva',
        'estado',
    ];


    // Definir los 'casts' para que Laravel convierta automáticamente
    // los campos JSON de la base de datos en arrays PHP.
    protected $casts = [
        'productos_cotizados' => 'json',
        'validez_oferta' => 'date', // Convertir la fecha a un objeto Carbon
        'created_at' => 'datetime', // Asegurarse de que created_at se caste a objeto Carbon
        'updated_at' => 'datetime', // Asegurarse de que updated_at se caste a objeto Carbon
    ];

    // Definir la relación: una cotización pertenece a un cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}