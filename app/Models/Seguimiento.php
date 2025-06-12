<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Seguimiento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'seguimientos';

    protected $fillable = [
        'cliente_id',
        'cotizacion_id',
        'vendedor_id',
        'estado',
        'prioridad',
        'ultima_gestion',
        'proxima_gestion',
        'notas',
        'resultado_ultima_gestion'
    ];

    protected $casts = [
        'ultima_gestion' => 'date',
        'proxima_gestion' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Estados de seguimiento
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EN_PROCESO = 'en_proceso';
    const ESTADO_COMPLETADO = 'completado';
    const ESTADO_VENCIDO = 'vencido';
    const ESTADO_REPROGRAMADO = 'reprogramado';

    // Prioridades
    const PRIORIDAD_BAJA = 'baja';
    const PRIORIDAD_MEDIA = 'media';
    const PRIORIDAD_ALTA = 'alta';
    const PRIORIDAD_URGENTE = 'urgente';

    // **RELACIONES**

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    // Relación con tareas generadas automáticamente
    public function tareas()
    {
        return $this->hasMany(Tarea::class);
    }

    // **MEJORAS DEL ROADMAP - FASE 2: CRISIS OPERATIVA RESUELTA**

    // **SCOPES PARA CLASIFICACIÓN AUTOMÁTICA (TRIAJE)**

    // Seguimientos atrasados (ROJO)
    public function scopeAtrasados($query)
    {
        return $query->where('proxima_gestion', '<', now()->toDateString())
                     ->whereIn('estado', [self::ESTADO_PENDIENTE, self::ESTADO_EN_PROCESO]);
    }

    // Seguimientos próximos (AMARILLO) - próximos 7 días
    public function scopeProximos($query, $dias = 7)
    {
        return $query->whereBetween('proxima_gestion', [
                         now()->toDateString(),
                         now()->addDays($dias)->toDateString()
                     ])
                     ->whereIn('estado', [self::ESTADO_PENDIENTE, self::ESTADO_EN_PROCESO]);
    }

    // Seguimientos de hoy (VERDE)
    public function scopeHoy($query)
    {
        return $query->where('proxima_gestion', now()->toDateString())
                     ->whereIn('estado', [self::ESTADO_PENDIENTE, self::ESTADO_EN_PROCESO]);
    }

    // Seguimientos completados hoy
    public function scopeCompletadosHoy($query)
    {
        return $query->where('estado', self::ESTADO_COMPLETADO)
                     ->whereDate('updated_at', now()->toDateString());
    }

    // **FILTROS POR USUARIO Y PERMISOS**

    // Scope para vendedores (solo sus seguimientos)
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

    // **BÚSQUEDA Y FILTRADO**

    public function scopeBusqueda($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->whereHas('cliente', function($clienteQuery) use ($termino) {
                $clienteQuery->where('nombre_institucion', 'LIKE', "%{$termino}%")
                            ->orWhere('rut', 'LIKE', "%{$termino}%");
            })
            ->orWhereHas('cotizacion', function($cotizacionQuery) use ($termino) {
                $cotizacionQuery->where('codigo', 'LIKE', "%{$termino}%")
                               ->orWhere('nombre_cotizacion', 'LIKE', "%{$termino}%");
            })
            ->orWhere('notas', 'LIKE', "%{$termino}%");
        });
    }

    public function scopeEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    // **MÉTODOS AUXILIARES PARA TRIAJE AUTOMÁTICO**

    // Calcular días de atraso
    public function getDiasAtrasoAttribute()
    {
        if (!$this->proxima_gestion || $this->estado === self::ESTADO_COMPLETADO) {
            return 0;
        }

        $diff = now()->diffInDays($this->proxima_gestion, false);
        return $diff < 0 ? abs($diff) : 0;
    }

    // Obtener color de clasificación automática
    public function getColorClasificacionAttribute()
    {
        if ($this->estado === self::ESTADO_COMPLETADO) {
            return 'success';
        }

        if ($this->dias_atraso > 0) {
            return 'danger'; // Rojo - Atrasado
        }

        if ($this->proxima_gestion && $this->proxima_gestion->isToday()) {
            return 'warning'; // Amarillo - Hoy
        }

        if ($this->proxima_gestion && $this->proxima_gestion->between(now(), now()->addDays(7))) {
            return 'warning'; // Amarillo - Próximos 7 días
        }

        return 'success'; // Verde - Normal
    }

    // Obtener prioridad automática basada en días de atraso
    public function getPrioridadAutomaticaAttribute()
    {
        if ($this->dias_atraso > 14) {
            return self::PRIORIDAD_URGENTE;
        } elseif ($this->dias_atraso > 7) {
            return self::PRIORIDAD_ALTA;
        } elseif ($this->dias_atraso > 0) {
            return self::PRIORIDAD_MEDIA;
        }

        return self::PRIORIDAD_BAJA;
    }

    // **MÉTODOS PARA GESTIÓN MASIVA**

    // Actualizar estado masivamente
    public static function actualizarMasivo($seguimientoIds, $datos)
    {
        $actualizado = 0;
        
        foreach ($seguimientoIds as $id) {
            $seguimiento = self::find($id);
            
            if ($seguimiento) {
                $seguimiento->fill($datos);
                
                // Si se marca como completado, actualizar fecha
                if (isset($datos['estado']) && $datos['estado'] === self::ESTADO_COMPLETADO) {
                    $seguimiento->ultima_gestion = now()->toDateString();
                }
                
                $seguimiento->save();
                $actualizado++;
            }
        }
        
        return $actualizado;
    }

    // **DISTRIBUCIÓN AUTOMÁTICA - ROADMAP FASE 4**

    // Distribuir seguimientos vencidos automáticamente
    public static function distribuirVencidosAutomaticamente($vendedorIds, $configuracion = [])
    {
        $seguimientosVencidos = self::atrasados()
                                   ->whereIn('vendedor_id', $vendedorIds)
                                   ->orderBy('proxima_gestion', 'asc')
                                   ->get();

        $maxPorDia = $configuracion['max_por_dia'] ?? 5;
        $diasDistribucion = $configuracion['dias_distribucion'] ?? 5;
        
        $tareas_creadas = 0;
        $fecha_actual = now();
        $seguimientos_por_dia = 0;

        foreach ($seguimientosVencidos as $seguimiento) {
            // Si ya se alcanzó el máximo del día, avanzar al siguiente día hábil
            if ($seguimientos_por_dia >= $maxPorDia) {
                $fecha_actual = $fecha_actual->addDay();
                
                // Saltar fines de semana
                while ($fecha_actual->isWeekend()) {
                    $fecha_actual = $fecha_actual->addDay();
                }
                
                $seguimientos_por_dia = 0;
            }

            // Crear tarea automática
            Tarea::create([
                'titulo' => "Seguimiento: {$seguimiento->cliente->nombre_institucion}",
                'descripcion' => "Seguimiento vencido desde {$seguimiento->proxima_gestion->format('d/m/Y')}",
                'usuario_id' => $seguimiento->vendedor_id,
                'tipo' => 'seguimiento',
                'origen' => 'automatica_seguimiento',
                'seguimiento_id' => $seguimiento->id,
                'cliente_id' => $seguimiento->cliente_id,
                'cotizacion_id' => $seguimiento->cotizacion_id,
                'fecha_tarea' => $fecha_actual->toDateString(),
                'estado' => 'pendiente',
                'prioridad' => $seguimiento->prioridad_automatica,
                'es_distribuida_automaticamente' => true,
                'metadata_distribucion' => [
                    'fecha_vencimiento_original' => $seguimiento->proxima_gestion,
                    'dias_atraso' => $seguimiento->dias_atraso
                ]
            ]);

            // Actualizar el seguimiento
            $seguimiento->update([
                'estado' => self::ESTADO_REPROGRAMADO,
                'proxima_gestion' => $fecha_actual->toDateString(),
                'notas' => ($seguimiento->notas ?? '') . "\n[AUTOMÁTICO] Redistribuido el " . now()->format('d/m/Y H:i')
            ]);

            $seguimientos_por_dia++;
            $tareas_creadas++;
        }

        return [
            'seguimientos_procesados' => $seguimientosVencidos->count(),
            'tareas_creadas' => $tareas_creadas,
            'distribuidos_hasta' => $fecha_actual->format('d/m/Y')
        ];
    }

    // **MÉTODOS PARA DASHBOARD**

    // Estadísticas para dashboard
    public static function estadisticasDashboard($vendedorId = null)
    {
        $query = self::query();
        
        if ($vendedorId) {
            $query->paraVendedor($vendedorId);
        }

        return [
            'atrasados' => (clone $query)->atrasados()->count(),
            'hoy' => (clone $query)->hoy()->count(),
            'proximos_7_dias' => (clone $query)->proximos(7)->count(),
            'completados_hoy' => (clone $query)->completadosHoy()->count(),
            'total_activos' => (clone $query)->whereIn('estado', [
                self::ESTADO_PENDIENTE, 
                self::ESTADO_EN_PROCESO
            ])->count()
        ];
    }

    // **INTEGRACIÓN CON COTIZACIONES**

    // Crear seguimiento automático desde cotización
    public static function crearDesdeCotizacion(Cotizacion $cotizacion, $configuracion = [])
    {
        $dias = $configuracion['dias'] ?? 3;
        $prioridad = $configuracion['prioridad'] ?? self::PRIORIDAD_MEDIA;

        return self::create([
            'cliente_id' => $cotizacion->cliente_id,
            'cotizacion_id' => $cotizacion->id,
            'vendedor_id' => $cotizacion->vendedor_id,
            'estado' => self::ESTADO_PENDIENTE,
            'prioridad' => $prioridad,
            'proxima_gestion' => now()->addDays($dias)->toDateString(),
            'notas' => "Seguimiento automático para cotización {$cotizacion->codigo}"
        ]);
    }

    // **MÉTODOS PARA API Y VISTAS**

    public function toTableArray()
    {
        return [
            'id' => $this->id,
            'cliente' => $this->cliente->nombre_institucion ?? 'Sin cliente',
            'rut_cliente' => $this->cliente->rut ?? '',
            'cotizacion' => $this->cotizacion->codigo ?? 'Sin cotización',
            'vendedor' => $this->vendedor->name ?? 'Sin asignar',
            'estado' => $this->estado,
            'prioridad' => $this->prioridad,
            'ultima_gestion' => $this->ultima_gestion ? $this->ultima_gestion->format('d/m/Y') : '',
            'proxima_gestion' => $this->proxima_gestion ? $this->proxima_gestion->format('d/m/Y') : '',
            'dias_atraso' => $this->dias_atraso,
            'color_clasificacion' => $this->color_clasificacion,
            'notas' => $this->notas ?? '',
            'resultado_ultima_gestion' => $this->resultado_ultima_gestion ?? ''
        ];
    }

    // **EVENTOS DEL MODELO**

    protected static function boot()
    {
        parent::boot();

        // Actualizar automáticamente estado vencido
        static::saving(function ($seguimiento) {
            if ($seguimiento->proxima_gestion && 
                $seguimiento->proxima_gestion < now()->toDateString() &&
                in_array($seguimiento->estado, [self::ESTADO_PENDIENTE, self::ESTADO_EN_PROCESO])) {
                
                $seguimiento->estado = self::ESTADO_VENCIDO;
            }
        });

        // Crear tarea automática cuando se actualiza un seguimiento importante
        static::updated(function ($seguimiento) {
            if ($seguimiento->isDirty('estado') && 
                $seguimiento->estado === self::ESTADO_COMPLETADO &&
                $seguimiento->cotizacion && 
                $seguimiento->cotizacion->estado === Cotizacion::ESTADO_GANADA) {
                
                // Crear tarea de post-venta si corresponde
                $seguimiento->crearTareaPostVenta();
            }
        });
    }

    // Crear tarea de post-venta
    private function crearTareaPostVenta()
    {
        Tarea::create([
            'titulo' => 'Post-venta: ' . $this->cliente->nombre_institucion,
            'descripcion' => 'Seguimiento post-venta para cotización ganada: ' . $this->cotizacion->codigo,
            'usuario_id' => $this->vendedor_id,
            'tipo' => 'seguimiento',
            'origen' => 'automatica_seguimiento',
            'cliente_id' => $this->cliente_id,
            'cotizacion_id' => $this->cotizacion_id,
            'fecha_tarea' => now()->addWeeks(2)->toDateString(),
            'estado' => 'pendiente',
            'prioridad' => 'media',
            'notas' => 'Verificar satisfacción del cliente y oportunidades adicionales'
        ]);
    }
}