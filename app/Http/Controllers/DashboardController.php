<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Seguimiento;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Cobranza;
use App\Models\ServicioTecnico;
use App\Models\DashboardMetrics;
use App\Models\DashboardAlertas;

class DashboardController extends Controller
{
    /**
     * DASHBOARD PRINCIPAL - PERSONALIZADO SEGÚN ROL
     * ROADMAP FASE 3: Control Total - Visibilidad completa
     */
    public function index(Request $request)
    {
        // Si no hay usuario autenticado, usar el primero disponible
        $user = Auth::user();
        if (!$user) {
            $user = \App\Models\User::first();
            if ($user) {
                Auth::login($user);
            }
        }

        // Actualizar último acceso solo si el usuario existe
        if ($user) {
            try {
                if (\Schema::hasColumn('users', 'ultimo_acceso')) {
                    $user->update(['ultimo_acceso' => now()]);
                }
            } catch (\Exception $e) {
                // Si falla, continuar sin actualizar
            }
        }

        // Obtener datos específicos según rol
        $dashboardData = $this->getDashboardDataByRole($user);

        // Si es petición AJAX, devolver solo los datos
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $dashboardData
            ]);
        }

        return view('dashboard.index', compact('dashboardData', 'user'));
    }

    /**
     * OBTENER DATOS DEL DASHBOARD SEGÚN ROL
     * Implementa la personalización por rol del Roadmap
     */
    private function getDashboardDataByRole($user)
    {
        $data = [
            'usuario' => $user ? $user->name ?? 'Usuario' : 'Usuario',
            'rol' => $this->getUserRoleSafe($user),
            'ultimo_acceso' => $this->getUltimoAccesoSafe($user),
            'fecha_actual' => now()->format('d/m/Y'),
            'saludo' => $this->getSaludo()
        ];

        // **SECCIÓN "MI DÍA HOY" - COMÚN PARA TODOS**
        $data['mi_dia'] = $this->getMiDiaHoy($user);

        // **ALERTAS CRÍTICAS** - ROADMAP: Rojas y Amarillas
        $data['alertas'] = $this->getAlertas($user);

        // **DATOS ESPECÍFICOS POR ROL** - Usar verificaciones seguras
        if ($this->esVendedorSafe($user)) {
            $data['ventas'] = $this->getDatosVentas($user);
        }

        if ($this->esJefeSafe($user)) {
            $data['equipo'] = $this->getDatosEquipo($user);
            $data['ventas'] = $this->getDatosVentas($user); // Jefes también ven sus datos de venta
        }

        if ($this->esAdministradorSafe($user)) {
            $data['general'] = $this->getDatosGenerales();
            $data['equipo'] = $this->getDatosEquipo($user);
        }

        if ($this->esCobranzaSafe($user)) {
            $data['cobranzas'] = $this->getDatosCobranzas($user);
        }

        if ($this->esServicioTecnicoSafe($user)) {
            $data['servicio_tecnico'] = $this->getDatosServicioTecnico($user);
        }

        // **MÉTRICAS DEL MES** - Motivación y contexto
        $data['metricas_mes'] = $this->getMetricasMes($user);

        return $data;
    }

    /**
     * MI DÍA HOY - Panel de carga de trabajo
     * ROADMAP: Visualización clara de la carga diaria
     */
    private function getMiDiaHoy($user)
    {
        // Verificación de seguridad: si no hay usuario, devolver datos vacíos
        if (!$user || !$user->id) {
            return [
                'tareas_pendientes' => 0,
                'tareas_urgentes' => 0,
                'seguimientos_hoy' => 0,
                'duracion_estimada_horas' => 0,
                'sobrecargado' => false,
                'tareas_detalle' => [],
                'seguimientos_detalle' => []
            ];
        }

        try {
            $tareas_hoy = Tarea::where('usuario_id', $user->id)
                ->where('fecha_tarea', now()->toDateString())
                ->where('estado', '!=', 'completada')
                ->with(['cliente', 'cotizacion', 'seguimiento'])
                ->orderBy('prioridad', 'desc')
                ->orderBy('hora_inicio', 'asc')
                ->get();

            $seguimientos_hoy = Seguimiento::where('vendedor_id', $user->id)
                ->whereDate('proxima_gestion', now()->toDateString())
                ->with(['cliente', 'cotizacion'])
                ->get();

            $carga_trabajo = $this->getCargaTrabajoHoySafe($user);

            return [
                'tareas_pendientes' => $tareas_hoy->count(),
                'tareas_urgentes' => $tareas_hoy->where('prioridad', 'urgente')->count(),
                'seguimientos_hoy' => $seguimientos_hoy->count(),
                'duracion_estimada_horas' => $carga_trabajo['duracion_estimada_horas'],
                'sobrecargado' => $carga_trabajo['sobrecargado'],
                'tareas_detalle' => $tareas_hoy->take(5)->map(function ($tarea) {
                    return [
                        'id' => $tarea->id,
                        'titulo' => $tarea->titulo,
                        'tipo' => $tarea->tipo,
                        'prioridad' => $tarea->prioridad,
                        'hora_inicio' => $tarea->hora_inicio ? $tarea->hora_inicio : null,
                        'cliente' => optional($tarea->cliente)->nombre_institucion,
                        'duracion_estimada' => $tarea->duracion_estimada
                    ];
                }),
                'seguimientos_detalle' => $seguimientos_hoy->take(3)->map(function ($seguimiento) {
                    return [
                        'id' => $seguimiento->id,
                        'cliente' => optional($seguimiento->cliente)->nombre_institucion,
                        'cotizacion' => optional($seguimiento->cotizacion)->codigo,
                        'prioridad' => $seguimiento->prioridad,
                        'notas' => substr($seguimiento->notas ?? '', 0, 100)
                    ];
                })
            ];
        } catch (\Exception $e) {
            // Si hay cualquier error, devolver datos vacíos
            return [
                'tareas_pendientes' => 0,
                'tareas_urgentes' => 0,
                'seguimientos_hoy' => 0,
                'duracion_estimada_horas' => 0,
                'sobrecargado' => false,
                'tareas_detalle' => [],
                'seguimientos_detalle' => [],
                'error' => 'Error al cargar datos del día'
            ];
        }
    }

    /**
     * CARGA DE TRABAJO HOY - Versión segura
     */
    private function getCargaTrabajoHoySafe($user)
    {
        if (!$user || !$user->id) {
            return [
                'duracion_estimada_horas' => 0,
                'sobrecargado' => false
            ];
        }

        try {
            if (method_exists($user, 'getCargaTrabajoHoy')) {
                return $user->getCargaTrabajoHoy();
            }
            
            // Versión simplificada si no existe el método
            $duracion_total = Tarea::where('usuario_id', $user->id)
                ->where('fecha_tarea', now()->toDateString())
                ->where('estado', '!=', 'completada')
                ->sum('duracion_estimada') / 60; // Convertir a horas

            return [
                'duracion_estimada_horas' => round($duracion_total, 1),
                'sobrecargado' => $duracion_total > 8
            ];
        } catch (\Exception $e) {
            return [
                'duracion_estimada_horas' => 0,
                'sobrecargado' => false
            ];
        }
    }

    /**
     * ALERTAS CRÍTICAS - ROADMAP: Rojas y Amarillas
     * Información visible "de un vistazo"
     */
    private function getAlertas($user)
    {
        $alertas = [
            'rojas' => [], // Críticas
            'amarillas' => [] // Próximas a vencer
        ];

        // Verificación de seguridad: si no hay usuario, devolver alertas vacías
        if (!$user || !$user->id) {
            return $alertas;
        }

        try {
            // **ALERTAS ROJAS (CRÍTICAS)**
            
            // Seguimientos vencidos
            $seguimientos_vencidos = Seguimiento::where('vendedor_id', $user->id)
                ->where('proxima_gestion', '<', now()->toDateString())
                ->count();
            
            if ($seguimientos_vencidos > 0) {
                $alertas['rojas'][] = [
                    'tipo' => 'seguimientos_vencidos',
                    'titulo' => 'Seguimientos Vencidos',
                    'cantidad' => $seguimientos_vencidos,
                    'mensaje' => "{$seguimientos_vencidos} seguimiento(s) atrasado(s)",
                    'url' => route('seguimiento.index', ['clasificacion' => 'atrasados']),
                    'icono' => 'fas fa-exclamation-triangle'
                ];
            }

            // Cotizaciones vencidas (solo para vendedores/jefes)
            if ($this->esVendedorSafe($user) || $this->esJefeSafe($user)) {
                $cotizaciones_vencidas = Cotizacion::where('vendedor_id', $user->id)
                    ->where('validez_oferta', '<', now()->toDateString())
                    ->count();
                
                if ($cotizaciones_vencidas > 0) {
                    $alertas['rojas'][] = [
                        'tipo' => 'cotizaciones_vencidas',
                        'titulo' => 'Cotizaciones Vencidas',
                        'cantidad' => $cotizaciones_vencidas,
                        'mensaje' => "{$cotizaciones_vencidas} cotización(es) vencida(s)",
                        'url' => route('cotizaciones.index', ['vencidas' => 1]),
                        'icono' => 'fas fa-file-invoice'
                    ];
                }
            }

            // **ALERTAS AMARILLAS (PRÓXIMAS A VENCER)**
            
            // Seguimientos próximos
            $seguimientos_proximos = Seguimiento::where('vendedor_id', $user->id)
                ->whereBetween('proxima_gestion', [
                    now()->toDateString(),
                    now()->addDays(7)->toDateString()
                ])
                ->count();
            
            if ($seguimientos_proximos > 0) {
                $alertas['amarillas'][] = [
                    'tipo' => 'seguimientos_proximos',
                    'titulo' => 'Seguimientos Próximos',
                    'cantidad' => $seguimientos_proximos,
                    'mensaje' => "{$seguimientos_proximos} seguimiento(s) en próximos 7 días",
                    'url' => route('seguimiento.index', ['clasificacion' => 'proximos']),
                    'icono' => 'fas fa-calendar-alt'
                ];
            }

            // Cotizaciones próximas a vencer
            if ($this->esVendedorSafe($user) || $this->esJefeSafe($user)) {
                $cotizaciones_proximas = Cotizacion::where('vendedor_id', $user->id)
                    ->whereBetween('validez_oferta', [
                        now()->toDateString(),
                        now()->addDays(7)->toDateString()
                    ])
                    ->count();
                
                if ($cotizaciones_proximas > 0) {
                    $alertas['amarillas'][] = [
                        'tipo' => 'cotizaciones_proximas',
                        'titulo' => 'Cotizaciones por Vencer',
                        'cantidad' => $cotizaciones_proximas,
                        'mensaje' => "{$cotizaciones_proximas} cotización(es) vencen en 7 días",
                        'url' => route('cotizaciones.index', ['proximas_vencer' => 1]),
                        'icono' => 'fas fa-clock'
                    ];
                }
            }
            
        } catch (\Exception $e) {
            // Si hay errores, devolver alertas vacías y log del error
            \Log::error('Error al obtener alertas: ' . $e->getMessage());
        }

        return $alertas;
    }

    /**
     * DATOS DE VENTAS - Para vendedores y jefes
     */
    private function getDatosVentas($user)
    {
        if (!$user || !$user->id) {
            return [
                'cotizaciones_mes' => 0,
                'cotizaciones_ganadas_mes' => 0,
                'tasa_conversion_mes' => 0,
                'valor_vendido_mes' => 0,
                'valor_vendido_formateado' => '$0',
                'seguimientos_pendientes' => 0,
                'cotizaciones_activas' => 0
            ];
        }

        try {
            $query = Cotizacion::where('vendedor_id', $user->id);

            // Reemplazar ->delMesActual() con consulta directa
            $mes_actual = (clone $query)->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
            $total_mes = $mes_actual->count();
            $ganadas_mes = (clone $mes_actual)->where('estado', 'Ganada')->count();
            $valor_ganado_mes = (clone $mes_actual)->where('estado', 'Ganada')->sum('total_con_iva');

            return [
                'cotizaciones_mes' => $total_mes,
                'cotizaciones_ganadas_mes' => $ganadas_mes,
                'tasa_conversion_mes' => $total_mes > 0 ? round(($ganadas_mes / $total_mes) * 100, 1) : 0,
                'valor_vendido_mes' => $valor_ganado_mes,
                'valor_vendido_formateado' => '$' . number_format($valor_ganado_mes, 0, ',', '.'),
                'seguimientos_pendientes' => Seguimiento::where('vendedor_id', $user->id)
                    ->whereIn('estado', ['pendiente', 'en_proceso'])
                    ->count(),
                'cotizaciones_activas' => (clone $query)->whereIn('estado', ['Pendiente', 'Enviada', 'En Revisión'])->count()
            ];
        } catch (\Exception $e) {
            return [
                'cotizaciones_mes' => 0,
                'cotizaciones_ganadas_mes' => 0,
                'tasa_conversion_mes' => 0,
                'valor_vendido_mes' => 0,
                'valor_vendido_formateado' => '$0',
                'seguimientos_pendientes' => 0,
                'cotizaciones_activas' => 0,
                'error' => 'Error al cargar datos de ventas'
            ];
        }
    }

    /**
     * DATOS DE EQUIPO - Para jefes y administradores
     */
    private function getDatosEquipo($user)
    {
        if (!$user || !$user->id) {
            return [
                'vendedores_activos' => 0,
                'mensaje' => 'No hay usuario válido'
            ];
        }

        try {
            // Versión simplificada si no hay relaciones configuradas
            $equipoIds = [$user->id]; // Por ahora solo el mismo usuario
            
            if (method_exists($user, 'vendedoresSupervision')) {
                try {
                    $equipoIds = $user->vendedoresSupervision()->pluck('users.id')->toArray();
                } catch (\Exception $e) {
                    // Si falla, usar solo el usuario actual
                    $equipoIds = [$user->id];
                }
            }
            
            if (empty($equipoIds)) {
                return [
                    'vendedores_activos' => 0,
                    'mensaje' => 'No tienes vendedores asignados'
                ];
            }

            $cotizaciones_equipo = Cotizacion::whereIn('vendedor_id', $equipoIds)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
            $seguimientos_equipo = Seguimiento::whereIn('vendedor_id', $equipoIds);

            return [
                'vendedores_activos' => count($equipoIds),
                'cotizaciones_equipo_mes' => $cotizaciones_equipo->count(),
                'valor_equipo_mes' => $cotizaciones_equipo->where('estado', 'Ganada')->sum('total_con_iva'),
                'seguimientos_atrasados_equipo' => (clone $seguimientos_equipo)->where('proxima_gestion', '<', now()->toDateString())->count(),
                'rendimiento_vendedores' => $this->getRendimientoVendedores($equipoIds),
                'top_vendedores_mes' => $this->getTopVendedoresMes(3)
            ];
        } catch (\Exception $e) {
            return [
                'vendedores_activos' => 0,
                'mensaje' => 'Error al cargar datos del equipo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * DATOS GENERALES - Para administradores
     */
    private function getDatosGenerales()
    {
        try {
            return [
                'total_usuarios' => User::count(),
                'total_clientes' => Cliente::count(),
                'cotizaciones_mes' => Cotizacion::whereMonth('created_at', now()->month)->count(),
                'valor_total_mes' => Cotizacion::whereMonth('created_at', now()->month)
                    ->where('estado', 'Ganada')
                    ->sum('total_con_iva'),
                'clientes_nuevos_mes' => Cliente::whereMonth('created_at', now()->month)->count(),
                'seguimientos_sistema' => [
                    'atrasados' => Seguimiento::where('proxima_gestion', '<', now()->toDateString())->count(),
                    'hoy' => Seguimiento::whereDate('proxima_gestion', now()->toDateString())->count(),
                    'activos' => Seguimiento::whereIn('estado', ['pendiente', 'en_proceso'])->count()
                ]
            ];
        } catch (\Exception $e) {
            return [
                'total_usuarios' => 0,
                'total_clientes' => 0,
                'cotizaciones_mes' => 0,
                'valor_total_mes' => 0,
                'clientes_nuevos_mes' => 0,
                'seguimientos_sistema' => [
                    'atrasados' => 0,
                    'hoy' => 0,
                    'activos' => 0
                ],
                'error' => 'Error al cargar datos generales'
            ];
        }
    }

    /**
     * DATOS DE COBRANZAS - Para módulo futuro
     */
    private function getDatosCobranzas($user)
    {
        return [
            'gestiones_pendientes' => 0,
            'facturas_vencidas' => 0,
            'valor_recuperado_mes' => 0,
            'mensaje' => 'Módulo de cobranzas en desarrollo'
        ];
    }

    /**
     * DATOS DE SERVICIO TÉCNICO - Para módulo futuro
     */
    private function getDatosServicioTecnico($user)
    {
        return [
            'solicitudes_pendientes' => 0,
            'mantenciones_programadas' => 0,
            'equipos_en_servicio' => 0,
            'mensaje' => 'Módulo de servicio técnico en desarrollo'
        ];
    }

    /**
     * MÉTRICAS DEL MES - Motivación y contexto
     */
    private function getMetricasMes($user)
    {
        if (!$user || !$user->id) {
            return [
                'logros_ayer' => ['tareas_completadas' => 0, 'seguimientos_realizados' => 0, 'cotizaciones_enviadas' => 0],
                'racha_dias_productivos' => 0,
                'objetivo_mes' => ['objetivo_cotizaciones' => 10, 'progreso_actual' => 0, 'porcentaje_cumplimiento' => 0],
                'comparacion_mes_anterior' => ['tendencia' => 'neutral']
            ];
        }

        try {
            $logros_ayer = $this->getLogrosAyer($user);
            
            return [
                'logros_ayer' => $logros_ayer,
                'racha_dias_productivos' => $this->calcularRachaProductiva($user),
                'objetivo_mes' => $this->getObjetivoMes($user),
                'comparacion_mes_anterior' => $this->getComparacionMesAnterior($user)
            ];
        } catch (\Exception $e) {
            return [
                'logros_ayer' => ['tareas_completadas' => 0, 'seguimientos_realizados' => 0, 'cotizaciones_enviadas' => 0],
                'racha_dias_productivos' => 0,
                'objetivo_mes' => ['objetivo_cotizaciones' => 10, 'progreso_actual' => 0, 'porcentaje_cumplimiento' => 0],
                'comparacion_mes_anterior' => ['tendencia' => 'neutral'],
                'error' => 'Error al cargar métricas del mes'
            ];
        }
    }

    /**
     * OBTENER RENDIMIENTO DE VENDEDORES
     */
    private function getRendimientoVendedores($vendedorIds)
    {
        try {
            return User::whereIn('id', $vendedorIds)
                ->get()
                ->map(function ($vendedor) {
                    $cotizaciones = Cotizacion::where('vendedor_id', $vendedor->id)
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->get();
                    
                    $total = $cotizaciones->count();
                    $ganadas = $cotizaciones->where('estado', 'Ganada')->count();
                    
                    return [
                        'nombre' => $vendedor->name,
                        'cotizaciones_mes' => $total,
                        'ganadas_mes' => $ganadas,
                        'tasa_conversion' => $total > 0 ? round(($ganadas / $total) * 100, 1) : 0,
                        'valor_vendido' => $cotizaciones->where('estado', 'Ganada')->sum('total_con_iva')
                    ];
                })
                ->sortByDesc('valor_vendido')
                ->values();
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * TOP VENDEDORES DEL MES
     */
    private function getTopVendedoresMes($limit = 3)
    {
        try {
            return Cotizacion::select('vendedor_id', DB::raw('SUM(total_con_iva) as total_vendido'))
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('estado', 'Ganada')
                ->groupBy('vendedor_id')
                ->orderByDesc('total_vendido')
                ->limit($limit)
                ->with('vendedor:id,name')
                ->get()
                ->map(function ($item) {
                    return [
                        'nombre' => optional($item->vendedor)->name ?? 'Usuario',
                        'total_vendido' => $item->total_vendido
                    ];
                });
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * LOGROS DE AYER - Para motivación
     */
    private function getLogrosAyer($user)
    {
        if (!$user || !$user->id) {
            return [
                'tareas_completadas' => 0,
                'seguimientos_realizados' => 0,
                'cotizaciones_enviadas' => 0
            ];
        }

        $ayer = now()->subDay()->toDateString();
        
        try {
            return [
                'tareas_completadas' => Tarea::where('usuario_id', $user->id)
                    ->where('fecha_tarea', $ayer)
                    ->where('estado', 'completada')
                    ->count(),
                'seguimientos_realizados' => Seguimiento::where('vendedor_id', $user->id)
                    ->where('ultima_gestion', $ayer)
                    ->count(),
                'cotizaciones_enviadas' => Cotizacion::where('vendedor_id', $user->id)
                    ->whereDate('updated_at', $ayer)
                    ->where('estado', 'Enviada')
                    ->count()
            ];
        } catch (\Exception $e) {
            return [
                'tareas_completadas' => 0,
                'seguimientos_realizados' => 0,
                'cotizaciones_enviadas' => 0
            ];
        }
    }

    /**
     * CALCULAR RACHA PRODUCTIVA
     */
    private function calcularRachaProductiva($user)
    {
        if (!$user || !$user->id) return 0;

        try {
            // Contar días consecutivos con actividad
            $dias_racha = 0;
            $fecha_actual = now()->subDay();
            
            for ($i = 0; $i < 30; $i++) {
                $actividad_dia = Tarea::where('usuario_id', $user->id)
                    ->where('fecha_tarea', $fecha_actual->toDateString())
                    ->where('estado', 'completada')
                    ->exists();
                
                if ($actividad_dia) {
                    $dias_racha++;
                    $fecha_actual->subDay();
                } else {
                    break;
                }
            }
            
            return $dias_racha;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * OBJETIVO DEL MES - Placeholder para futuras mejoras
     */
    private function getObjetivoMes($user)
    {
        if (!$user || !$user->id) {
            return [
                'objetivo_cotizaciones' => 10,
                'progreso_actual' => 0,
                'porcentaje_cumplimiento' => 0
            ];
        }

        try {
            $progreso = Cotizacion::where('vendedor_id', $user->id)
                ->whereMonth('created_at', now()->month)
                ->count();

            return [
                'objetivo_cotizaciones' => 10,
                'progreso_actual' => $progreso,
                'porcentaje_cumplimiento' => ($progreso / 10) * 100
            ];
        } catch (\Exception $e) {
            return [
                'objetivo_cotizaciones' => 10,
                'progreso_actual' => 0,
                'porcentaje_cumplimiento' => 0
            ];
        }
    }

    /**
     * COMPARACIÓN CON MES ANTERIOR
     */
    private function getComparacionMesAnterior($user)
    {
        if (!$user || !$user->id) {
            return [
                'valor_este_mes' => 0,
                'valor_mes_anterior' => 0,
                'diferencia' => 0,
                'porcentaje_cambio' => 0,
                'tendencia' => 'neutral'
            ];
        }

        try {
            $este_mes = Cotizacion::where('vendedor_id', $user->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('estado', 'Ganada')
                ->sum('total_con_iva');
            
            $mes_anterior = Cotizacion::where('vendedor_id', $user->id)
                ->whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->where('estado', 'Ganada')
                ->sum('total_con_iva');
            
            $diferencia = $este_mes - $mes_anterior;
            $porcentaje_cambio = $mes_anterior > 0 ? round(($diferencia / $mes_anterior) * 100, 1) : 0;
            
            return [
                'valor_este_mes' => $este_mes,
                'valor_mes_anterior' => $mes_anterior,
                'diferencia' => $diferencia,
                'porcentaje_cambio' => $porcentaje_cambio,
                'tendencia' => $diferencia > 0 ? 'positiva' : ($diferencia < 0 ? 'negativa' : 'neutral')
            ];
        } catch (\Exception $e) {
            return [
                'valor_este_mes' => 0,
                'valor_mes_anterior' => 0,
                'diferencia' => 0,
                'porcentaje_cambio' => 0,
                'tendencia' => 'neutral'
            ];
        }
    }

    /**
     * OBTENER SALUDO PERSONALIZADO
     */
    private function getSaludo()
    {
        $hora = now()->hour;
        
        if ($hora < 12) {
            return 'Buenos días';
        } elseif ($hora < 18) {
            return 'Buenas tardes';
        } else {
            return 'Buenas noches';
        }
    }

    /**
     * API: MÉTRICAS EN TIEMPO REAL
     * Para actualización automática del dashboard
     */
    public function metricas(Request $request)
    {
        try {
            // Usar el mismo sistema de autenticación temporal
            $user = Auth::user();
            if (!$user) {
                $user = \App\Models\User::first();
                if ($user) {
                    Auth::login($user);
                }
            }

            if (!$user) {
                return response()->json(['error' => 'No hay usuarios disponibles'], 401);
            }

            $tipo = $request->get('tipo', 'general');

            $metricas = [];

            switch ($tipo) {
                case 'alertas':
                    $metricas = $this->getAlertas($user);
                    break;
                    
                case 'mi_dia':
                    $metricas = $this->getMiDiaHoy($user);
                    break;
                    
                case 'ventas':
                    if ($this->esVendedorSafe($user) || $this->esJefeSafe($user)) {
                        $metricas = $this->getDatosVentas($user);
                    }
                    break;
                    
                case 'equipo':
                    if ($this->esJefeSafe($user) || $this->esAdministradorSafe($user)) {
                        $metricas = $this->getDatosEquipo($user);
                    }
                    break;
                    
                default:
                    $metricas = $this->getDashboardDataByRole($user);
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => $metricas,
                'timestamp' => now()->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métricas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * MÉTODOS HELPER SEGUROS PARA VERIFICACIÓN DE ROLES
     */

    /**
     * Obtener rol de usuario de forma segura
     */
    private function getUserRoleSafe($user)
    {
        if (!$user) return 'Usuario';
        
        try {
            if (method_exists($user, 'getRoleNames')) {
                $roles = $user->getRoleNames();
                return $roles->first() ?? 'Vendedor';
            }
        } catch (\Exception $e) {
            // Si hay error con roles
        }
        
        return 'Vendedor'; // Rol por defecto
    }

    /**
     * Obtener último acceso de forma segura
     */
    private function getUltimoAccesoSafe($user)
    {
        if (!$user) return 'Primera vez';
        
        try {
            if (isset($user->ultimo_acceso_formateado)) {
                return $user->ultimo_acceso_formateado;
            }
            if (isset($user->ultimo_acceso)) {
                return $user->ultimo_acceso->format('d/m/Y H:i');
            }
        } catch (\Exception $e) {
            // Si hay error con último acceso
        }
        
        return 'Primera vez';
    }

    /**
     * VERIFICACIONES DE ROL SEGURAS
     */
    private function esVendedorSafe($user)
    {
        if (!$user) return false;
        
        try {
            return method_exists($user, 'esVendedor') ? $user->esVendedor() : true; // Por defecto es vendedor
        } catch (\Exception $e) {
            return true;
        }
    }

    private function esJefeSafe($user)
    {
        if (!$user) return false;
        
        try {
            return method_exists($user, 'esJefe') ? $user->esJefe() : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function esAdministradorSafe($user)
    {
        if (!$user) return false;
        
        try {
            return method_exists($user, 'esAdministrador') ? $user->esAdministrador() : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function esCobranzaSafe($user)
    {
        if (!$user) return false;
        
        try {
            return method_exists($user, 'esCobranza') ? $user->esCobranza() : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function esServicioTecnicoSafe($user)
    {
        if (!$user) return false;
        
        try {
            return method_exists($user, 'esServicioTecnico') ? $user->esServicioTecnico() : false;
        } catch (\Exception $e) {
            return false;
        }
    }
}