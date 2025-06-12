<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre_institucion',
        'rut',
        'tipo_cliente',
        'vendedores_a_cargo',
        'informacion_adicional',
        'email',
        'telefono',
        'direccion',
        'nombre_contacto'
    ];

    protected $casts = [
        'vendedores_a_cargo' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Validaciones personalizadas
    protected $rules = [
        'nombre_institucion' => 'required|string|max:255',
        'rut' => 'nullable|string|max:12|unique:clientes,rut',
        'tipo_cliente' => 'nullable|in:Cliente Público,Cliente Privado,Revendedor',
        'email' => 'required|email|max:255',
        'telefono' => 'nullable|string|max:20',
        'direccion' => 'nullable|string|max:255',
        'nombre_contacto' => 'nullable|string|max:255'
    ];

    // **MEJORAS DEL ROADMAP - FASE 3**

    // Relación con contactos (múltiples contactos por cliente)
    public function contactos()
    {
        return $this->hasMany(Contacto::class);
    }

    // Relación con cotizaciones
    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class);
    }

    // Relación con seguimientos
    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class);
    }

    // Relación many-to-many con vendedores (usuarios)
    public function vendedores()
    {
        return $this->belongsToMany(User::class, 'cliente_vendedor', 'cliente_id', 'vendedor_id');
    }

    // **MÉTODOS DE BÚSQUEDA Y FILTRADO**

    // Scope para búsqueda rápida por nombre o RUT
    public function scopeBusqueda($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('nombre_institucion', 'LIKE', "%{$termino}%")
              ->orWhere('rut', 'LIKE', "%{$termino}%")
              ->orWhere('nombre_contacto', 'LIKE', "%{$termino}%");
        });
    }

    // Scope para filtrar por tipo de cliente
    public function scopeTipoCliente($query, $tipo)
    {
        return $query->where('tipo_cliente', $tipo);
    }

    // Scope para clientes con vendedor específico
    public function scopeVendedor($query, $vendedorId)
    {
        return $query->whereJsonContains('vendedores_a_cargo', $vendedorId);
    }

    // **MÉTODOS AUXILIARES**

    // Obtener el contacto principal
    public function getContactoPrincipalAttribute()
    {
        return $this->contactos()->first() ?? 
               (object)[
                   'nombre' => $this->nombre_contacto,
                   'email' => $this->email,
                   'telefono' => $this->telefono
               ];
    }

    // Formatear RUT con puntos y guión
    public function getRutFormateadoAttribute()
    {
        if (!$this->rut) return '';
        
        $rut = preg_replace('/[^0-9kK]/', '', $this->rut);
        $cuerpo = substr($rut, 0, -1);
        $dv = substr($rut, -1);
        
        return number_format($cuerpo, 0, '', '.') . '-' . $dv;
    }

    // Verificar si tiene seguimientos pendientes
    public function getSeguimientosPendientesAttribute()
    {
        return $this->seguimientos()
                   ->whereIn('estado', ['pendiente', 'en_proceso'])
                   ->count();
    }

    // Obtener última cotización
    public function getUltimaCotizacionAttribute()
    {
        return $this->cotizaciones()
                   ->latest('created_at')
                   ->first();
    }

    // **MÉTODOS ESTÁTICOS PARA DASHBOARD**

    // Contar clientes por tipo
    public static function contarPorTipo()
    {
        return self::selectRaw('tipo_cliente, COUNT(*) as total')
                   ->groupBy('tipo_cliente')
                   ->pluck('total', 'tipo_cliente')
                   ->toArray();
    }

    // Clientes con más cotizaciones
    public static function conMasCotizaciones($limite = 5)
    {
        return self::withCount('cotizaciones')
                   ->orderBy('cotizaciones_count', 'desc')
                   ->limit($limite)
                   ->get();
    }

    // **VALIDACIÓN PERSONALIZADA**

    // Validar que el RUT sea único al crear o actualizar
    public function validarRutUnico($rut, $clienteId = null)
    {
        $query = self::where('rut', $rut);
        
        if ($clienteId) {
            $query->where('id', '!=', $clienteId);
        }
        
        return $query->doesntExist();
    }

    // **MÉTODOS PARA API Y BÚSQUEDAS AJAX**

    // Transformar para API (solo campos necesarios)
    public function toSearchArray()
    {
        return [
            'id' => $this->id,
            'nombre_institucion' => $this->nombre_institucion,
            'rut' => $this->rut_formateado,
            'tipo_cliente' => $this->tipo_cliente,
            'nombre_contacto' => $this->nombre_contacto,
            'email' => $this->email,
            'telefono' => $this->telefono
        ];
    }

    // **PERSONALIZACIÓN PARA ROLES DEL ROADMAP**

    // Scope para vendedores que solo ven sus clientes
    public function scopeParaVendedor($query, $vendedorId)
    {
        return $query->whereJsonContains('vendedores_a_cargo', $vendedorId);
    }

    // Scope para jefes que ven todo el equipo
    public function scopeParaJefeVentas($query, $equipoIds = [])
    {
        if (empty($equipoIds)) {
            return $query;
        }
        
        return $query->where(function($q) use ($equipoIds) {
            foreach ($equipoIds as $vendedorId) {
                $q->orWhereJsonContains('vendedores_a_cargo', $vendedorId);
            }
        });
    }
}