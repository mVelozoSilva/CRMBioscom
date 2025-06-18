<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Tarea extends Model
{
    use HasFactory; // Removemos SoftDeletes por ahora ya que la tabla no tiene deleted_at

    protected $table = 'tareas';

    protected $fillable = [
        'titulo',
        'descripcion',
        'usuario_id',                    // Mantener por compatibilidad
        'usuario_asignado_id',           // Real en BD
        'usuario_creador_id',            // Real en BD
        'tipo',
        'origen',
        'seguimiento_id',
        'cotizacion_id',
        'cliente_id',
        'fecha_vencimiento',             // Real en BD (no fecha_tarea)
        'hora_estimada',                 // Real en BD (no hora_inicio)
        'duracion_estimada_minutos',     // Real en BD (no duracion_estimada)
        'estado',
        'prioridad',
        'metadatos',                     // Real en BD (no metadata_distribucion)
        'es_distribuida_automaticamente',
        'notas',
        'resultado',
        'fecha_completada',              // Real en BD (no completada_en)
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',   // Cambiado de fecha_tarea
        'hora_estimada' => 'datetime:H:i',
        'es_distribuida_automaticamente' => 'boolean',
        'fecha_completada' => 'datetime', // Cambiado de completada_en
        'metadatos' => 'array',          // Cambiado de metadata_distribucion
    ];

    protected $dates = [
        'fecha_vencimiento',             // Cambiado
        'fecha_completada',              // Cambiado
        'created_at',
        'updated_at',
    ];

    /**
     * Estados válidos para las tareas
     */
    const ESTADOS = [
        'pendiente',
        'en_progreso',
        'completada',
        'cancelada',
        'pospuesta',
        'vencida'  // Agregado según BD
    ];

    /**
     * Prioridades válidas para las tareas
     */
    const PRIORIDADES = [
        'baja',
        'media',
        'alta',
        'urgente'
    ];

    /**
     * Tipos de tareas disponibles
     */
    const TIPOS = [
        'seguimiento',
        'cotizacion',
        'mantencion',
        'cobranza',
        'reunion',
        'llamada',
        'email',
        'visita',
        'administrativa',
        'personal'
    ];

    /**
     * Orígenes de las tareas (según BD real)
     */
    const ORIGENES = [
        'manual',
        'distribucion_masiva',
        'distribucion_automatica',
        'sistema',
        'integracion',
        'distribucion_masiva_fase1a'
    ];

    /**
     * Relaciones
     */

    /**
     * Usuario asignado a la tarea
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_asignado_id'); // Cambiado
    }

    /**
     * Usuario creador de la tarea
     */
    public function usuarioCreador()
    {
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }

    /**
     * Seguimiento relacionado (si aplica)
     */
    public function seguimiento()
    {
        return $this->belongsTo(Seguimiento::class, 'seguimiento_id');
    }

    /**
     * Cotización relacionada (si aplica)
     */
    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    /**
     * Cliente relacionado (si aplica)
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Scopes corregidos
     */

    /**
     * Scope para tareas pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    /**
     * Scope para tareas en progreso
     */
    public function scopeEnProgreso($query)
    {
        return $query->where('estado', 'en_progreso');
    }

    /**
     * Scope para tareas completadas
     */
    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'completada');
    }

    /**
     * Scope para tareas de un usuario específico
     */
    public function scopeDeUsuario($query, $usuarioId)
    {
        return $query->where('usuario_asignado_id', $usuarioId); // Corregido
    }

    /**
     * Scope para tareas por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para tareas por prioridad
     */
    public function scopePorPrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    /**
     * Scope para tareas de hoy
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_vencimiento', Carbon::today()); // Corregido
    }

    /**
     * Scope para tareas de esta semana
     */
    public function scopeEstaSemana($query)
    {
        return $query->whereBetween('fecha_vencimiento', [ // Corregido
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    /**
     * Scope para tareas vencidas
     */
    public function scopeVencidas($query)
    {
        return $query->where('fecha_vencimiento', '<', Carbon::today()) // Corregido
                    ->whereIn('estado', ['pendiente', 'en_progreso']);
    }

    /**
     * Scope para tareas próximas (siguiente X días)
     */
    public function scopeProximas($query, $dias = 7)
    {
        return $query->whereBetween('fecha_vencimiento', [ // Corregido
            Carbon::today(),
            Carbon::today()->addDays($dias)
        ]);
    }

    /**
     * Scope para tareas por rango de fechas
     */
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_vencimiento', [$fechaInicio, $fechaFin]); // Corregido
    }

    /**
     * Scope para tareas automáticas
     */
    public function scopeAutomaticas($query)
    {
        return $query->where('es_distribuida_automaticamente', true);
    }

    /**
     * Scope para tareas manuales
     */
    public function scopeManuales($query)
    {
        return $query->where('es_distribuida_automaticamente', false);
    }

    /**
     * Accessors corregidos
     */

    /**
     * Accessor para obtener el color según el estado
     */
    public function getColorEstadoAttribute()
    {
        $colores = [
            'pendiente' => 'yellow',
            'en_progreso' => 'blue',
            'completada' => 'green',
            'cancelada' => 'red',
            'pospuesta' => 'gray',
            'vencida' => 'red'
        ];

        return $colores[$this->estado] ?? 'gray';
    }

    /**
     * Accessor para obtener el color según la prioridad
     */
    public function getColorPrioridadAttribute()
    {
        $colores = [
            'baja' => 'green',
            'media' => 'yellow',
            'alta' => 'orange',
            'urgente' => 'red'
        ];

        return $colores[$this->prioridad] ?? 'gray';
    }

    /**
     * Accessor para determinar si la tarea está vencida
     */
    public function getEsVencidaAttribute()
    {
        if (in_array($this->estado, ['completada', 'cancelada'])) {
            return false;
        }

        return $this->fecha_vencimiento < Carbon::today(); // Corregido
    }

    /**
     * Accessor para determinar si la tarea es de hoy
     */
    public function getEsHoyAttribute()
    {
        return $this->fecha_vencimiento->isToday(); // Corregido
    }

    /**
     * Accessor para obtener días restantes/vencidos
     */
    public function getDiasRestantesAttribute()
    {
        if (in_array($this->estado, ['completada', 'cancelada'])) {
            return null;
        }

        $hoy = Carbon::today();
        $fechaTarea = $this->fecha_vencimiento; // Corregido

        if ($fechaTarea->isToday()) {
            return 0;
        } elseif ($fechaTarea->isPast()) {
            return -$hoy->diffInDays($fechaTarea);
        } else {
            return $hoy->diffInDays($fechaTarea);
        }
    }

    /**
     * Accessor para obtener duración formateada
     */
    public function getDuracionFormateadaAttribute()
    {
        if (!$this->duracion_estimada_minutos) { // Corregido nombre de columna
            return null;
        }

        $horas = intval($this->duracion_estimada_minutos / 60);
        $minutos = $this->duracion_estimada_minutos % 60;

        if ($horas > 0) {
            return $horas . 'h ' . ($minutos > 0 ? $minutos . 'm' : '');
        }

        return $minutos . 'm';
    }

    /**
     * Métodos de negocio corregidos
     */

    /**
     * Marcar tarea como completada
     */
    public function completar($resultado = null, $usuarioId = null)
    {
        $this->update([
            'estado' => 'completada',
            'fecha_completada' => Carbon::now(), // Corregido
            'resultado' => $resultado,
        ]);

        // Actualizar seguimiento relacionado si existe
        if ($this->seguimiento_id && $this->tipo === 'seguimiento') {
            $this->seguimiento()->update([
                'estado' => 'completado',
                'ultima_gestion' => Carbon::today(),
                'resultado_ultima_gestion' => $resultado
            ]);
        }

        return $this;
    }

    /**
     * Posponer tarea a una nueva fecha
     */
    public function posponer($nuevaFecha, $motivo = null)
    {
        $fechaAnterior = $this->fecha_vencimiento; // Corregido
        
        $this->update([
            'fecha_vencimiento' => $nuevaFecha, // Corregido
            'estado' => 'pospuesta',
            'notas' => $this->notas . "\n[" . Carbon::now()->format('d/m/Y H:i') . "] Pospuesta de {$fechaAnterior->format('d/m/Y')} a {$nuevaFecha->format('d/m/Y')}" . ($motivo ? " - Motivo: {$motivo}" : "")
        ]);

        return $this;
    }

    /**
     * Métodos estáticos para consultas comunes
     */

    /**
     * Obtener tareas del dashboard para un usuario
     */
    public static function paraUsuarioHoy($usuarioId)
    {
        return static::with(['cliente', 'seguimiento', 'cotizacion'])
            ->deUsuario($usuarioId)
            ->hoy()
            ->whereIn('estado', ['pendiente', 'en_progreso'])
            ->orderBy('prioridad', 'desc')
            ->orderBy('hora_estimada', 'asc') // Corregido
            ->get();
    }

    /**
     * Obtener carga de trabajo de un usuario
     */
    public static function cargaTrabajoUsuario($usuarioId, $fechaInicio = null, $fechaFin = null)
    {
        $query = static::deUsuario($usuarioId)
            ->whereIn('estado', ['pendiente', 'en_progreso']);

        if ($fechaInicio && $fechaFin) {
            $query->entreFechas($fechaInicio, $fechaFin);
        } else {
            $query->estaSemana();
        }

        return $query->sum('duracion_estimada_minutos') ?? 0; // Corregido
    }

    /**
     * Obtener estadísticas de tareas
     */
    public static function estadisticas($usuarioId = null, $fechaInicio = null, $fechaFin = null)
    {
        $query = static::query();

        if ($usuarioId) {
            $query->deUsuario($usuarioId);
        }

        if ($fechaInicio && $fechaFin) {
            $query->entreFechas($fechaInicio, $fechaFin);
        } else {
            $query->estaSemana();
        }

        return [
            'total' => $query->count(),
            'pendientes' => (clone $query)->pendientes()->count(),
            'en_progreso' => (clone $query)->enProgreso()->count(),
            'completadas' => (clone $query)->completadas()->count(),
            'vencidas' => (clone $query)->vencidas()->count(),
            'hoy' => (clone $query)->hoy()->count(),
        ];
    }
}