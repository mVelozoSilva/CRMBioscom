<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Cotizacion;
use App\Models\Contacto;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Muestra la pantalla principal (Dashboard) con métricas completas
     */
    public function index()
    {
        // === MÉTRICAS BÁSICAS ===
        $totalClientes = Cliente::count();
        $totalProductos = Producto::count();
        $productosActivos = Producto::where('estado', 'Activo')->count();
        $totalContactos = Contacto::count();

        // === MÉTRICAS DE COTIZACIONES ===
        $totalCotizaciones = Cotizacion::count();
        $cotizacionesPendientes = Cotizacion::where('estado', 'Pendiente')->count();
        $cotizacionesEnviadas = Cotizacion::where('estado', 'Enviada')->count();
        $cotizacionesAceptadas = Cotizacion::where('estado', 'Aceptada')->count();

        // === MÉTRICAS DEL MES ACTUAL ===
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        $clientesEsteMes = Cliente::whereBetween('created_at', [$inicioMes, $finMes])->count();
        $cotizacionesEsteMes = Cotizacion::whereBetween('created_at', [$inicioMes, $finMes])->count();
        
        // Valor total de cotizaciones este mes
        $valorCotizacionesEsteMes = Cotizacion::whereBetween('created_at', [$inicioMes, $finMes])
            ->sum('total_con_iva');

        // === MÉTRICAS DE ACTIVIDAD RECIENTE ===
        $cotizacionesRecientes = Cotizacion::with('cliente')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $clientesRecientes = Cliente::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // === MÉTRICAS DE PRODUCTOS ===
        $categorias = Producto::select('categoria')
            ->distinct()
            ->whereNotNull('categoria')
            ->pluck('categoria');

        $categoriaStats = [];
        foreach ($categorias as $categoria) {
            $categoriaStats[] = [
                'nombre' => $categoria,
                'cantidad' => Producto::where('categoria', $categoria)->count()
            ];
        }

        // === MÉTRICAS DE RENDIMIENTO ===
        // Tasa de conversión de cotizaciones (si tienes datos)
        $tasaConversion = $totalCotizaciones > 0 
            ? round(($cotizacionesAceptadas / $totalCotizaciones) * 100, 1) 
            : 0;

        // === ALERTAS Y NOTIFICACIONES ===
        $alertas = [];

        // Alerta si hay pocas cotizaciones este mes
        if ($cotizacionesEsteMes < 5) {
            $alertas[] = [
                'tipo' => 'warning',
                'mensaje' => "Solo {$cotizacionesEsteMes} cotizaciones generadas este mes."
            ];
        }

        // Alerta si hay muchas cotizaciones pendientes
        if ($cotizacionesPendientes > 10) {
            $alertas[] = [
                'tipo' => 'info',
                'mensaje' => "{$cotizacionesPendientes} cotizaciones pendientes requieren seguimiento."
            ];
        }

        // Alerta si no hay clientes nuevos este mes
        if ($clientesEsteMes == 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'mensaje' => 'No se han registrado clientes nuevos este mes.'
            ];
        }

        // === PREPARAR DATOS PARA GRÁFICOS ===
        // Datos para gráfico de cotizaciones por estado
        $graficoCotizaciones = [
            'labels' => ['Pendientes', 'Enviadas', 'Aceptadas', 'Rechazadas'],
            'data' => [
                $cotizacionesPendientes,
                $cotizacionesEnviadas,
                $cotizacionesAceptadas,
                Cotizacion::where('estado', 'Rechazada')->count()
            ],
            'colors' => ['#ffc107', '#17a2b8', '#28a745', '#dc3545']
        ];

        // Compilar todas las métricas
        $metricas = compact(
            'totalClientes',
            'totalProductos', 
            'productosActivos',
            'totalContactos',
            'totalCotizaciones',
            'cotizacionesPendientes',
            'cotizacionesEnviadas',
            'cotizacionesAceptadas',
            'clientesEsteMes',
            'cotizacionesEsteMes',
            'valorCotizacionesEsteMes',
            'cotizacionesRecientes',
            'clientesRecientes',
            'categoriaStats',
            'tasaConversion',
            'alertas',
            'graficoCotizaciones'
        );

        return view('dashboard.index', $metricas);
    }

    /**
     * API para obtener métricas en tiempo real (para AJAX)
     */
    public function metricas(Request $request)
    {
        if (!$request->expectsJson()) {
            return response()->json(['error' => 'Esta ruta es solo para API'], 400);
        }

        // Métricas rápidas para actualización en tiempo real
        $metricas = [
            'total_clientes' => Cliente::count(),
            'total_cotizaciones' => Cotizacion::count(),
            'cotizaciones_pendientes' => Cotizacion::where('estado', 'Pendiente')->count(),
            'valor_mes_actual' => Cotizacion::whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ])->sum('total_con_iva')
        ];

        return response()->json($metricas);
    }
}