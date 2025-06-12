<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Tarea extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tareas';

    protected $fillable = [
        'titulo',
        'descripcion',
        'usuario_id',
        'tipo',
        'origen',
        'seguimiento_id',
        'cotizacion_id',
        'cliente_id',
        'fecha_tarea',
        'hora_inicio',
        'hora_fin',
        'duracion_estimada',
        'estado',
        'prioridad',
        'tiene_recordatorio',
        'recordatorio_en',
        'tipo_recordatorio',
        'metadata_distribucion',
        'es_distribuida_automaticamente',
        'intentos_completar',
        'notas',
        'resultado',
        'completada_en',
        'configuracion_rol',
    ];

    protected $casts = [
        'fecha_tarea' => 'date',
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
        'tiene_recordatorio' => 'boolean',
        'recordatorio_en' => 'datetime',
        'es_distribuida_automaticamente' => 'boolean',
        'completada_en' => 'datetime',
        'metadata_distribucion' => 'array',
        'configuracion_rol' => 'array',
    ];

    protected $dates = [
        'fecha_tarea',
        'recordatorio_en',
        'completada_en',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Estados válidos para las tareas
     */
    const ESTADOS = [
        'pendiente',
        'en_progreso',
        'completada',
        'cancelada',
        'pospuesta'
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
     * Orígenes de las tareas
     */
    const ORIGENES = [
        'manual',
        'automatica_seguimiento',
        'automatica_cotizacion',
        'automatica_triaje',
        'distribucion_masiva'
    ];

    /**
     * Tipos de recordatorios
     */
    const TIPOS_RECORDATORIO = [
        'email',
        'sistema',
        'popup'
    ];

    /**
     * Relaciones
     */

    /**
     * Usuario asignado a la tarea
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
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
     * Scopes
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
        return $query->where('usuario_id', $usuarioId);
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
        return $query->whereDate('fecha_tarea', Carbon::today());
    }

    /**
     * Scope para tareas de esta semana
     */
    public function scopeEstaSemana($query)
    {
        return $query->whereBetween('fecha_tarea', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    /**
     * Scope para tareas vencidas
     */
    public function scopeVencidas($query)
    {
        return $query->where('fecha_tarea', '<', Carbon::today())
                    ->whereIn('estado', ['pendiente', 'en_progreso']);
    }

    /**
     * Scope para tareas próximas (siguiente X días)
     */
    public function scopeProximas($query, $dias = 7)
    {
        return $query->whereBetween('fecha_tarea', [
            Carbon::today(),
            Carbon::today()->addDays($dias)
        ]);
    }

    /**
     * Scope para tareas por rango de fechas
     */
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_tarea', [$fechaInicio, $fechaFin]);
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
     * Scope para tareas con recordatorio
     */
    public function scopeConRecordatorio($query)
    {
        return $query->where('tiene_recordatorio', true);
    }

    /**
     * Scope para tareas que requieren recordatorio hoy
     */
    public function scopeRecordatoriosHoy($query)
    {
        return $query->where('tiene_recordatorio', true)
                    ->whereDate('recordatorio_en', Carbon::today())
                    ->whereIn('estado', ['pendiente', 'en_progreso']);
    }

    /**
     * Accessors
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
            'pospuesta' => 'gray'
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

        return $this->fecha_tarea < Carbon::today();
    }

    /**
     * Accessor para determinar si la tarea es de hoy
     */
    public function getEsHoyAttribute()
    {
        return $this->fecha_tarea->isToday();
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
        $fechaTarea = $this->fecha_tarea;

        if ($fechaTarea->isToday()) {
            return 0;
        } elseif ($fechaTarea->isPast()) {
            return -$hoy->diffInDays($fechaTarea);
        } else {
            return $hoy->diffInDays($fechaTarea);
        }
    }

    /**
     * Accessor para obtener texto de estado humanizado
     */
    public function getEstadoHumanoAttribute()
    {
        $estados = [
            'pendiente' => 'Pendiente',
            'en_progreso' => 'En Progreso',
            'completada' => 'Completada',
            'cancelada' => 'Cancelada',
            'pospuesta' => 'Pospuesta'
        ];

        return $estados[$this->estado] ?? $this->estado;
    }

    /**
     * Accessor para obtener texto de prioridad humanizado
     */
    public function getPrioridadHumanaAttribute()
    {
        $prioridades = [
            'baja' => 'Baja',
            'media' => 'Media',
            'alta' => 'Alta',
            'urgente' => 'Urgente'
        ];

        return $prioridades[$this->prioridad] ?? $this->prioridad;
    }

    /**
     * Accessor para obtener texto de tipo humanizado
     */
    public function getTipoHumanoAttribute()
    {
        $tipos = [
            'seguimiento' => 'Seguimiento',
            'cotizacion' => 'Cotización',
            'mantencion' => 'Mantención',
            'cobranza' => 'Cobranza',
            'reunion' => 'Reunión',
            'llamada' => 'Llamada',
            'email' => 'Email',
            'visita' => 'Visita',
            'administrativa' => 'Administrativa',
            'personal' => 'Personal'
        ];

        return $tipos[$this->tipo] ?? $this->tipo;
    }

    /**
     * Accessor para obtener duración formateada
     */
    public function getDuracionFormateadaAttribute()
    {
        if (!$this->duracion_estimada) {
            return null;
        }

        $horas = intval($this->duracion_estimada / 60);
        $minutos = $this->duracion_estimada % 60;

        if ($horas > 0) {
            return $horas . 'h ' . ($minutos > 0 ? $minutos . 'm' : '');
        }

        return $minutos . 'm';
    }

    /**
     * Accessor para obtener información de distribución
     */
    public function getInfoDistribucionAttribute()
    {
        if (!$this->es_distribuida_automaticamente || !$this->metadata_distribucion) {
            return null;
        }

        return $this->metadata_distribucion;
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
     * Mutator para validar tipo
     */
    public function setTipoAttribute($value)
    {
        if (!in_array($value, self::TIPOS)) {
            throw new \InvalidArgumentException("Tipo '{$value}' no es válido.");
        }

        $this->attributes['tipo'] = $value;
    }

    /**
     * Métodos de negocio
     */

    /**
     * Marcar tarea como completada
     */
    public function completar($resultado = null, $usuarioId = null)
    {
        $this->update([
            'estado' => 'completada',
            'completada_en' => Carbon::now(),
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
        $fechaAnterior = $this->fecha_tarea;
        
        $this->update([
            'fecha_tarea' => $nuevaFecha,
            'estado' => 'pospuesta',
            'notas' => $this->notas . "\n[" . Carbon::now()->format('d/m/Y H:i') . "] Pospuesta de {$fechaAnterior->format('d/m/Y')} a {$nuevaFecha->format('d/m/Y')}" . ($motivo ? " - Motivo: {$motivo}" : "")
        ]);

        return $this;
    }

    /**
     * Iniciar tarea
     */
    public function iniciar()
    {
        $this->update([
            'estado' => 'en_progreso',
            'hora_inicio' => Carbon::now()
        ]);

        return $this;
    }

    /**
     * Cancelar tarea
     */
    public function cancelar($motivo = null)
    {
        $this->update([
            'estado' => 'cancelada',
            'notas' => $this->notas . "\n[" . Carbon::now()->format('d/m/Y H:i') . "] Cancelada" . ($motivo ? " - Motivo: {$motivo}" : "")
        ]);

        return $this;
    }

    /**
     * Configurar recordatorio
     */
    public function configurarRecordatorio($fechaHora, $tipo = 'sistema')
    {
        $this->update([
            'tiene_recordatorio' => true,
            'recordatorio_en' => $fechaHora,
            'tipo_recordatorio' => $tipo
        ]);

        return $this;
    }

    /**
     * Calcular duración real si tiene hora inicio y fin
     */
    public function calcularDuracionReal()
    {
        if (!$this->hora_inicio || !$this->hora_fin) {
            return null;
        }

        $inicio = Carbon::parse($this->hora_inicio);
        $fin = Carbon::parse($this->hora_fin);

        return $fin->diffInMinutes($inicio);
    }

    /**
     * Verificar si puede ser editada
     */
    public function puedeSerEditada()
    {
        return !in_array($this->estado, ['completada', 'cancelada']);
    }

    /**
     * Verificar si puede ser eliminada
     */
    public function puedeSerEliminada()
    {
        // No se pueden eliminar tareas automáticas completadas
        if ($this->es_distribuida_automaticamente && $this->estado === 'completada') {
            return false;
        }

        return true;
    }

    /**
     * Duplicar tarea para otra fecha
     */
    public function duplicar($nuevaFecha, $usuarioId = null)
    {
        $nuevaTarea = $this->replicate([
            'fecha_tarea',
            'estado',
            'completada_en',
            'resultado',
            'hora_inicio',
            'hora_fin',
            'recordatorio_en'
        ]);

        $nuevaTarea->fecha_tarea = $nuevaFecha;
        $nuevaTarea->estado = 'pendiente';
        $nuevaTarea->es_distribuida_automaticamente = false;
        $nuevaTarea->origen = 'manual';

        if ($usuarioId) {
            $nuevaTarea->usuario_id = $usuarioId;
        }

        $nuevaTarea->save();

        return $nuevaTarea;
    }

    /**
     * Obtener resumen de la tarea para notificaciones
     */
    public function getResumenAttribute()
    {
        $resumen = $this->titulo;
        
        if ($this->cliente) {
            $resumen .= " - {$this->cliente->nombre_institucion}";
        }

        if ($this->es_vencida) {
            $diasVencidos = abs($this->dias_restantes);
            $resumen .= " (Vencida hace {$diasVencidos} día" . ($diasVencidos !== 1 ? 's' : '') . ")";
        } elseif ($this->es_hoy) {
            $resumen .= " (Hoy)";
        } elseif ($this->dias_restantes > 0) {
            $resumen .= " (En {$this->dias_restantes} día" . ($this->dias_restantes !== 1 ? 's' : '') . ")";
        }

        return $resumen;
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
            ->orderBy('hora_inicio', 'asc')
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

        return $query->sum('duracion_estimada') ?? 0;
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