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

class DashboardController extends Controller
{
    /**
     * DASHBOARD PRINCIPAL - PERSONALIZADO SEGÚN ROL
     * ROADMAP FASE 3: Control Total - Visibilidad completa
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Actualizar último acceso
        $user->update(['ultimo_acceso' => now()]);

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
            'usuario' => $user->name,
            'rol' => $user->getRoleNames()->first(),
            'ultimo_acceso' => $user->ultimo_acceso_formateado,
            'fecha_actual' => now()->format('d/m/Y'),
            'saludo' => $this->getSaludo()
        ];

        // **SECCIÓN "MI DÍA HOY" - COMÚN PARA TODOS**
        $data['mi_dia'] = $this->getMiDiaHoy($user);

        // **ALERTAS CRÍTICAS** - ROADMAP: Rojas y Amarillas
        $data['alertas'] = $this->getAlertas($user);

        // **DATOS ESPECÍFICOS POR ROL**
        if ($user->esVendedor()) {
            $data['ventas'] = $this->getDatosVentas($user);
        }

        if ($user->esJefe()) {
            $data['equipo'] = $this->getDatosEquipo($user);
            $data['ventas'] = $this->getDatosVentas($user); // Jefes también ven sus datos de venta
        }

        if ($user->esAdministrador()) {
            $data['general'] = $this->getDatosGenerales();
            $data['equipo'] = $this->getDatosEquipo($user);
        }

        if ($user->esCobranza()) {
            $data['cobranzas'] = $this->getDatosCobranzas($user);
        }

        if ($user->esServicioTecnico()) {
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
        $tareas_hoy = Tarea::where('usuario_id', $user->id)
            ->where('fecha_tarea', now()->toDateString())
            ->where('estado', '!=', 'completada')
            ->with(['cliente', 'cotizacion', 'seguimiento'])
            ->orderBy('prioridad', 'desc')
            ->orderBy('hora_inicio', 'asc')
            ->get();

        $seguimientos_hoy = Seguimiento::where('vendedor_id', $user->id)
            ->hoy()
            ->with(['cliente', 'cotizacion'])
            ->get();

        $carga_trabajo = $user->getCargaTrabajoHoy();

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
                    'hora_inicio' => $tarea->hora_inicio ? $tarea->hora_inicio->format('H:i') : null,
                    'cliente' => $tarea->cliente->nombre_institucion ?? null,
                    'duracion_estimada' => $tarea->duracion_estimada
                ];
            }),
            'seguimientos_detalle' => $seguimientos_hoy->take(3)->map(function ($seguimiento) {
                return [
                    'id' => $seguimiento->id,
                    'cliente' => $seguimiento->cliente->nombre_institucion,
                    'cotizacion' => $seguimiento->cotizacion->codigo ?? null,
                    'prioridad' => $seguimiento->prioridad,
                    'notas' => substr($seguimiento->notas ?? '', 0, 100)
                ];
            })
        ];
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

        // **ALERTAS ROJAS (CRÍTICAS)**
        
        // Seguimientos vencidos
        $seguimientos_vencidos = Seguimiento::where('vendedor_id', $user->id)
            ->atrasados()
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
        if ($user->esVendedor() || $user->esJefe()) {
            $cotizaciones_vencidas = Cotizacion::where('vendedor_id', $user->id)
                ->vencidas()
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
            ->proximos(7)
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
        if ($user->esVendedor() || $user->esJefe()) {
            $cotizaciones_proximas = Cotizacion::where('vendedor_id', $user->id)
                ->proximasVencer(7)
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

        return $alertas;
    }

    /**
     * DATOS DE VENTAS - Para vendedores y jefes
     */
    private function getDatosVentas($user)
    {
        $query = Cotizacion::where('vendedor_id', $user->id);

        $mes_actual = (clone $query)->delMesActual();
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
    }

