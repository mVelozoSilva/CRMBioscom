<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ConfiguracionSeguimiento extends Model
{
    use HasFactory;

    protected $table = 'configuraciones_seguimiento';

    protected $fillable = [
        'tipo_cotizacion',
        'tipo_cliente',
        'modalidad_seguimiento',
        'dias_verde',
        'dias_amarillo',
        'dias_rojo',
        'max_intentos',
        'dias_entre_intentos',
        'reglas_especiales',
        'activo',
        'prioridad_triaje',
        'descripcion',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'reglas_especiales' => 'array',
        'dias_verde' => 'integer',
        'dias_amarillo' => 'integer',
        'dias_rojo' => 'integer',
        'max_intentos' => 'integer',
        'dias_entre_intentos' => 'integer',
        'prioridad_triaje' => 'integer',
    ];

    /**
     * Tipos de cotización válidos
     */
    const TIPOS_COTIZACION = [
        'COTIZACION_INFORMATIVA',
        'COTIZACION_FORMAL',
        'PROPUESTA_TECNICA',
        'LICITACION_PUBLICA',
        'LICITACION_PRIVADA',
        'ORDEN_COMPRA_DIRECTA',
        'PRESUPUESTO_ESTIMATIVO'
    ];

    /**
     * Tipos de cliente válidos
     */
    const TIPOS_CLIENTE = [
        'Cliente Público',
        'Cliente Privado',
        'Revendedor'
    ];

    /**
     * Modalidades de seguimiento
     */
    const MODALIDADES_SEGUIMIENTO = [
        'REGULAR',
        'INTENSIVO',
        'ESPACIADO',
        'PERSONALIZADO',
        'SIN_SEGUIMIENTO'
    ];

    /**
     * Scopes
     */

    /**
     * Scope para configuraciones activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para ordenar por prioridad de triaje
     */
    public function scopeOrdenadoPorPrioridad($query)
    {
        return $query->orderBy('prioridad_triaje', 'desc');
    }

    /**
     * Scope para buscar configuración específica
     */
    public function scopeParaCombinacion($query, $tipoCotizacion, $tipoCliente, $modalidad = null)
    {
        $query->where('tipo_cotizacion', $tipoCotizacion)
              ->where('tipo_cliente', $tipoCliente);

        if ($modalidad) {
            $query->where('modalidad_seguimiento', $modalidad);
        }

        return $query->activas()->ordenadoPorPrioridad();
    }

    /**
     * Scope para configuraciones por tipo de cotización
     */
    public function scopePorTipoCotizacion($query, $tipo)
    {
        return $query->where('tipo_cotizacion', $tipo);
    }

    /**
     * Scope para configuraciones por tipo de cliente
     */
    public function scopePorTipoCliente($query, $tipo)
    {
        return $query->where('tipo_cliente', $tipo);
    }

    /**
     * Scope para configuraciones con reglas especiales
     */
    public function scopeConReglasEspeciales($query)
    {
        return $query->whereNotNull('reglas_especiales')
                    ->where('reglas_especiales', '!=', '[]');
    }

    /**
     * Accessors
     */

    /**
     * Accessor para obtener el estado visual según días
     */
    public function getEstadoVisualAttribute()
    {
        return [
            'verde' => [
                'dias' => $this->dias_verde,
                'descripcion' => 'Seguimiento normal',
                'color' => 'green',
                'clase_css' => 'bg-green-100 text-green-800'
            ],
            'amarillo' => [
                'dias' => $this->dias_amarillo,
                'descripcion' => 'Requiere atención',
                'color' => 'yellow',
                'clase_css' => 'bg-yellow-100 text-yellow-800'
            ],
            'rojo' => [
                'dias' => $this->dias_rojo,
                'descripcion' => 'Urgente - Atención inmediata',
                'color' => 'red',
                'clase_css' => 'bg-red-100 text-red-800'
            ]
        ];
    }

    /**
     * Accessor para obtener configuración completa formateada
     */
    public function getConfiguracionCompletaAttribute()
    {
        return [
            'identificacion' => [
                'tipo_cotizacion' => $this->tipo_cotizacion,
                'tipo_cliente' => $this->tipo_cliente,
                'modalidad' => $this->modalidad_seguimiento
            ],
            'timings' => [
                'verde' => $this->dias_verde,
                'amarillo' => $this->dias_amarillo,
                'rojo' => $this->dias_rojo,
                'entre_intentos' => $this->dias_entre_intentos
            ],
            'limites' => [
                'max_intentos' => $this->max_intentos
            ],
            'configuracion' => [
                'activo' => $this->activo,
                'prioridad_triaje' => $this->prioridad_triaje,
                'reglas_especiales' => $this->reglas_especiales
            ]
        ];
    }

    /**
     * Accessor para obtener descripción completa humanizada
     */
    public function getDescripcionCompletaAttribute()
    {
        $descripcion = "Configuración para {$this->tipo_cotizacion} - {$this->tipo_cliente}";
        
        if ($this->modalidad_seguimiento !== 'REGULAR') {
            $descripcion .= " (Modalidad: {$this->modalidad_seguimiento})";
        }

        $descripcion .= " - Verde: {$this->dias_verde}d, Amarillo: {$this->dias_amarillo}d, Rojo: {$this->dias_rojo}d";
        $descripcion .= " - Max intentos: {$this->max_intentos}";

        return $descripcion;
    }

    /**
     * Accessor para determinar si tiene reglas especiales
     */
    public function getTieneReglasEspecialesAttribute()
    {
        return !empty($this->reglas_especiales) && count($this->reglas_especiales) > 0;
    }

    /**
     * Accessor para obtener reglas especiales procesadas
     */
    public function getReglasEspecialesProcesadasAttribute()
    {
        if (!$this->tiene_reglas_especiales) {
            return [];
        }

        $reglas = [];
        foreach ($this->reglas_especiales as $regla) {
            $reglas[] = [
                'tipo' => $regla['tipo'] ?? 'custom',
                'condicion' => $regla['condicion'] ?? '',
                'accion' => $regla['accion'] ?? '',
                'valor' => $regla['valor'] ?? null,
                'descripcion' => $this->formatearDescripcionRegla($regla),
                'activa' => $regla['activa'] ?? true
            ];
        }

        return $reglas;
    }

    /**
     * Accessor para obtener nivel de intensidad de seguimiento
     */
    public function getNivelIntensidadAttribute()
    {
        $puntuacion = 0;

        // Menor cantidad de días = mayor intensidad
        $puntuacion += (10 - $this->dias_verde) * 0.3;
        $puntuacion += (10 - $this->dias_amarillo) * 0.2;
        $puntuacion += (15 - $this->dias_rojo) * 0.1;

        // Más intentos = mayor intensidad
        $puntuacion += $this->max_intentos * 0.2;

        // Menos días entre intentos = mayor intensidad
        $puntuacion += (7 - $this->dias_entre_intentos) * 0.2;

        if ($puntuacion >= 8) return 'muy_alta';
        if ($puntuacion >= 6) return 'alta';
        if ($puntuacion >= 4) return 'media';
        if ($puntuacion >= 2) return 'baja';
        return 'muy_baja';
    }

    /**
     * Mutators
     */

    /**
     * Mutator para validar tipo de cotización
     */
    public function setTipoCotizacionAttribute($value)
    {
        if (!in_array($value, self::TIPOS_COTIZACION)) {
            throw new \InvalidArgumentException("Tipo de cotización '{$value}' no es válido.");
        }

        $this->attributes['tipo_cotizacion'] = $value;
    }

    /**
     * Mutator para validar tipo de cliente
     */
    public function setTipoClienteAttribute($value)
    {
        if (!in_array($value, self::TIPOS_CLIENTE)) {
            throw new \InvalidArgumentException("Tipo de cliente '{$value}' no es válido.");
        }

        $this->attributes['tipo_cliente'] = $value;
    }

    /**
     * Mutator para validar modalidad de seguimiento
     */
    public function setModalidadSeguimientoAttribute($value)
    {
        if (!in_array($value, self::MODALIDADES_SEGUIMIENTO)) {
            throw new \InvalidArgumentException("Modalidad de seguimiento '{$value}' no es válida.");
        }

        $this->attributes['modalidad_seguimiento'] = $value;
    }

    /**
     * Mutator para validar coherencia de días
     */
    public function setDiasVerdeAttribute($value)
    {
        if ($value < 0 || $value > 30) {
            throw new \InvalidArgumentException("Días verde debe estar entre 0 y 30.");
        }

        $this->attributes['dias_verde'] = $value;
    }

    public function setDiasAmarilloAttribute($value)
    {
        if ($value < 0 || $value > 30) {
            throw new \InvalidArgumentException("Días amarillo debe estar entre 0 y 30.");
        }

        $this->attributes['dias_amarillo'] = $value;
    }

    public function setDiasRojoAttribute($value)
    {
        if ($value < 0 || $value > 60) {
            throw new \InvalidArgumentException("Días rojo debe estar entre 0 y 60.");
        }

        $this->attributes['dias_rojo'] = $value;
    }

    /**
     * Métodos de negocio
     */

    /**
     * Buscar configuración aplicable para una cotización
     */
    public static function buscarConfiguracionAplicable($tipoCotizacion, $tipoCliente, $modalidad = 'REGULAR')
    {
        // Buscar coincidencia exacta primero
        $configuracion = static::paraCombinacion($tipoCotizacion, $tipoCliente, $modalidad)->first();

        if (!$configuracion) {
            // Buscar sin modalidad específica
            $configuracion = static::paraCombinacion($tipoCotizacion, $tipoCliente)->first();
        }

        if (!$configuracion) {
            // Buscar configuración genérica por tipo de cliente
            $configuracion = static::porTipoCliente($tipoCliente)
                ->where('tipo_cotizacion', 'COTIZACION_INFORMATIVA')
                ->activas()
                ->ordenadoPorPrioridad()
                ->first();
        }

        if (!$configuracion) {
            // Usar configuración por defecto
            $configuracion = static::obtenerConfiguracionPorDefecto();
        }

        return $configuracion;
    }

    /**
     * Obtener configuración por defecto si no existe ninguna
     */
    public static function obtenerConfiguracionPorDefecto()
    {
        return new static([
            'tipo_cotizacion' => 'COTIZACION_INFORMATIVA',
            'tipo_cliente' => 'Cliente Privado',
            'modalidad_seguimiento' => 'REGULAR',
            'dias_verde' => 1,
            'dias_amarillo' => 3,
            'dias_rojo' => 7,
            'max_intentos' => 5,
            'dias_entre_intentos' => 2,
            'activo' => true,
            'prioridad_triaje' => 50,
            'descripcion' => 'Configuración por defecto del sistema'
        ]);
    }

    /**
     * Determinar estado de urgencia basado en días transcurridos
     */
    public function determinarEstadoUrgencia($diasTranscurridos)
    {
        if ($diasTranscurridos <= $this->dias_verde) {
            return [
                'estado' => 'verde',
                'nivel' => 'normal',
                'descripcion' => 'Seguimiento en tiempo normal',
                'accion_recomendada' => 'seguimiento_regular',
                'clase_css' => 'bg-green-100 text-green-800'
            ];
        } elseif ($diasTranscurridos <= $this->dias_amarillo) {
            return [
                'estado' => 'amarillo',
                'nivel' => 'atencion',
                'descripcion' => 'Requiere atención próxima',
                'accion_recomendada' => 'seguimiento_priorizado',
                'clase_css' => 'bg-yellow-100 text-yellow-800'
            ];
        } elseif ($diasTranscurridos <= $this->dias_rojo) {
            return [
                'estado' => 'rojo',
                'nivel' => 'urgente',
                'descripcion' => 'Atención urgente requerida',
                'accion_recomendada' => 'seguimiento_inmediato',
                'clase_css' => 'bg-red-100 text-red-800'
            ];
        } else {
            return [
                'estado' => 'critico',
                'nivel' => 'critico',
                'descripcion' => 'Seguimiento crítico - Escalar',
                'accion_recomendada' => 'escalar_supervision',
                'clase_css' => 'bg-red-600 text-white'
            ];
        }
    }

    /**
     * Calcular próxima fecha de seguimiento
     */
    public function calcularProximaFecha($fechaUltimaGestion = null, $intentosActuales = 0)
    {
        $fechaBase = $fechaUltimaGestion ? Carbon::parse($fechaUltimaGestion) : Carbon::now();
        
        // Si supera max intentos, escalar tiempo
        if ($intentosActuales >= $this->max_intentos) {
            $diasAdicionales = $this->dias_entre_intentos * 2; // Duplicar tiempo entre intentos
        } else {
            $diasAdicionales = $this->dias_entre_intentos;
        }

        return $fechaBase->addDays($diasAdicionales);
    }

    /**
     * Verificar si debe escalarse a supervisión
     */
    public function debeEscalarseSupervision($intentosActuales, $diasTranscurridos)
    {
        // Escalar si supera máximo de intentos
        if ($intentosActuales >= $this->max_intentos) {
            return true;
        }

        // Escalar si está en estado crítico
        if ($diasTranscurridos > $this->dias_rojo) {
            return true;
        }

        // Verificar reglas especiales
        if ($this->tiene_reglas_especiales) {
            foreach ($this->reglas_especiales as $regla) {
                if ($this->evaluarReglaEscalacion($regla, $intentosActuales, $diasTranscurridos)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Aplicar reglas especiales
     */
    public function aplicarReglasEspeciales($cotizacion, $seguimiento = null)
    {
        if (!$this->tiene_reglas_especiales) {
            return [];
        }

        $resultados = [];
        
        foreach ($this->reglas_especiales as $regla) {
            if ($regla['activa'] ?? true) {
                $resultado = $this->procesarReglaEspecial($regla, $cotizacion, $seguimiento);
                if ($resultado) {
                    $resultados[] = $resultado;
                }
            }
        }

        return $resultados;
    }

    /**
     * Clonar configuración con modificaciones
     */
    public function clonarConModificaciones($modificaciones = [])
    {
        $nuevaConfiguracion = $this->replicate(['id', 'created_at', 'updated_at']);
        
        foreach ($modificaciones as $campo => $valor) {
            if (in_array($campo, $this->fillable)) {
                $nuevaConfiguracion->{$campo} = $valor;
            }
        }

        // Marcar como inactiva la configuración original si se especifica
        if (isset($modificaciones['_desactivar_original']) && $modificaciones['_desactivar_original']) {
            $this->update(['activo' => false]);
        }

        $nuevaConfiguracion->save();
        
        return $nuevaConfiguracion;
    }

    /**
     * Validar coherencia de la configuración
     */
    public function validarCoherencia()
    {
        $errores = [];

        // Validar secuencia lógica de días
        if ($this->dias_verde >= $this->dias_amarillo) {
            $errores[] = 'Los días verdes deben ser menores que los días amarillos';
        }

        if ($this->dias_amarillo >= $this->dias_rojo) {
            $errores[] = 'Los días amarillos deben ser menores que los días rojos';
        }

        // Validar límites razonables
        if ($this->max_intentos > 10) {
            $errores[] = 'El máximo de intentos no debería exceder 10';
        }

        if ($this->dias_entre_intentos > 14) {
            $errores[] = 'Los días entre intentos no deberían exceder 14';
        }

        // Validar reglas especiales
        if ($this->tiene_reglas_especiales) {
            foreach ($this->reglas_especiales as $index => $regla) {
                if (empty($regla['tipo']) || empty($regla['condicion'])) {
                    $errores[] = "Regla especial #{$index} está incompleta";
                }
            }
        }

        return $errores;
    }

    /**
     * Métodos auxiliares privados
     */

    private function formatearDescripcionRegla($regla)
    {
        $tipo = $regla['tipo'] ?? 'custom';
        $condicion = $regla['condicion'] ?? '';
        $valor = $regla['valor'] ?? '';

        switch ($tipo) {
            case 'monto_mayor':
                return "Si el monto es mayor a $" . number_format($valor) . ", {$regla['accion']}";
            case 'tipo_cliente':
                return "Si el cliente es tipo '{$valor}', {$regla['accion']}";
            case 'dias_transcurridos':
                return "Si han transcurrido más de {$valor} días, {$regla['accion']}";
            case 'intentos_fallidos':
                return "Si hay más de {$valor} intentos fallidos, {$regla['accion']}";
            default:
                return $condicion;
        }
    }

    private function evaluarReglaEscalacion($regla, $intentosActuales, $diasTranscurridos)
    {
        if (!($regla['activa'] ?? true)) {
            return false;
        }

        $tipo = $regla['tipo'] ?? '';
        $valor = $regla['valor'] ?? 0;

        switch ($tipo) {
            case 'intentos_fallidos':
                return $intentosActuales >= $valor;
            case 'dias_transcurridos':
                return $diasTranscurridos >= $valor;
            default:
                return false;
        }
    }

    private function procesarReglaEspecial($regla, $cotizacion, $seguimiento)
    {
        if (!($regla['activa'] ?? true)) {
            return null;
        }

        $tipo = $regla['tipo'] ?? '';
        $condicion = $regla['condicion'] ?? '';
        $accion = $regla['accion'] ?? '';
        $valor = $regla['valor'] ?? null;

        // Evaluar condición
        $condicionCumplida = false;

        switch ($tipo) {
            case 'monto_mayor':
                $condicionCumplida = $cotizacion->total_con_iva > $valor;
                break;
            case 'tipo_cliente':
                $condicionCumplida = $cotizacion->cliente && $cotizacion->cliente->tipo_cliente === $valor;
                break;
            case 'dias_transcurridos':
                if ($seguimiento && $seguimiento->proxima_gestion) {
                    $dias = Carbon::now()->diffInDays($seguimiento->proxima_gestion);
                    $condicionCumplida = $dias >= $valor;
                }
                break;
        }

        if ($condicionCumplida) {
            return [
                'regla' => $regla,
                'accion' => $accion,
                'descripcion' => $this->formatearDescripcionRegla($regla),
                'aplicada_en' => Carbon::now()
            ];
        }

        return null;
    }

    /**
     * Métodos estáticos para consultas comunes
     */

    /**
     * Obtener todas las configuraciones agrupadas por tipo
     */
    public static function obtenerConfiguracionesAgrupadas()
    {
        return static::activas()
            ->ordenadoPorPrioridad()
            ->get()
            ->groupBy(['tipo_cotizacion', 'tipo_cliente']);
    }

    /**
     * Crear configuración estándar para un nuevo tipo
     */
    public static function crearConfiguracionEstandar($tipoCotizacion, $tipoCliente, $modalidad = 'REGULAR')
    {
        // Configuraciones base según tipo de cotización
        $configuracionesBase = [
            'COTIZACION_INFORMATIVA' => ['verde' => 1, 'amarillo' => 3, 'rojo' => 7, 'intentos' => 5],
            'COTIZACION_FORMAL' => ['verde' => 2, 'amarillo' => 5, 'rojo' => 10, 'intentos' => 7],
            'LICITACION_PUBLICA' => ['verde' => 3, 'amarillo' => 7, 'rojo' => 15, 'intentos' => 3],
            'LICITACION_PRIVADA' => ['verde' => 2, 'amarillo' => 5, 'rojo' => 12, 'intentos' => 5],
        ];

        $base = $configuracionesBase[$tipoCotizacion] ?? $configuracionesBase['COTIZACION_INFORMATIVA'];

        return static::create([
            'tipo_cotizacion' => $tipoCotizacion,
            'tipo_cliente' => $tipoCliente,
            'modalidad_seguimiento' => $modalidad,
            'dias_verde' => $base['verde'],
            'dias_amarillo' => $base['amarillo'],
            'dias_rojo' => $base['rojo'],
            'max_intentos' => $base['intentos'],
            'dias_entre_intentos' => 2,
            'activo' => true,
            'prioridad_triaje' => 50,
            'descripcion' => "Configuración estándar para {$tipoCotizacion} - {$tipoCliente}"
        ]);
    }

    /**
     * Obtener estadísticas de uso de configuraciones
     */
    public static function estadisticasUso()
    {
        $configuraciones = static::with(['cotizaciones' => function($query) {
            $query->whereDate('created_at', '>=', Carbon::now()->subMonth());
        }])->get();

        return [
            'total_configuraciones' => $configuraciones->count(),
            'activas' => $configuraciones->where('activo', true)->count(),
            'con_reglas_especiales' => $configuraciones->filter->tiene_reglas_especiales->count(),
            'uso_ultimo_mes' => $configuraciones->sum(function($config) {
                return $config->cotizaciones->count();
            }),
            'por_tipo_cotizacion' => $configuraciones->groupBy('tipo_cotizacion')
                ->map->count()
                ->toArray(),
            'por_tipo_cliente' => $configuraciones->groupBy('tipo_cliente')
                ->map->count()
                ->toArray()
        ];
    }
}