<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_neto',
        'categoria',
        'imagenes',
        'accesorios',
        'opcionales',
        'estado',
        'codigo_producto',
        'stock_minimo',
        'requiere_mantencion',
        'frecuencia_mantencion_meses'
    ];

    protected $casts = [
        'precio_neto' => 'decimal:2',
        'imagenes' => 'array',
        'accesorios' => 'array',
        'opcionales' => 'array',
        'requiere_mantencion' => 'boolean',
        'frecuencia_mantencion_meses' => 'integer',
        'stock_minimo' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Estados válidos
    const ESTADO_ACTIVO = 'Activo';
    const ESTADO_INACTIVO = 'Inactivo';
    const ESTADO_DESCONTINUADO = 'Descontinuado';

    // Categorías de productos (según equipamiento médico Bioscom)
    const CATEGORIAS = [
        'Equipamiento Médico',
        'Insumos Médicos',
        'Mobiliario Hospitalario',
        'Tecnología Médica',
        'Instrumental Quirúrgico',
        'Equipos de Diagnóstico',
        'Mantenimiento y Servicios'
    ];

    // **MEJORAS DEL ROADMAP - CONSTRUCTOR VISUAL DE CONTENIDO**

    // Relación con plantillas de contenido para cotizaciones
    public function plantillasContenido()
    {
        return $this->hasMany(PlantillaContenido::class);
    }

    // Relación many-to-many con cotizaciones
    public function cotizaciones()
    {
        return $this->belongsToMany(Cotizacion::class, 'cotizacion_producto')
                   ->withPivot('cantidad', 'precio_unitario', 'descuento')
                   ->withTimestamps();
    }

    // **MÉTODOS DE BÚSQUEDA Y FILTRADO**

    // Scope para búsqueda rápida por nombre, código o descripción
    public function scopeBusqueda($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('nombre', 'LIKE', "%{$termino}%")
              ->orWhere('codigo_producto', 'LIKE', "%{$termino}%")
              ->orWhere('descripcion', 'LIKE', "%{$termino}%");
        });
    }

    // Scope para filtrar por categoría
    public function scopeCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    // Scope para productos activos
    public function scopeActivos($query)
    {
        return $query->where('estado', self::ESTADO_ACTIVO);
    }

    // Scope para productos que requieren mantenimiento
    public function scopeConMantencion($query)
    {
        return $query->where('requiere_mantencion', true);
    }

    // Scope para productos por rango de precio
    public function scopeRangoPrecio($query, $min = null, $max = null)
    {
        if ($min !== null) {
            $query->where('precio_neto', '>=', $min);
        }
        if ($max !== null) {
            $query->where('precio_neto', '<=', $max);
        }
        return $query;
    }

    // **MÉTODOS AUXILIARES PARA COTIZACIONES**

    // Obtener precio con IVA
    public function getPrecioConIvaAttribute()
    {
        return $this->precio_neto * 1.19;
    }

    // Formatear precio para mostrar
    public function getPrecioFormateadoAttribute()
    {
        return '$' . number_format($this->precio_neto, 0, ',', '.');
    }

    // Formatear precio con IVA
    public function getPrecioIvaFormateadoAttribute()
    {
        return '$' . number_format($this->precio_con_iva, 0, ',', '.');
    }

    // Obtener primera imagen o placeholder
    public function getImagenPrincipalAttribute()
    {
        if ($this->imagenes && count($this->imagenes) > 0) {
            return $this->imagenes[0];
        }
        return '/assets/images/producto-placeholder.png';
    }

    // **CONSTRUCTOR VISUAL DE CONTENIDO - ROADMAP FASE 4**

    // Obtener plantilla predefinida según tipo de producto
    public function getPlantillaPredefinida()
    {
        $plantillas = [
            'insumo' => [
                'titulo' => 'Insumo Médico',
                'bloques' => [
                    ['tipo' => 'descripcion', 'contenido' => $this->descripcion],
                    ['tipo' => 'especificaciones', 'contenido' => ''],
                    ['tipo' => 'incluye', 'contenido' => $this->accesorios],
                    ['tipo' => 'garantia', 'contenido' => 'Garantía estándar']
                ]
            ],
            'equipo_simple' => [
                'titulo' => 'Equipamiento Médico',
                'bloques' => [
                    ['tipo' => 'descripcion', 'contenido' => $this->descripcion],
                    ['tipo' => 'caracteristicas', 'contenido' => ''],
                    ['tipo' => 'incluye', 'contenido' => $this->accesorios],
                    ['tipo' => 'opcionales', 'contenido' => $this->opcionales],
                    ['tipo' => 'instalacion', 'contenido' => 'Instalación incluida'],
                    ['tipo' => 'capacitacion', 'contenido' => 'Capacitación básica incluida'],
                    ['tipo' => 'garantia', 'contenido' => 'Garantía técnica 12 meses']
                ]
            ],
            'equipo_complejo' => [
                'titulo' => 'Tecnología Médica Avanzada',
                'bloques' => [
                    ['tipo' => 'descripcion', 'contenido' => $this->descripcion],
                    ['tipo' => 'especificaciones_tecnicas', 'contenido' => ''],
                    ['tipo' => 'beneficios', 'contenido' => ''],
                    ['tipo' => 'incluye', 'contenido' => $this->accesorios],
                    ['tipo' => 'opcionales', 'contenido' => $this->opcionales],
                    ['tipo' => 'instalacion', 'contenido' => 'Instalación profesional certificada'],
                    ['tipo' => 'capacitacion', 'contenido' => 'Capacitación completa del personal'],
                    ['tipo' => 'mantencion', 'contenido' => 'Programa de mantención preventiva disponible'],
                    ['tipo' => 'soporte', 'contenido' => 'Soporte técnico 24/7'],
                    ['tipo' => 'garantia', 'contenido' => 'Garantía técnica 24 meses + repuestos']
                ]
            ]
        ];

        // Determinar tipo según categoría y precio
        if (in_array($this->categoria, ['Insumos Médicos'])) {
            return $plantillas['insumo'];
        } elseif ($this->precio_neto > 5000000) { // Más de 5 millones
            return $plantillas['equipo_complejo'];
        } else {
            return $plantillas['equipo_simple'];
        }
    }

    // **MÉTODOS PARA SERVICIO TÉCNICO - ROADMAP FASE 5**

    // Verificar si necesita mantención programada
    public function necesitaMantencion()
    {
        if (!$this->requiere_mantencion) {
            return false;
        }

        // Aquí se podría verificar con registros de mantención
        // return $this->ultimaMantencion() < now()->subMonths($this->frecuencia_mantencion_meses);
        return true;
    }

    // **MÉTODOS ESTÁTICOS PARA DASHBOARD Y REPORTES**

    // Productos más cotizados
    public static function masCotizados($limite = 10)
    {
        return self::withCount('cotizaciones')
                   ->orderBy('cotizaciones_count', 'desc')
                   ->limit($limite)
                   ->get();
    }

    // Productos por categoría para gráficos
    public static function contarPorCategoria()
    {
        return self::selectRaw('categoria, COUNT(*) as total')
                   ->where('estado', self::ESTADO_ACTIVO)
                   ->groupBy('categoria')
                   ->pluck('total', 'categoria')
                   ->toArray();
    }

    // Valor total del catálogo
    public static function valorTotalCatalogo()
    {
        return self::where('estado', self::ESTADO_ACTIVO)
                   ->sum('precio_neto');
    }

    // **MÉTODOS PARA API Y BÚSQUEDAS AJAX**

    // Transformar para búsquedas rápidas en cotizaciones
    public function toSearchArray()
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'codigo_producto' => $this->codigo_producto,
            'precio_neto' => $this->precio_neto,
            'precio_formateado' => $this->precio_formateado,
            'categoria' => $this->categoria,
            'imagen_principal' => $this->imagen_principal,
            'descripcion_corta' => substr($this->descripcion, 0, 100) . '...'
        ];
    }

    // **VALIDACIONES PERSONALIZADAS**

    // Validar que el código de producto sea único
    public function validarCodigoUnico($codigo, $productoId = null)
    {
        $query = self::where('codigo_producto', $codigo);
        
        if ($productoId) {
            $query->where('id', '!=', $productoId);
        }
        
        return $query->doesntExist();
    }

    // **MÉTODO PARA GENERAR CÓDIGO AUTOMÁTICO**

    public static function generarCodigoproducto($categoria)
    {
        $prefijos = [
            'Equipamiento Médico' => 'EM',
            'Insumos Médicos' => 'IM',
            'Mobiliario Hospitalario' => 'MH',
            'Tecnología Médica' => 'TM',
            'Instrumental Quirúrgico' => 'IQ',
            'Equipos de Diagnóstico' => 'ED',
            'Mantenimiento y Servicios' => 'MS'
        ];

        $prefijo = $prefijos[$categoria] ?? 'PR';
        $siguiente = self::where('codigo_producto', 'LIKE', $prefijo . '%')
                         ->count() + 1;

        return $prefijo . str_pad($siguiente, 4, '0', STR_PAD_LEFT);
    }
}