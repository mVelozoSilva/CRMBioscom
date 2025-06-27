<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Tarea extends Model
{
    use HasFactory;

    protected $table = 'tareas';

    protected $fillable = [
        'titulo',
        'descripcion',
        'usuario_asignado_id',
        'usuario_creador_id',
        'tipo',
        'origen',
        'seguimiento_id',
        'cotizacion_id',
        'cliente_id',
        'fecha_vencimiento',
        'hora_estimada',
        'duracion_estimada_minutos',
        'estado',
        'prioridad',
        'metadatos',
        'es_distribuida_automaticamente',
        'notas',
        'resultado',
        'fecha_completada'
    ];

    protected $casts = [
        'metadatos' => 'array',
        'fecha_vencimiento' => 'date',
        'hora_estimada' => 'datetime:H:i',
        'fecha_completada' => 'datetime',
        'es_distribuida_automaticamente' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Constantes para validación y consistencia
    const TIPOS_TAREA = [
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

    const ORIGENES_TAREA = [
        'manual',
        'distribucion_masiva',
        'distribucion_automatica',
        'sistema',
        'integracion',
        'distribucion_masiva_fase1a'
    ];

    const ESTADOS_TAREA = [
        'pendiente',
        'en_progreso',
        'completada',
        'cancelada',
        'pospuesta',
        'vencida'
    ];

    const PRIORIDADES_TAREA = [
        'baja',
        'media',
        'alta',
        'urgente'
    ];

    // Relaciones
    
    /**
     * Usuario asignado a la tarea
     */
    public function usuarioAsignado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_asignado_id');
    }

    /**
     * Usuario que creó la tarea
     */
    public function usuarioCreador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }

    /**
     * Seguimiento relacionado (si aplica)
     */
    public function seguimiento(): BelongsTo
    {
        return $this->belongsTo(Seguimiento::class, 'seguimiento_id');
    }

    /**
     * Cotización relacionada (si aplica)
     */
    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    /**
     * Cliente relacionado (si aplica)
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // Scopes y métodos de consulta

    /**
     * Scope para tareas de hoy
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_vencimiento', Carbon::today());
    }

    /**
     * Scope para tareas de esta semana
     */
    public function scopeEstaSemana($query)
    {
        return $query->whereBetween('fecha_vencimiento', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    /**
     * Scope para tareas pendientes
     */
    public function scopePendientes($query)
    {
        return $query->whereIn('estado', ['pendiente', 'en_progreso']);
    }

    /**
     * Scope para tareas vencidas
     */
    public function scopeVencidas($query)
    {
        return $query->where('fecha_vencimiento', '<', Carbon::today())
                    ->whereIn('estado', ['pendiente', 'en_progreso']);
    }

    /**
     * Scope para tareas por usuario
     */
    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_asignado_id', $usuarioId);
    }

    /**
     * Scope para tareas por prioridad
     */
    public function scopePorPrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    /**
     * Scope para tareas por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    // Métodos auxiliares

    /**
     * Determina si la tarea está vencida
     */
    public function getEsVencidaAttribute(): bool
    {
        return $this->fecha_vencimiento < Carbon::today() && 
               in_array($this->estado, ['pendiente', 'en_progreso']);
    }

    /**
     * Determina si la tarea es para hoy
     */
    public function getEsHoyAttribute(): bool
    {
        return $this->fecha_vencimiento->isToday();
    }

    /**
     * Determina si la tarea es para mañana
     */
    public function getEsMananaAttribute(): bool
    {
        return $this->fecha_vencimiento->isTomorrow();
    }

    /**
     * Obtiene el color CSS según la prioridad para el diseño Bioscom
     */
    public function getColorPrioridadAttribute(): string
    {
        return match($this->prioridad) {
            'urgente' => 'text-red-600 bg-red-50 border-red-200',
            'alta' => 'text-orange-600 bg-orange-50 border-orange-200',
            'media' => 'text-blue-600 bg-blue-50 border-blue-200',
            'baja' => 'text-gray-600 bg-gray-50 border-gray-200',
            default => 'text-gray-600 bg-gray-50 border-gray-200'
        };
    }

    /**
     * Obtiene el color CSS según el estado para el diseño Bioscom
     */
    public function getColorEstadoAttribute(): string
    {
        return match($this->estado) {
            'completada' => 'text-green-600 bg-green-50 border-green-200',
            'en_progreso' => 'text-blue-600 bg-blue-50 border-blue-200',
            'pendiente' => 'text-yellow-600 bg-yellow-50 border-yellow-200',
            'vencida' => 'text-red-600 bg-red-50 border-red-200',
            'pospuesta' => 'text-gray-600 bg-gray-50 border-gray-200',
            'cancelada' => 'text-red-600 bg-red-50 border-red-200',
            default => 'text-gray-600 bg-gray-50 border-gray-200'
        };
    }

    /**
     * Obtiene una descripción amigable del tipo de tarea
     */
    public function getTipoDescripcionAttribute(): string
    {
        return match($this->tipo) {
            'seguimiento' => 'Seguimiento',
            'cotizacion' => 'Cotización',
            'mantencion' => 'Mantención',
            'cobranza' => 'Cobranza',
            'reunion' => 'Reunión',
            'llamada' => 'Llamada',
            'email' => 'Email',
            'visita' => 'Visita',
            'administrativa' => 'Administrativa',
            'personal' => 'Personal',
            default => ucfirst($this->tipo)
        };
    }

    /**
     * Marcar tarea como completada
     */
    public function marcarComoCompletada($resultado = null): bool
    {
        $this->estado = 'completada';
        $this->fecha_completada = Carbon::now();
        if ($resultado) {
            $this->resultado = $resultado;
        }
        return $this->save();
    }

    /**
     * Posponer tarea a una nueva fecha
     */
    public function posponer($nuevaFecha, $motivo = null): bool
    {
        $this->fecha_vencimiento = Carbon::parse($nuevaFecha);
        $this->estado = 'pospuesta';
        
        // Agregar motivo a metadatos si se proporciona
        if ($motivo) {
            $metadatos = $this->metadatos ?? [];
            $metadatos['posposiciones'][] = [
                'fecha_anterior' => $this->getOriginal('fecha_vencimiento'),
                'fecha_nueva' => $nuevaFecha,
                'motivo' => $motivo,
                'fecha_postergacion' => Carbon::now()
            ];
            $this->metadatos = $metadatos;
        }
        
        return $this->save();
    }
}
