<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contacto extends Model
{
    use HasFactory;

    // Nombre de la tabla asociada al modelo
    protected $table = 'contactos'; // ¡Importante: usa el nombre correcto 'contactos'!

    // Campos que pueden ser asignados masivamente
    protected $fillable = [
        'cliente_id',
        'nombre',
        'cargo',
        'email',
        'telefono',
        'area',
        'notas',
    ];

    // Definir la relación inversa: un contacto pertenece a un cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}