<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Contacto - Sistema de Gestión de Contactos por Cliente
 * 
 * Este modelo gestiona los contactos asociados a cada cliente,
 * permitiendo múltiples contactos por cliente según el roadmap.
 * 
 * FASE: 1-2 (Fundación y Crisis Operativa)
 * PRIORIDAD: 🔵 Importante para completar la gestión de clientes
 */
class Contacto extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'contactos';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'cliente_id',
        'nombre',
        'cargo',
        'email',
        'telefono',
        'area',
        'notas'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'cliente_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Normalizar datos antes de guardar
        static::saving(function ($contacto) {
            // Normalizar email
            if ($contacto->email) {
                $contacto->email = strtolower(trim($contacto->email));
            }

            // Capitalizar nombre apropiadamente
            if ($contacto->nombre) {
                $contacto->nombre = ucwords(strtolower(trim($contacto->nombre)));
            }

            // Capitalizar cargo
            if ($contacto->cargo) {
                $contacto->cargo = ucwords(strtolower(trim($contacto->cargo)));
            }

            // Capitalizar área
            if ($contacto->area) {
                $contacto->area = ucwords(strtolower(trim($contacto->area)));
            }

            // Formatear teléfono básico (remover caracteres no numéricos excepto + - ( ) espacios)
            if ($contacto->telefono) {
                $contacto->telefono = preg_replace('/[^0-9\s\-\(\)\+]/', '', trim($contacto->telefono));
            }
        });
    }

    // ===== RELACIONES =====

    /**
     * Relación: Cliente al que pertenece este contacto
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    // ===== SCOPES =====

    /**
     * Scope: Buscar contactos por término general
     */
    public function scopeBuscar($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('nombre', 'like', "%{$term}%")
              ->orWhere('cargo', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('area', 'like', "%{$term}%")
              ->orWhere('telefono', 'like', "%{$term}%");
        });
    }

    /**
     * Scope: Filtrar por área específica
     */
    public function scopePorArea($query, $area)
    {
        return $query->where('area', $area);
    }

    /**
     * Scope: Contactos que tienen email
     */
    public function scopeConEmail($query)
    {
        return $query->whereNotNull('email')->where('email', '!=', '');
    }

    /**
     * Scope: Contactos que tienen teléfono
     */
    public function scopeConTelefono($query)
    {
        return $query->whereNotNull('telefono')->where('telefono', '!=', '');
    }

    /**
     * Scope: Contactos de un cliente específico
     */
    public function scopePorCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    /**
     * Scope: Ordenar por relevancia (contactos principales del cliente primero)
     */
    public function scopeOrdenadoPorRelevancia($query)
    {
        return $query->leftJoin('clientes', 'contactos.cliente_id', '=', 'clientes.id')
                    ->orderByRaw('CASE WHEN clientes.nombre_contacto = contactos.nombre THEN 0 ELSE 1 END')
                    ->orderBy('contactos.nombre')
                    ->select('contactos.*');
    }

    // ===== ACCESSORS =====

    /**
     * Accessor: Nombre completo con cargo
     */
    public function getNombreCompletoAttribute()
    {
        $nombre = $this->nombre;
        if ($this->cargo) {
            $nombre .= ' (' . $this->cargo . ')';
        }
        return $nombre;
    }

    /**
     * Accessor: Información de contacto resumida
     */
    public function getInfoContactoAttribute()
    {
        $info = [];
        
        if ($this->email) {
            $info[] = $this->email;
        }
        
        if ($this->telefono) {
            $info[] = $this->telefono;
        }
        
        return implode(' • ', $info);
    }

    /**
     * Accessor: Verificar si el contacto tiene información completa
     */
    public function getEsContactoCompletoAttribute()
    {
        return !empty($this->email) && !empty($this->telefono);
    }

    /**
     * Accessor: Obtener iniciales del nombre para avatares
     */
    public function getInicialesAttribute()
    {
        $palabras = explode(' ', $this->nombre);
        $iniciales = '';
        
        foreach ($palabras as $palabra) {
            if (!empty($palabra)) {
                $iniciales .= strtoupper(substr($palabra, 0, 1));
            }
        }
        
        return substr($iniciales, 0, 2); // Máximo 2 iniciales
    }

    /**
     * Accessor: Teléfono formateado para Chile
     */
    public function getTelefonoFormateadoAttribute()
    {
        if (empty($this->telefono)) {
            return '';
        }

        // Formato básico para números chilenos
        $telefono = preg_replace('/[^0-9]/', '', $this->telefono);
        
        // Si es un móvil chileno típico (9 dígitos empezando en 9)
        if (strlen($telefono) === 9 && substr($telefono, 0, 1) === '9') {
            return '+56 ' . substr($telefono, 0, 1) . ' ' . 
                   substr($telefono, 1, 4) . ' ' . 
                   substr($telefono, 5, 4);
        }
        
        return $this->telefono; // Retornar original si no coincide con formato estándar
    }

    // ===== MÉTODOS ESTÁTICOS =====

    /**
     * Obtener todas las áreas únicas disponibles
     */
    public static function areasUnicas()
    {
        return static::whereNotNull('area')
                    ->where('area', '!=', '')
                    ->distinct()
                    ->pluck('area')
                    ->sort()
                    ->values();
    }

    /**
     * Obtener contactos agrupados por área
     */
    public static function agrupadosPorArea()
    {
        return static::whereNotNull('area')
                    ->where('area', '!=', '')
                    ->with('cliente')
                    ->get()
                    ->groupBy('area')
                    ->map(function($contactos) {
                        return $contactos->sortBy('nombre');
                    })
                    ->sortKeys();
    }

    /**
     * Buscar contactos similares (para evitar duplicados)
     */
    public static function buscarSimilares($email = null, $nombre = null, $excludeId = null)
    {
        $query = static::query();
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $query->where(function($q) use ($email, $nombre) {
            if ($email) {
                $q->where('email', $email);
            }
            
            if ($nombre) {
                $q->orWhere('nombre', 'like', "%{$nombre}%");
            }
        });
        
        return $query->with('cliente')->get();
    }

    // ===== MÉTODOS DE INSTANCIA =====

    /**
     * Verificar si es el contacto principal del cliente
     */
    public function esContactoPrincipal()
    {
        if (!$this->cliente) {
            return false;
        }
        
        return $this->cliente->nombre_contacto === $this->nombre;
    }

    /**
     * Obtener estadísticas del contacto
     */
    public function getEstadisticas()
    {
        return [
            'cliente' => $this->cliente->nombre_institucion ?? 'Sin cliente',
            'tiene_email' => !empty($this->email),
            'tiene_telefono' => !empty($this->telefono),
            'tiene_cargo' => !empty($this->cargo),
            'tiene_area' => !empty($this->area),
            'completitud' => $this->calcularCompletitud(),
            'es_principal' => $this->esContactoPrincipal()
        ];
    }

    /**
     * Calcular porcentaje de completitud de información
     */
    public function calcularCompletitud()
    {
        $campos = ['nombre', 'email', 'telefono', 'cargo', 'area'];
        $camposCompletos = 0;
        
        foreach ($campos as $campo) {
            if (!empty($this->{$campo})) {
                $camposCompletos++;
            }
        }
        
        return round(($camposCompletos / count($campos)) * 100);
    }

    /**
     * Convertir a array para APIs
     */
    public function toApiArray()
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'nombre_completo' => $this->nombre_completo,
            'cargo' => $this->cargo,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'telefono_formateado' => $this->telefono_formateado,
            'area' => $this->area,
            'iniciales' => $this->iniciales,
            'info_contacto' => $this->info_contacto,
            'es_contacto_completo' => $this->es_contacto_completo,
            'es_principal' => $this->esContactoPrincipal(),
            'completitud' => $this->calcularCompletitud(),
            'cliente' => $this->cliente ? [
                'id' => $this->cliente->id,
                'nombre_institucion' => $this->cliente->nombre_institucion,
                'rut' => $this->cliente->rut
            ] : null,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s')
        ];
    }
}