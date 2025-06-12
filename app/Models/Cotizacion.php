<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\TipoCotizacion;
use App\Enums\ModalidadSeguimiento;

class Cotizacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cotizaciones';

    protected $fillable = [
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
        'vendedor_id',
        'nombre_cotizacion',
        'productos_cotizados',
        'total_neto',
        'iva',
        'total_con_iva',
        'estado',
        'tipo_cotizacion',
        'modalidad_seguimiento',
        'dias_seguimiento_verde',
        'dias_seguimiento_amarillo',
        'dias_seguimiento_rojo',
        'seguimiento_personalizado',
        'configuracion_seguimiento',
        'proximo_seguimiento',
        'intentos_seguimiento',
        'max_intentos_seguimiento'
    ];

    protected $casts = [
        'validez_oferta' => 'date',
        'productos_cotizados' => 'array',
        'total_neto' => 'decimal:2',
        'iva' => 'decimal:2',
        'total_con_iva' => 'decimal:2',
        'seguimiento_personalizado' => 'boolean',
        'configuracion_seguimiento' => 'array',
        'proximo_seguimiento' => 'datetime',
        'intentos_seguimiento' => 'integer',
        'max_intentos_seguimiento' => 'integer',
        'dias_seguimiento_verde' => 'integer',
        'dias_seguimiento_amarillo' => 'integer',
        'dias_seguimiento_rojo' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Estados de cotización
    const ESTADO_PENDIENTE = 'Pendiente';
    const ESTADO_ENVIADA = 'Enviada';
    const ESTADO_EN_REVISION = 'En Revisión';
    const ESTADO_GANADA = 'Ganada';
    const ESTADO_PERDIDA = 'Perdida';
    const ESTADO_VENCIDA = 'Vencida';

    // Formas de pago predefinidas (según Bioscom)
    const FORMAS_PAGO = [
        'Contado contra entrega',
        '30 días fecha factura',
        '60 días fecha factura',
        '90 días fecha factura',
        'Leasing financiero',
        'Programa gobierno',
        'Licitación pública'
    ];

    // **RELACIONES**

    // Relación con cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Relación con vendedor
    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    // Relación con productos (many-to-many)
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'cotizacion_producto')
                   ->withPivot('cantidad', 'precio_unitario', 'descuento', 'subtotal')
                   ->withTimestamps();
    }

    // Relación con seguimientos
    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class);
    }

    // **MEJORAS DEL ROADMAP - FASE 3: CONTROL TOTAL**

    // **MÉTODOS DE BÚSQUEDA Y FILTRADO**

    // Scope para búsqueda por código, nombre o institución
    public function scopeBusqueda($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('codigo', 'LIKE', "%{$termino}%")
              ->orWhere('nombre_cotizacion', 'LIKE', "%{$termino}%")
              ->orWhere('nombre_institucion', 'LIKE', "%{$termino}%")
              ->orWhereHas('cliente', function($clienteQuery) use ($termino) {
                  $clienteQuery->where('nombre_institucion', 'LIKE', "%{$termino}%")
                               ->orWhere('rut', 'LIKE', "%{$termino}%");
              });
        });
    }

    // Scope para filtrar por estado
    public function scopeEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    // Scope para filtrar por vendedor
    public function scopeVendedor($query, $vendedorId)
    {
        return $query->where('vendedor_id', $vendedorId);
    }

    // Scope para cotizaciones vencidas
    public function scopeVencidas($query)
    {
        return $query->where('validez_oferta', '<', now())
                     ->whereNotIn('estado', [self::ESTADO_GANADA, self::ESTADO_PERDIDA]);
    }

    // Scope para cotizaciones próximas a vencer
    public function scopeProximasVencer($query, $dias = 7)
    {
        return $query->whereBetween('validez_oferta', [now(), now()->addDays($dias)])
                     ->whereNotIn('estado', [self::ESTADO_GANADA, self::ESTADO_PERDIDA, self::ESTADO_VENCIDA]);
    }

    // **MÉTODOS AUXILIARES**

    // Verificar si está vencida
    public function getVencidaAttribute()
    {
        return $this->validez_oferta < now() && 
               !in_array($this->estado, [self::ESTADO_GANADA, self::ESTADO_PERDIDA]);
    }

    // Días restantes para vencimiento
    public function getDiasVencimientoAttribute()
    {
        if ($this->vencida) return 0;
        return now()->diffInDays($this->validez_oferta);
    }

    // Color para indicador de vencimiento
    public function getColorVencimientoAttribute()
    {
        if ($this->vencida) return 'red';
        if ($this->dias_vencimiento <= 3) return 'red';
        if ($this->dias_vencimiento <= 7) return 'yellow';
        return 'green';
    }

    // **CÁLCULOS AUTOMÁTICOS**

    // Recalcular totales basado en productos
    public function recalcularTotales()
    {
        if (!$this->productos_cotizados || empty($this->productos_cotizados)) {
            $this->total_neto = 0;
            $this->iva = 0;
            $this->total_con_iva = 0;
            return;
        }

        $total_neto = 0;
        
        foreach ($this->productos_cotizados as $producto) {
            $cantidad = $producto['cantidad'] ?? 1;
            $precio = $producto['precio_unitario'] ?? 0;
            $descuento = $producto['descuento'] ?? 0;
            
            $subtotal = $cantidad * $precio;
            $subtotal_con_descuento = $subtotal - ($subtotal * $descuento / 100);
            $total_neto += $subtotal_con_descuento;
        }

        $this->total_neto = $total_neto;
        $this->iva = $total_neto * 0.19;
        $this->total_con_iva = $total_neto + $this->iva;
    }

    // **GENERACIÓN AUTOMÁTICA DE CÓDIGO**

    public static function generarCodigo()
    {
        $anio = date('Y');
        $mes = date('m');
        
        // Buscar el último número de la serie del mes
        $ultimo = self::where('codigo', 'LIKE', "COT-{$anio}{$mes}-%")
                     ->orderBy('codigo', 'desc')
                     ->first();

        if ($ultimo) {
            $ultimoNumero = intval(substr($ultimo->codigo, -4));
            $siguiente = $ultimoNumero + 1;
        } else {
            $siguiente = 1;
        }

        return "COT-{$anio}{$mes}-" . str_pad($siguiente, 4, '0', STR_PAD_LEFT);
    }

    // **SEGUIMIENTO AUTOMÁTICO - ROADMAP FASE 2**

    // Crear seguimiento automático al cambiar estado
    public function crearSeguimientoAutomatico($estadoAnterior = null)
    {
        // Solo crear seguimiento para estados que requieren acción
        $estadosConSeguimiento = [self::ESTADO_ENVIADA, self::ESTADO_EN_REVISION];
        
        if (!in_array($this->estado, $estadosConSeguimiento)) {
            return;
        }

        // Obtener configuración de seguimiento
        $configuracion = ConfiguracionSeguimiento::where('tipo_cotizacion', $this->tipo_cotizacion)
                                                ->where('tipo_cliente', $this->cliente->tipo_cliente ?? 'Cliente Privado')
                                                ->where('modalidad_seguimiento', $this->modalidad_seguimiento)
                                                ->where('activo', true)
                                                ->first();

        if (!$configuracion) {
            // Usar configuración por defecto
            $diasProximo = $this->dias_seguimiento_verde;
        } else {
            $diasProximo = $configuracion->dias_verde;
        }

        // Crear seguimiento
        Seguimiento::create([
            'cliente_id' => $this->cliente_id,
            'cotizacion_id' => $this->id,
            'vendedor_id' => $this->vendedor_id,
            'estado' => 'pendiente',
            'prioridad' => 'media',
            'proxima_gestion' => now()->addDays($diasProximo),
            'notas' => "Seguimiento automático para cotización {$this->codigo} - Estado: {$this->estado}"
        ]);

        // Actualizar próximo seguimiento en la cotización
        $this->proximo_seguimiento = now()->addDays($diasProximo);
        $this->save();
    }

    // **AUTOMATIZACIÓN POST-VENTA - ROADMAP FASE 5**

    // Crear gestión de mantención automática al ganar cotización
    public function crearGestionMantencionAutomatica()
    {
        if ($this->estado !== self::ESTADO_GANADA) {
            return;
        }

        // Verificar si algún producto requiere mantención
        $productosConMantencion = collect($this->productos_cotizados)
            ->filter(function($producto) {
                $productoModel = Producto::find($producto['producto_id'] ?? null);
                return $productoModel && $productoModel->requiere_mantencion;
            });

        if ($productosConMantencion->isEmpty()) {
            return;
        }

        // Crear cola de seguimiento para evaluación de ST
        ColaSeguimiento::create([
            'cotizacion_id' => $this->id,
            'programado_para' => now()->addDays(7), // Una semana después
            'estado' => 'pendiente',
            'prioridad' => 'normal',
            'tipo_accion' => 'evaluar_mantencion_preventiva',
            'metadata_triaje' => [
                'motivo' => 'Cotización ganada con equipos que requieren mantención',
                'productos_con_mantencion' => $productosConMantencion->toArray()
            ],
            'notas' => "Evaluar ofertar mantención preventiva para equipos vendidos en cotización {$this->codigo}"
        ]);
    }

    // **MÉTODOS PARA DASHBOARD Y REPORTES**

    // Cotizaciones por estado para gráficos
    public static function contarPorEstado()
    {
        return self::selectRaw('estado, COUNT(*) as total')
                   ->groupBy('estado')
                   ->pluck('total', 'estado')
                   ->toArray();
    }

    // Cotizaciones del mes actual
    public static function delMesActual()
    {
        return self::whereMonth('created_at', now()->month)
                   ->whereYear('created_at', now()->year);
    }

    // Valor total ganado en el mes
    public static function valorGanadoMes()
    {
        return self::delMesActual()
                   ->where('estado', self::ESTADO_GANADA)
                   ->sum('total_con_iva');
    }

    // Top vendedores del mes
    public static function topVendedoresMes($limite = 5)
    {
        return self::delMesActual()
                   ->where('estado', self::ESTADO_GANADA)
                   ->with('vendedor')
                   ->selectRaw('vendedor_id, COUNT(*) as cotizaciones_ganadas, SUM(total_con_iva) as valor_total')
                   ->groupBy('vendedor_id')
                   ->orderBy('valor_total', 'desc')
                   ->limit($limite)
                   ->get();
    }

    // **MÉTODOS PARA API**

    public function toSearchArray()
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre_cotizacion' => $this->nombre_cotizacion,
            'cliente' => $this->cliente->nombre_institucion ?? $this->nombre_institucion,
            'total_formateado' => '$' . number_format($this->total_con_iva, 0, ',', '.'),
            'estado' => $this->estado,
            'validez_oferta' => $this->validez_oferta->format('d/m/Y'),
            'color_vencimiento' => $this->color_vencimiento
        ];
    }

    // **SCOPES PARA ROLES - ROADMAP PERMISOS**

    // Scope para vendedores (solo sus cotizaciones)
    public function scopeParaVendedor($query, $vendedorId)
    {
        return $query->where('vendedor_id', $vendedorId);
    }

    // Scope para jefe de ventas (todo el equipo)
    public function scopeParaJefeVentas($query, $equipoIds = [])
    {
        if (empty($equipoIds)) {
            return $query;
        }
        
        return $query->whereIn('vendedor_id', $equipoIds);
    }

    // **EVENTOS DEL MODELO**

    protected static function boot()
    {
        parent::boot();

        // Generar código automáticamente al crear
        static::creating(function ($cotizacion) {
            if (empty($cotizacion->codigo)) {
                $cotizacion->codigo = self::generarCodigo();
            }
        });

        // Recalcular totales antes de guardar
        static::saving(function ($cotizacion) {
            $cotizacion->recalcularTotales();
        });

        // Crear seguimiento automático al cambiar estado
        static::updated(function ($cotizacion) {
            if ($cotizacion->isDirty('estado')) {
                $estadoAnterior = $cotizacion->getOriginal('estado');
                $cotizacion->crearSeguimientoAutomatico($estadoAnterior);
                
                // Si se ganó la cotización, crear gestión de mantención
                if ($cotizacion->estado === self::ESTADO_GANADA) {
                    $cotizacion->crearGestionMantencionAutomatica();
                }
            }
        });
    }
}