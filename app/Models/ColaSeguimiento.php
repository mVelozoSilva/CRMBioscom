<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ColaSeguimiento extends Model
{
    use HasFactory;

    protected $table = 'cola_seguimientos';

    protected $fillable = [
        'cotizacion_id',
        'programado_para',
        'estado',
        'prioridad',
        'metadata_triaje',
        'tipo_accion',
        'procesado',
        'procesado_en',
        'procesado_por',
        'notas',
        'resultado',
    ];

    protected $casts = [
        'programado_para' => 'datetime',
        'procesado' => 'boolean',
        'procesado_en' => 'datetime',
        'metadata_triaje' => 'array',
    ];

    protected $dates = [
        'programado_para',
        'procesado_en',
        'created_at',
        'updated_at',
    ];

    /**
     * Estados válidos para la cola de seguimientos
     */
    const ESTADOS = [
        'pendiente',
        'en_proceso',
        'completado',
        'cancelado',
        'error'
    ];

    /**
     * Prioridades del sistema de triaje
     */
    const PRIORIDADES = [
        'baja',
        'normal',
        'alta',
        'urgente',
        'critica'
    ];

    /**
     * Tipos de acciones disponibles
     */
    const TIPOS_ACCION = [
        'distribuir_automatico',
        'asignar_vendedor',
        'crear_tarea',
        'enviar_recordatorio',
        'escalar_supervision',
        'marcar_perdido',
        'reagendar_seguimiento',
        'notificar_vencimiento'
    ];

    /**
     * Relaciones
     */

    /**
     * Cotización asociada al seguimiento en cola
     */
    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    /**
     * Usuario que procesó la cola (si aplica)
     */
    public function procesadoPor()
    {
        return $this->belongsTo(User::class, 'procesado_por');
    }

    /**
     * Seguimiento relacionado través de la cotización
     */
    public function seguimiento()
    {
        return $this->hasOneThrough(
            Seguimiento::class,
            Cotizacion::class,
            'id', // Foreign key en cotizaciones
            'cotizacion_id', // Foreign key en seguimientos
            'cotizacion_id', // Local key en cola_seguimientos
            'id' // Local key en cotizaciones
        );
    }

    /**
     * Scopes
     */

    /**
     * Scope para elementos pendientes de procesamiento
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente')
                    ->where('procesado', false);
    }

    /**
     * Scope para elementos en proceso
     */
    public function scopeEnProceso($query)
    {
        return $query->where('estado', 'en_proceso');
    }

    /**
     * Scope para elementos completados
     */
    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado')
                    ->where('procesado', true);
    }

    /**
     * Scope para elementos por prioridad
     */
    public function scopePorPrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    /**
     * Scope para elementos de alta prioridad
     */
    public function scopeAltaPrioridad($query)
    {
        return $query->whereIn('prioridad', ['alta', 'urgente', 'critica']);
    }

    /**
     * Scope para elementos críticos
     */
    public function scopeCriticos($query)
    {
        return $query->where('prioridad', 'critica');
    }

    /**
     * Scope para elementos programados hasta cierta fecha
     */
    public function scopeProgramadosHasta($query, $fecha)
    {
        return $query->where('programado_para', '<=', $fecha);
    }

    /**
     * Scope para elementos programados para hoy
     */
    public function scopeProgramadosHoy($query)
    {
        return $query->whereDate('programado_para', Carbon::today());
    }

    /**
     * Scope para elementos vencidos
     */
    public function scopeVencidos($query)
    {
        return $query->where('programado_para', '<', Carbon::now())
                    ->where('procesado', false);
    }

    /**
     * Scope para elementos por tipo de acción
     */
    public function scopePorTipoAccion($query, $tipo)
    {
        return $query->where('tipo_accion', $tipo);
    }

    /**
     * Scope para ordenar por prioridad del triaje
     */
    public function scopeOrdenadoPorPrioridad($query)
    {
        return $query->orderByRaw("
            CASE prioridad
                WHEN 'critica' THEN 1
                WHEN 'urgente' THEN 2
                WHEN 'alta' THEN 3
                WHEN 'normal' THEN 4
                WHEN 'baja' THEN 5
                ELSE 6
            END
        ")->orderBy('programado_para', 'asc');
    }

    /**
     * Scope para elementos con errores
     */
    public function scopeConErrores($query)
    {
        return $query->where('estado', 'error');
    }

    /**
     * Accessors
     */

    /**
     * Accessor para determinar si el elemento está vencido
     */
    public function getEsVencidoAttribute()
    {
        return $this->programado_para < Carbon::now() && !$this->procesado;
    }

    /**
     * Accessor para obtener el color según la prioridad
     */
    public function getColorPrioridadAttribute()
    {
        $colores = [
            'baja' => 'green',
            'normal' => 'blue',
            'alta' => 'yellow',
            'urgente' => 'orange',
            'critica' => 'red'
        ];

        return $colores[$this->prioridad] ?? 'gray';
    }

    /**
     * Accessor para obtener el color según el estado
     */
    public function getColorEstadoAttribute()
    {
        $colores = [
            'pendiente' => 'yellow',
            'en_proceso' => 'blue',
            'completado' => 'green',
            'cancelado' => 'gray',
            'error' => 'red'
        ];

        return $colores[$this->estado] ?? 'gray';
    }

    /**
     * Accessor para obtener tiempo transcurrido desde programación
     */
    public function getTiempoTranscurridoAttribute()
    {
        return $this->programado_para->diffForHumans();
    }

    /**
     * Accessor para obtener tiempo de retraso si está vencido
     */
    public function getTiempoRetrasoAttribute()
    {
        if (!$this->es_vencido) {
            return null;
        }

        $minutosRetraso = Carbon::now()->diffInMinutes($this->programado_para);
        
        if ($minutosRetraso < 60) {
            return $minutosRetraso . ' minutos';
        } elseif ($minutosRetraso < 1440) { // 24 horas
            $horas = intval($minutosRetraso / 60);
            return $horas . ' hora' . ($horas !== 1 ? 's' : '');
        } else {
            $dias = intval($minutosRetraso / 1440);
            return $dias . ' día' . ($dias !== 1 ? 's' : '');
        }
    }

    /**
     * Accessor para obtener información de triaje formateada
     */
    public function getInfoTriajeAttribute()
    {
        if (!$this->metadata_triaje) {
            return null;
        }

        $metadata = $this->metadata_triaje;
        
        return [
            'criterios_aplicados' => $metadata['criterios'] ?? [],
            'puntuacion_prioridad' => $metadata['puntuacion'] ?? 0,
            'reglas_activadas' => $metadata['reglas'] ?? [],
            'contexto_cliente' => $metadata['contexto_cliente'] ?? null,
            'historial_seguimientos' => $metadata['historial'] ?? null,
            'automaticamente_clasificado' => $metadata['auto_clasificado'] ?? false
        ];
    }

    /**
     * Accessor para obtener descripción de la acción
     */
    public function getDescripcionAccionAttribute()
    {
        $descripciones = [
            'distribuir_automatico' => 'Distribución automática a vendedor disponible',
            'asignar_vendedor' => 'Asignar a vendedor específico',
            'crear_tarea' => 'Crear tarea de seguimiento',
            'enviar_recordatorio' => 'Enviar recordatorio al responsable',
            'escalar_supervision' => 'Escalar a supervisión',
            'marcar_perdido' => 'Marcar oportunidad como perdida',
            'reagendar_seguimiento' => 'Reagendar seguimiento',
            'notificar_vencimiento' => 'Notificar vencimiento'
        ];

        return $descripciones[$this->tipo_accion] ?? $this->tipo_accion;
    }

    /**
     * Mutators
     */

    /**
     * Mutator para validar estado
     */
    public function setEstadoAttribute($value)
    {
        if (!in_array($value, self::ESTADOS)) {
            throw new \InvalidArgumentException("Estado '{$value}' no es válido.");
        }

        $this->attributes['estado'] = $value;
    }

    /**
     * Mutator para validar prioridad
     */
    public function setPrioridadAttribute($value)
    {
        if (!in_array($value, self::PRIORIDADES)) {
            throw new \InvalidArgumentException("Prioridad '{$value}' no es válida.");
        }

        $this->attributes['prioridad'] = $value;
    }

    /**
     * Mutator para validar tipo de acción
     */
    public function setTipoAccionAttribute($value)
    {
        if (!in_array($value, self::TIPOS_ACCION)) {
            throw new \InvalidArgumentException("Tipo de acción '{$value}' no es válido.");
        }

        $this->attributes['tipo_accion'] = $value;
    }

    /**
     * Métodos de negocio
     */

    /**
     * Marcar como procesado
     */
    public function marcarProcesado($usuarioId = null, $resultado = null)
    {
        $this->update([
            'procesado' => true,
            'procesado_en' => Carbon::now(),
            'procesado_por' => $usuarioId,
            'estado' => 'completado',
            'resultado' => $resultado
        ]);

        return $this;
    }

    /**
     * Marcar como en proceso
     */
    public function marcarEnProceso($usuarioId = null)
    {
        $this->update([
            'estado' => 'en_proceso',
            'procesado_por' => $usuarioId
        ]);

        return $this;
    }

    /**
     * Marcar como error
     */
    public function marcarError($mensajeError = null)
    {
        $this->update([
            'estado' => 'error',
            'resultado' => $mensajeError,
            'notas' => ($this->notas ?? '') . "\n[ERROR - " . Carbon::now()->format('d/m/Y H:i') . "] " . $mensajeError
        ]);

        return $this;
    }

    /**
     * Reagendar para nuevo procesamiento
     */
    public function reagendar($nuevaFecha, $motivo = null)
    {
        $this->update([
            'programado_para' => $nuevaFecha,
            'estado' => 'pendiente',
            'procesado' => false,
            'notas' => ($this->notas ?? '') . "\n[REAGENDADO - " . Carbon::now()->format('d/m/Y H:i') . "] Nueva fecha: " . $nuevaFecha->format('d/m/Y H:i') . ($motivo ? " - Motivo: {$motivo}" : "")
        ]);

        return $this;
    }

    /**
     * Cancelar procesamiento
     */
    public function cancelar($motivo = null)
    {
        $this->update([
            'estado' => 'cancelado',
            'procesado' => true,
            'procesado_en' => Carbon::now(),
            'resultado' => 'Cancelado' . ($motivo ? ": {$motivo}" : "")
        ]);

        return $this;
    }

    /**
     * Escalar prioridad
     */
    public function escalarPrioridad($nuevaPrioridad = null, $motivo = null)
    {
        $prioridadAnterior = $this->prioridad;
        
        if (!$nuevaPrioridad) {
            // Escalar automáticamente un nivel
            $escalas = ['baja' => 'normal', 'normal' => 'alta', 'alta' => 'urgente', 'urgente' => 'critica'];
            $nuevaPrioridad = $escalas[$this->prioridad] ?? 'critica';
        }

        $this->update([
            'prioridad' => $nuevaPrioridad,
            'notas' => ($this->notas ?? '') . "\n[ESCALADO - " . Carbon::now()->format('d/m/Y H:i') . "] Prioridad cambió de {$prioridadAnterior} a {$nuevaPrioridad}" . ($motivo ? " - Motivo: {$motivo}" : "")
        ]);

        return $this;
    }

    /**
     * Agregar metadatos de triaje
     */
    public function agregarMetadataTriaje($datos)
    {
        $metadataActual = $this->metadata_triaje ?? [];
        $metadataNueva = array_merge($metadataActual, $datos);

        $this->update([
            'metadata_triaje' => $metadataNueva
        ]);

        return $this;
    }

    /**
     * Obtener siguiente elemento de la cola para procesamiento
     */
    public static function siguienteParaProcesar()
    {
        return static::with(['cotizacion.cliente', 'cotizacion.seguimiento'])
            ->pendientes()
            ->programadosHasta(Carbon::now())
            ->ordenadoPorPrioridad()
            ->first();
    }

    /**
     * Agregar nuevo elemento a la cola con clasificación automática
     */
    public static function agregarConTriaje($cotizacionId, $configuracion = [])
    {
        $cotizacion = Cotizacion::with(['cliente', 'seguimiento'])->findOrFail($cotizacionId);
        
        // Calcular prioridad basada en reglas de negocio
        $prioridad = static::calcularPrioridad($cotizacion);
        
        // Determinar tipo de acción recomendada
        $tipoAccion = static::determinarAccion($cotizacion);
        
        // Calcular cuándo debe procesarse
        $programadoPara = static::calcularFechaProcesamiento($cotizacion, $prioridad);
        
        // Generar metadatos de triaje
        $metadataTriaje = static::generarMetadataTriaje($cotizacion, $prioridad, $tipoAccion);

        return static::create([
            'cotizacion_id' => $cotizacionId,
            'programado_para' => $programadoPara,
            'estado' => 'pendiente',
            'prioridad' => $prioridad,
            'tipo_accion' => $tipoAccion,
            'metadata_triaje' => $metadataTriaje,
            'procesado' => false
        ]);
    }

    /**
     * Calcular prioridad basada en reglas de negocio
     */
    private static function calcularPrioridad($cotizacion)
    {
        $puntuacion = 0;
        $seguimiento = $cotizacion->seguimiento;

        // Factor 1: Días de vencimiento
        if ($seguimiento && $seguimiento->proxima_gestion) {
            $diasVencidos = Carbon::now()->diffInDays($seguimiento->proxima_gestion, false);
            if ($diasVencidos > 7) $puntuacion += 5; // Muy vencido
            elseif ($diasVencidos > 3) $puntuacion += 3; // Vencido
            elseif ($diasVencidos > 0) $puntuacion += 2; // Próximo a vencer
        }

        // Factor 2: Valor de la cotización
        if ($cotizacion->total_con_iva >= 5000000) $puntuacion += 3; // Alta
        elseif ($cotizacion->total_con_iva >= 1000000) $puntuacion += 2; // Media
        elseif ($cotizacion->total_con_iva >= 500000) $puntuacion += 1; // Baja

        // Factor 3: Tipo de cliente
        if ($cotizacion->cliente) {
            switch ($cotizacion->cliente->tipo_cliente) {
                case 'Cliente Público': $puntuacion += 2; break;
                case 'Cliente Privado': $puntuacion += 1; break;
                case 'Revendedor': $puntuacion += 3; break;
            }
        }

        // Factor 4: Intentos de seguimiento fallidos
        if ($seguimiento && $seguimiento->intentos_seguimiento >= 3) {
            $puntuacion += 2;
        }

        // Determinar prioridad final
        if ($puntuacion >= 8) return 'critica';
        if ($puntuacion >= 6) return 'urgente';
        if ($puntuacion >= 4) return 'alta';
        if ($puntuacion >= 2) return 'normal';
        return 'baja';
    }

    /**
     * Determinar acción recomendada
     */
    private static function determinarAccion($cotizacion)
    {
        $seguimiento = $cotizacion->seguimiento;

        // Si no tiene vendedor asignado
        if (!$seguimiento || !$seguimiento->vendedor_id) {
            return 'distribuir_automatico';
        }

        // Si está muy vencido
        if ($seguimiento && $seguimiento->proxima_gestion) {
            $diasVencidos = Carbon::now()->diffInDays($seguimiento->proxima_gestion, false);
            if ($diasVencidos > 14) {
                return 'escalar_supervision';
            } elseif ($diasVencidos > 7) {
                return 'enviar_recordatorio';
            }
        }

        // Si tiene muchos intentos fallidos
        if ($seguimiento && $seguimiento->intentos_seguimiento >= 5) {
            return 'escalar_supervision';
        }

        return 'crear_tarea';
    }

    /**
     * Calcular fecha de procesamiento
     */
    private static function calcularFechaProcesamiento($cotizacion, $prioridad)
    {
        $ahora = Carbon::now();

        switch ($prioridad) {
            case 'critica':
                return $ahora; // Inmediatamente
            case 'urgente':
                return $ahora->addMinutes(30);
            case 'alta':
                return $ahora->addHours(2);
            case 'normal':
                return $ahora->addHours(6);
            case 'baja':
                return $ahora->addDay();
            default:
                return $ahora->addHours(4);
        }
    }

    /**
     * Generar metadatos de triaje
     */
    private static function generarMetadataTriaje($cotizacion, $prioridad, $tipoAccion)
    {
        $seguimiento = $cotizacion->seguimiento;
        
        return [
            'auto_clasificado' => true,
            'timestamp_clasificacion' => Carbon::now()->toISOString(),
            'criterios' => [
                'valor_cotizacion' => $cotizacion->total_con_iva,
                'tipo_cliente' => $cotizacion->cliente->tipo_cliente ?? null,
                'dias_vencimiento' => $seguimiento && $seguimiento->proxima_gestion ? 
                    Carbon::now()->diffInDays($seguimiento->proxima_gestion, false) : null,
                'intentos_seguimiento' => $seguimiento ? $seguimiento->intentos_seguimiento : 0
            ],
            'puntuacion_calculada' => $prioridad,
            'accion_recomendada' => $tipoAccion,
            'contexto_cliente' => [
                'id' => $cotizacion->cliente->id ?? null,
                'nombre' => $cotizacion->cliente->nombre_institucion ?? null,
                'tipo' => $cotizacion->cliente->tipo_cliente ?? null
            ],
            'contexto_cotizacion' => [
                'id' => $cotizacion->id,
                'codigo' => $cotizacion->codigo,
                'total' => $cotizacion->total_con_iva,
                'estado' => $cotizacion->estado
            ]
        ];
    }

    /**
     * Obtener estadísticas de la cola
     */
    public static function estadisticas()
    {
        return [
            'total_pendientes' => static::pendientes()->count(),
            'total_en_proceso' => static::enProceso()->count(),
            'total_completados_hoy' => static::completados()->whereDate('procesado_en', Carbon::today())->count(),
            'vencidos' => static::vencidos()->count(),
            'criticos' => static::pendientes()->where('prioridad', 'critica')->count(),
            'por_prioridad' => [
                'critica' => static::pendientes()->where('prioridad', 'critica')->count(),
                'urgente' => static::pendientes()->where('prioridad', 'urgente')->count(),
                'alta' => static::pendientes()->where('prioridad', 'alta')->count(),
                'normal' => static::pendientes()->where('prioridad', 'normal')->count(),
                'baja' => static::pendientes()->where('prioridad', 'baja')->count(),
            ],
            'por_tipo_accion' => static::pendientes()
                ->selectRaw('tipo_accion, count(*) as total')
                ->groupBy('tipo_accion')
                ->pluck('total', 'tipo_accion')
                ->toArray()
        ];
    }

    /**
     * Procesar elementos vencidos automáticamente
     */
    public static function procesarVencidos($limite = 50)
    {
        $elementosVencidos = static::vencidos()
            ->ordenadoPorPrioridad()
            ->limit($limite)
            ->get();

        $procesados = 0;
        
        foreach ($elementosVencidos as $elemento) {
            try {
                // Escalar prioridad si es muy antiguo
                if ($elemento->programado_para->diffInHours(Carbon::now()) > 24) {
                    $elemento->escalarPrioridad(null, 'Vencido por más de 24 horas');
                }

                // Marcar como en proceso para evitar duplicados
                $elemento->marcarEnProceso();
                
                $procesados++;
            } catch (\Exception $e) {
                $elemento->marcarError("Error en procesamiento automático: " . $e->getMessage());
            }
        }

        return $procesados;
    }
}