    /**
     * DATOS DE EQUIPO - Para jefes y administradores
     */
    private function getDatosEquipo($user)
    {
        $equipoIds = $user->vendedoresSupervision()->pluck('users.id')->toArray();
        
        if (empty($equipoIds)) {
            return [
                'vendedores_activos' => 0,
                'mensaje' => 'No tienes vendedores asignados'
            ];
        }

        $cotizaciones_equipo = Cotizacion::whereIn('vendedor_id', $equipoIds)->delMesActual();
        $seguimientos_equipo = Seguimiento::whereIn('vendedor_id', $equipoIds);

        return [
            'vendedores_activos' => count($equipoIds),
            'cotizaciones_equipo_mes' => $cotizaciones_equipo->count(),
            'valor_equipo_mes' => $cotizaciones_equipo->where('estado', 'Ganada')->sum('total_con_iva'),
            'seguimientos_atrasados_equipo' => (clone $seguimientos_equipo)->atrasados()->count(),
            'rendimiento_vendedores' => $this->getRendimientoVendedores($equipoIds),
            'top_vendedores_mes' => Cotizacion::topVendedoresMes(3)
        ];
    }

    /**
     * DATOS GENERALES - Para administradores
     */
    private function getDatosGenerales()
    {
        return [
            'total_usuarios' => User::activos()->count(),
            'total_clientes' => Cliente::count(),
            'cotizaciones_mes' => Cotizacion::delMesActual()->count(),
            'valor_total_mes' => Cotizacion::valorGanadoMes(),
            'clientes_nuevos_mes' => Cliente::whereMonth('created_at', now()->month)->count(),
            'seguimientos_sistema' => [
                'atrasados' => Seguimiento::atrasados()->count(),
                'hoy' => Seguimiento::hoy()->count(),
                'activos' => Seguimiento::whereIn('estado', ['pendiente', 'en_proceso'])->count()
            ]
        ];
    }

    /**
     * DATOS DE COBRANZAS - Para módulo futuro
     */
    private function getDatosCobranzas($user)
    {
        // TODO: Implementar cuando esté el módulo de cobranzas
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
        // TODO: Implementar cuando esté el módulo de ST
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
        $logros_ayer = $this->getLogrosAyer($user);
        
        return [
            'logros_ayer' => $logros_ayer,
            'racha_dias_productivos' => $this->calcularRachaProductiva($user),
            'objetivo_mes' => $this->getObjetivoMes($user),
            'comparacion_mes_anterior' => $this->getComparacionMesAnterior($user)
        ];
    }

    /**
     * OBTENER RENDIMIENTO DE VENDEDORES
     */
    private function getRendimientoVendedores($vendedorIds)
    {
        return User::whereIn('id', $vendedorIds)
            ->with(['cotizaciones' => function ($query) {
                $query->delMesActual();
            }])
            ->get()
            ->map(function ($vendedor) {
                $cotizaciones = $vendedor->cotizaciones;
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
    }

    /**
     * LOGROS DE AYER - Para motivación
     */
    private function getLogrosAyer($user)
    {
        $ayer = now()->subDay()->toDateString();
        
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
    }

    /**
     * CALCULAR RACHA PRODUCTIVA
     */
    private function calcularRachaProductiva($user)
    {
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
    }

    /**
     * OBJETIVO DEL MES - Placeholder para futuras mejoras
     */
    private function getObjetivoMes($user)
    {
        // TODO: Implementar sistema de objetivos
        return [
            'objetivo_cotizaciones' => 10,
            'progreso_actual' => Cotizacion::where('vendedor_id', $user->id)->delMesActual()->count(),
            'porcentaje_cumplimiento' => 0
        ];
    }

    /**
     * COMPARACIÓN CON MES ANTERIOR
     */
    private function getComparacionMesAnterior($user)
    {
        $este_mes = Cotizacion::where('vendedor_id', $user->id)
            ->delMesActual()
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
            $user = Auth::user();
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
                    if ($user->esVendedor() || $user->esJefe()) {
                        $metricas = $this->getDatosVentas($user);
                    }
                    break;
                    
                case 'equipo':
                    if ($user->esJefe() || $user->esAdministrador()) {
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
}