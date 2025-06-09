<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Seguimiento extends Model
{
    use HasFactory;

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
    ];

    // Relaciones
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    // Scopes para filtros
    public function scopeAtrasados($query)
    {
        return $query->where('proxima_gestion', '<', Carbon::today())
                    ->whereNotIn('estado', ['completado']);
    }

    public function scopeProximos($query, $dias = 7)
    {
        return $query->whereBetween('proxima_gestion', [
            Carbon::today(),
            Carbon::today()->addDays($dias)
        ])->whereNotIn('estado', ['completado']);
    }

    public function scopePorVendedor($query, $vendedorId)
    {
        return $query->where('vendedor_id', $vendedorId);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorPrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    public function scopeBuscarCliente($query, $termino)
    {
        return $query->whereHas('cliente', function ($q) use ($termino) {
            $q->where('nombre_institucion', 'LIKE', "%{$termino}%")
              ->orWhere('rut', 'LIKE', "%{$termino}%");
        });
    }

    // Atributos calculados
    public function getEstadoColorAttribute()
    {
        $colores = [
            'pendiente' => 'table-warning',
            'en_proceso' => 'table-info',
            'completado' => 'table-success',
            'vencido' => 'table-danger',
            'reprogramado' => 'table-secondary'
        ];

        // Si está atrasado, siempre mostrar como peligro
        if ($this->proxima_gestion < Carbon::today() && $this->estado !== 'completado') {
            return 'table-danger';
        }

        return $colores[$this->estado] ?? '';
    }

    public function getPrioridadColorAttribute()
    {
        $colores = [
            'baja' => 'text-secondary',
            'media' => 'text-primary',
            'alta' => 'text-warning',
            'urgente' => 'text-danger'
        ];

        return $colores[$this->prioridad] ?? '';
    }

    public function getDiasRestantesAttribute()
    {
        // Protección para proxima_gestion nula
        if (!$this->proxima_gestion) {
            return 'Sin fecha';
        }

        $dias = $this->proxima_gestion->diffInDays(Carbon::today(), false);

        if ($dias < 0) {
            return abs($dias) . ' día(s) atrasado'; // Más claro que 'restantes'
        } elseif ($dias == 0) {
            return 'Hoy';
        } else {
            return $dias . ' día(s) restante(s)'; // Más claro
        }
    }
}
