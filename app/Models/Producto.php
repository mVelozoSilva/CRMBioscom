<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    /**
     * Columnas que pueden ser asignadas masivamente
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_neto',
        'categoria',
        'imagenes',   // JSON
        'accesorios', // JSON
        'opcionales', // JSON
        'estado'      // Agregué este campo para activar/desactivar productos
    ];

    /**
     * Definir los 'casts' para que Laravel convierta automáticamente
     * los campos JSON de la base de datos en arrays PHP y viceversa.
     */
    protected $casts = [
        'imagenes' => 'array',
        'accesorios' => 'array',
        'opcionales' => 'array',
        'precio_neto' => 'decimal:2'
    ];

    /**
     * Accessor: Formatear precio para mostrar
     */
    public function getPrecioFormateadoAttribute()
    {
        return '$' . number_format($this->precio_neto, 0, ',', '.');
    }

    /**
     * Scope: Solo productos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'Activo');
    }

    /**
     * Scope: Filtrar por categoría
     */
    public function scopePorCategoria($query, $categoria)
    {
        if ($categoria) {
            return $query->where('categoria', $categoria);
        }
        return $query;
    }

    /**
     * Scope: Buscar por nombre o descripción
     */
    public function scopeBuscar($query, $termino)
    {
        if ($termino) {
            return $query->where(function($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                  ->orWhere('descripcion', 'like', "%{$termino}%")
                  ->orWhere('categoria', 'like', "%{$termino}%");
            });
        }
        return $query;
    }

    /**
     * Relación: Producto puede estar en muchas cotizaciones (si agregas tabla pivot)
     */
    // public function cotizaciones()
    // {
    //     return $this->belongsToMany(Cotizacion::class, 'cotizacion_producto')
    //                 ->withPivot('cantidad', 'precio_unitario', 'descripcion_corta')
    //                 ->withTimestamps();
    // }

    /**
     * Método para obtener el primer accesorio (útil para vistas)
     */
    public function getPrimerAccesorioAttribute()
    {
        if (is_array($this->accesorios) && count($this->accesorios) > 0) {
            return $this->accesorios[0];
        }
        return 'No especificado';
    }

    /**
     * Método para contar accesorios
     */
    public function getCantidadAccesoriosAttribute()
    {
        return is_array($this->accesorios) ? count($this->accesorios) : 0;
    }

    /**
     * Método para obtener categorías únicas (método estático)
     */
    public static function getCategorias()
    {
        return self::distinct()->pluck('categoria')->filter()->sort()->values();
    }

    /**
     * Método para verificar si tiene imágenes
     */
    public function tieneImagenes()
    {
        return is_array($this->imagenes) && count($this->imagenes) > 0;
    }

    /**
     * Método para obtener la primera imagen
     */
    public function getPrimeraImagenAttribute()
    {
        if ($this->tieneImagenes()) {
            return $this->imagenes[0];
        }
        return 'https://via.placeholder.com/300x200?text=Sin+Imagen';
    }
}