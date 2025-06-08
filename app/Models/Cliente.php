<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
// Aquí definimos las columnas de la tabla 'clientes' que se pueden asignar masivamente
    protected $fillable = [
        'nombre_institucion', // Renombrado de 'nombre'
        'rut',
        'tipo_cliente',
        'vendedores_a_cargo',
        'informacion_adicional',
        'email',
        'telefono',
        'direccion',
        'nombre_contacto',
    ];

    // Laravel guardará automáticamente los campos 'created_at' y 'updated_at'

    // Si planeas que 'vendedores_a_cargo' sea un array en PHP, puedes castearlo
    protected $casts = [
        'vendedores_a_cargo' => 'array',
    ];
     
    public function contactos()
    {
        return $this->hasMany(Contacto::class);
    }
}

?>



