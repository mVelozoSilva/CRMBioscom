<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    // Columnas que pueden ser asignadas masivamente
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_neto',
        'categoria',
        'imagenes',   // Estas columnas serán JSON
        'accesorios', // Estas columnas serán JSON
        'opcionales', // Estas columnas serán JSON
    ];

    // Definir los 'casts' para que Laravel convierta automáticamente
    // los campos JSON de la base de datos en arrays PHP y viceversa.
    protected $casts = [
        'imagenes' => 'array',
        'accesorios' => 'array',
        'opcionales' => 'array',
    ];
}
