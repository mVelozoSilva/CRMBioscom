<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'telefono',
        'cargo',
        'activo',
        'ultimo_acceso'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
            'ultimo_acceso' => 'datetime'
        ];
    }

    // **MEJORAS DEL ROADMAP - SISTEMA DE ROLES FASE 3**

    // Roles predefinidos del sistema
    const ROL_ADMINISTRADOR_GENERAL = 'Administrador General';
    const ROL_GERENTE_GENERAL = 'Gerente General';
    const ROL_JEFE_VENTAS = 'Jefe de Ventas';
    const ROL_VENDEDOR = 'Vendedor';
    const ROL_ASISTENTE_VENTAS = 'Asistente de Ventas';
    const ROL_ENCARGADO_COBRANZA = 'Encargado de Cobranza';
    const ROL_ASISTENTE_COBRANZA = 'Asistente de Cobranza';
    const ROL_ENCARGADO_ST = 'Encargado de Servicio Técnico y Logística';
    const ROL_PERSONAL_ST = 'Personal Servicio Técnico y Logística';

    // **RELACIONES**

    // Relación con clientes asignados (many-to-many)
    public function clientes()
    {
        return $this->belongsToMany(Cliente::class, 'cliente_vendedor', 'vendedor_id', 'cliente_id');
    }

    // Relación con cotizaciones como vendedor
    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class, 'vendedor_id');
    }

    // Relación con seguimientos asignados
    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class, 'vendedor_id');
    }

    // Relación con tareas asignadas
    public function tareas()
    {
        return $this->hasMany(Tarea::class, 'usuario_id');
    }

    // Relación many-to-many: vendedores supervisados (para jefes)
    public function vendedoresSupervision()
    {
        // Solución temporal: devolver todos los vendedores si es jefe
        if ($this->esJefe()) {
            return User::whereHas('roles', function($query) {
                $query->whereIn('name', [self::ROL_VENDEDOR, self::ROL_ASISTENTE_VENTAS]);
            });
        }
    
    // Si no es jefe, devolver query builder vacío
    return User::whereRaw('1 = 0');
}

    // Relación many-to-many: jefes que supervisan (para vendedores)
    public function supervisores()
    {
        return $this->belongsToMany(User::class, 'supervision_vendedores', 'vendedor_id', 'jefe_id');
    }

    // **MÉTODOS DE ROLES Y PERMISOS**

    // Verificar si es administrador
    public function esAdministrador()
    {
        return $this->hasRole([self::ROL_ADMINISTRADOR_GENERAL, self::ROL_GERENTE_GENERAL]);
    }

    // Verificar si es jefe (puede supervisar vendedores)
    public function esJefe()
    {
        return $this->hasRole([
            self::ROL_ADMINISTRADOR_GENERAL,
            self::ROL_GERENTE_GENERAL,
            self::ROL_JEFE_VENTAS
        ]);
    }

    // Verificar si es vendedor
    public function esVendedor()
    {
        return $this->hasRole([self::ROL_VENDEDOR, self::ROL_ASISTENTE_VENTAS]);
    }

    // Verificar si trabaja en cobranzas
    public function esCobranza()
    {
        return $this->hasRole([self::ROL_ENCARGADO_COBRANZA, self::ROL_ASISTENTE_COBRANZA]);
    }

    // Verificar si trabaja en servicio técnico
    public function esServicioTecnico()
    {
        return $this->hasRole([self::ROL_ENCARGADO_ST, self::ROL_PERSONAL_ST]);
    }

    // **MÉTODOS PARA DASHBOARD PERSONALIZADO**

    // Obtener datos del dashboard según rol
    public function getDashboardData()
    {
        $data = [
            'usuario' => $this->name,
            'rol' => $this->getRoleNames()->first(),
            'ultimo_acceso' => $this->ultimo_acceso
        ];

        // Datos específicos por rol
        if ($this->esVendedor()) {
            $data['ventas'] = $this->getDatosVentas();
        }

        if ($this->esJefe()) {
            $data['equipo'] = $this->getDatosEquipo();
        }

        if ($this->esCobranza()) {
            $data['cobranzas'] = $this->getDatosCobranzas();
        }

        if ($this->esServicioTecnico()) {
            $data['servicio_tecnico'] = $this->getDatosServicioTecnico();
        }

        return $data;
    }

    // Datos de ventas para vendedores
    private function getDatosVentas()
    {
        return [
            'cotizaciones_mes' => $this->cotizaciones()->whereMonth('created_at', now()->month)->count(),
            'cotizaciones_ganadas_mes' => $this->cotizaciones()
                ->whereMonth('created_at', now()->month)
                ->where('estado', Cotizacion::ESTADO_GANADA)
                ->count(),
            'valor_vendido_mes' => $this->cotizaciones()
                ->whereMonth('created_at', now()->month)
                ->where('estado', Cotizacion::ESTADO_GANADA)
                ->sum('total_con_iva'),
            'seguimientos_pendientes' => $this->seguimientos()
                ->whereIn('estado', ['pendiente', 'en_proceso'])
                ->count(),
            'tareas_hoy' => $this->tareas()
                ->where('fecha_tarea', now()->toDateString())
                ->where('estado', 'pendiente')
                ->count()
        ];
    }

    // Datos de equipo para jefes
    private function getDatosEquipo()
    {
        $vendedoresIds = $this->vendedoresSupervision()->pluck('id')->toArray();
        
        return [
            'vendedores_activos' => count($vendedoresIds),
            'cotizaciones_equipo_mes' => Cotizacion::whereIn('vendedor_id', $vendedoresIds)
                ->whereMonth('created_at', now()->month)
                ->count(),
            'valor_equipo_mes' => Cotizacion::whereIn('vendedor_id', $vendedoresIds)
                ->whereMonth('created_at', now()->month)
                ->where('estado', Cotizacion::ESTADO_GANADA)
                ->sum('total_con_iva'),
            'seguimientos_atrasados_equipo' => Seguimiento::whereIn('vendedor_id', $vendedoresIds)
                ->atrasados()
                ->count()
        ];
    }

    // Datos de cobranzas
    private function getDatosCobranzas()
    {
        // Aquí irían las métricas específicas de cobranza
        return [
            'gestiones_pendientes' => 0, // TODO: Implementar cuando esté el módulo
            'facturas_vencidas' => 0,
            'valor_recuperado_mes' => 0
        ];
    }

    // Datos de servicio técnico
    private function getDatosServicioTecnico()
    {
        // Aquí irían las métricas específicas de ST
        return [
            'solicitudes_pendientes' => 0, // TODO: Implementar cuando esté el módulo
            'mantenciones_programadas' => 0,
            'equipos_en_servicio' => 0
        ];
    }

    // **MÉTODOS PARA AGENDA Y TAREAS**

    // Obtener carga de trabajo del día
    public function getCargaTrabajoHoy()
    {
        $tareas = $this->tareas()
            ->where('fecha_tarea', now()->toDateString())
            ->where('estado', '!=', 'completada')
            ->get();

        $duracion_total = $tareas->sum('duracion_estimada') ?? 0;

        return [
            'tareas_total' => $tareas->count(),
            'tareas_urgentes' => $tareas->where('prioridad', 'urgente')->count(),
            'duracion_estimada_minutos' => $duracion_total,
            'duracion_estimada_horas' => round($duracion_total / 60, 1),
            'sobrecargado' => $duracion_total > 480 // Más de 8 horas
        ];
    }

    // Obtener próximas tareas (para vista "Mi Día")
    public function getProximasTareas($limite = 10)
    {
        return $this->tareas()
            ->where('fecha_tarea', '>=', now()->toDateString())
            ->where('estado', '!=', 'completada')
            ->orderBy('fecha_tarea', 'asc')
            ->orderBy('prioridad', 'desc')
            ->limit($limite)
            ->with(['cliente', 'cotizacion', 'seguimiento'])
            ->get();
    }

    // **DISTRIBUCIÓN AUTOMÁTICA - ROADMAP FASE 4**

    // Configurar distribución automática para el equipo (solo jefes)
    public function configurarDistribucionAutomatica($configuracion)
    {
        if (!$this->esJefe()) {
            throw new \Exception('Solo los jefes pueden configurar distribución automática');
        }

        $vendedoresIds = $this->vendedoresSupervision()->pluck('users.id')->toArray();

        return Seguimiento::distribuirVencidosAutomaticamente($vendedoresIds, $configuracion);
    }

    // **MÉTRICAS Y ESTADÍSTICAS**

    // Rendimiento del vendedor
    public function getRendimientoMes()
    {
        if (!$this->esVendedor()) {
            return null;
        }

        $cotizaciones = $this->cotizaciones()->whereMonth('created_at', now()->month);
        $total = $cotizaciones->count();
        $ganadas = (clone $cotizaciones)->where('estado', Cotizacion::ESTADO_GANADA)->count();

        return [
            'cotizaciones_total' => $total,
            'cotizaciones_ganadas' => $ganadas,
            'tasa_conversion' => $total > 0 ? round(($ganadas / $total) * 100, 1) : 0,
            'valor_vendido' => (clone $cotizaciones)->where('estado', Cotizacion::ESTADO_GANADA)->sum('total_con_iva')
        ];
    }

    // **SCOPES PARA FILTRADO**

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeVendedores($query)
    {
        return $query->whereHas('roles', function($q) {
            $q->whereIn('name', [self::ROL_VENDEDOR, self::ROL_ASISTENTE_VENTAS]);
        });
    }

    public function scopeJefes($query)
    {
        return $query->whereHas('roles', function($q) {
            $q->whereIn('name', [self::ROL_JEFE_VENTAS, self::ROL_GERENTE_GENERAL, self::ROL_ADMINISTRADOR_GENERAL]);
        });
    }

    // **MÉTODOS AUXILIARES**

    // Obtener iniciales para avatar
    public function getInicialesAttribute()
    {
        $nombres = explode(' ', $this->name);
        $iniciales = '';
        
        foreach (array_slice($nombres, 0, 2) as $nombre) {
            $iniciales .= strtoupper(substr($nombre, 0, 1));
        }
        
        return $iniciales;
    }

    // Formatear último acceso
    public function getUltimoAccesoFormateadoAttribute()
    {
        if (!$this->ultimo_acceso) {
            return 'Nunca';
        }

        return $this->ultimo_acceso->diffForHumans();
    }

    // **EVENTOS DEL MODELO**

    protected static function boot()
    {
        parent::boot();

        // Actualizar último acceso al hacer login
        static::updating(function ($user) {
            if ($user->isDirty('remember_token')) {
                $user->ultimo_acceso = now();
            }
        });
    }

    // **MÉTODOS PARA API**

    public function toSearchArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'cargo' => $this->cargo,
            'rol' => $this->getRoleNames()->first(),
            'iniciales' => $this->iniciales
        ];
    }

    // **PERSONALIZACIÓN SEGÚN ROADMAP**

    // Obtener menú personalizado según rol
    public function getMenuPersonalizado()
    {
        $menu = [];

        // Menú base para todos
        $menu[] = ['titulo' => 'Dashboard', 'ruta' => 'dashboard', 'icono' => 'fas fa-tachometer-alt'];

        // Menú para vendedores
        if ($this->esVendedor() || $this->esJefe()) {
            $menu[] = ['titulo' => 'Clientes', 'ruta' => 'clientes.index', 'icono' => 'fas fa-users'];
            $menu[] = ['titulo' => 'Cotizaciones', 'ruta' => 'cotizaciones.index', 'icono' => 'fas fa-file-invoice'];
            $menu[] = ['titulo' => 'Seguimiento', 'ruta' => 'seguimiento.index', 'icono' => 'fas fa-tasks'];
            $menu[] = ['titulo' => 'Mi Agenda', 'ruta' => 'agenda.mi-dia', 'icono' => 'fas fa-calendar-alt'];
        }

        // Menú para jefes adicional
        if ($this->esJefe()) {
            $menu[] = ['titulo' => 'Triaje', 'ruta' => 'triaje.index', 'icono' => 'fas fa-filter'];
        }

        // Menú para cobranzas
        if ($this->esCobranza()) {
            $menu[] = ['titulo' => 'Cobranzas', 'ruta' => 'cobranzas.index', 'icono' => 'fas fa-money-check-alt'];
        }

        // Menú para servicio técnico
        if ($this->esServicioTecnico()) {
            $menu[] = ['titulo' => 'Servicio Técnico', 'ruta' => 'servicio-tecnico.index', 'icono' => 'fas fa-tools'];
            $menu[] = ['titulo' => 'Inventario', 'ruta' => 'inventario.index', 'icono' => 'fas fa-boxes'];
        }

        // Menú para administradores
        if ($this->esAdministrador()) {
            $menu[] = ['titulo' => 'Productos', 'ruta' => 'productos.index', 'icono' => 'fas fa-box'];
            $menu[] = ['titulo' => 'Usuarios', 'ruta' => 'usuarios.index', 'icono' => 'fas fa-user-cog'];
            $menu[] = ['titulo' => 'Reportes', 'ruta' => 'reportes.index', 'icono' => 'fas fa-chart-bar'];
        }

        return $menu;
    }
}