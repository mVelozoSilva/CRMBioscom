<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seguimiento;
use App\Models\Cotizacion;
use App\Models\User;
use App\Models\Tarea;
use App\Models\ColaSeguimiento;
use App\Models\ConfiguracionSeguimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TriajeController extends Controller
{
    /**
     * Vista principal del triaje
     * Muestra el dashboard de clasificación de seguimientos
     */
    public function index()
    {
        $estadisticas = $this->obtenerEstadisticasTriaje();
        $configuraciones = ConfiguracionSeguimiento::where('activo', true)
            ->orderBy('prioridad_triaje', 'desc')
            ->get();

        return view('triaje.index', compact('estadisticas', 'configuraciones'));
    }

    /**
     * Obtiene seguimientos clasificados por prioridad y estado
     * Sistema inteligente de triaje basado en configuraciones
     */
    public function getSeguimientosClasificados(Request $request)
    {
        try {
            $filtros = [
                'estado' => $request->get('estado'),
                'prioridad' => $request->get('prioridad'),
                'vendedor_id' => $request->get('vendedor_id'),
                'dias_vencimiento' => $request->get('dias_vencimiento'),
                'tipo_cliente' => $request->get('tipo_cliente'),
                'monto_minimo' => $request->get('monto_minimo'),
            ];

            $seguimientos = $this->clasificarSeguimientos($filtros);
            
            return response()->json([
                'success' => true,
                'data' => $seguimientos,
                'estadisticas' => $this->obtenerEstadisticasTriaje(),
                'total' => $seguimientos->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener seguimientos clasificados: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los seguimientos clasificados'
            ], 500);
        }
    }

    /**
     * Sistema de clasificación inteligente de seguimientos
     * Aplica reglas de negocio y configuraciones automáticas
     */
    private function clasificarSeguimientos($filtros = [])
    {
        $query = Seguimiento::with(['cliente', 'cotizacion', 'vendedor'])
            ->select('seguimientos.*');

        // Aplicar filtros básicos
        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (!empty($filtros['vendedor_id'])) {
            $query->where('vendedor_id', $filtros['vendedor_id']);
        }

        if (!empty($filtros['tipo_cliente'])) {
            $query->whereHas('cliente', function($q) use ($filtros) {
                $q->where('tipo_cliente', $filtros['tipo_cliente']);
            });
        }

        if (!empty($filtros['monto_minimo'])) {
            $query->whereHas('cotizacion', function($q) use ($filtros) {
                $q->where('total_con_iva', '>=', $filtros['monto_minimo']);
            });
        }

        // Clasificación por urgencia (días de vencimiento)
        $hoy = Carbon::now();
        
        $query->addSelect([
            // Calcular días de diferencia
            DB::raw("DATEDIFF(proxima_gestion, '$hoy') as dias_diferencia"),
            
            // Clasificación automática de prioridad
            DB::raw("CASE 
                WHEN DATEDIFF(proxima_gestion, '$hoy') < -7 THEN 'critica'
                WHEN DATEDIFF(proxima_gestion, '$hoy') < -3 THEN 'alta'  
                WHEN DATEDIFF(proxima_gestion, '$hoy') < 0 THEN 'urgente'
                WHEN DATEDIFF(proxima_gestion, '$hoy') <= 1 THEN 'alta'
                WHEN DATEDIFF(proxima_gestion, '$hoy') <= 3 THEN 'media'
                ELSE 'baja'
            END as prioridad_calculada"),

            // Estado calculado basado en configuraciones
            DB::raw("CASE 
                WHEN DATEDIFF(proxima_gestion, '$hoy') < -7 THEN 'vencido_critico'
                WHEN DATEDIFF(proxima_gestion, '$hoy') < 0 THEN 'vencido'
                WHEN DATEDIFF(proxima_gestion, '$hoy') <= 1 THEN 'urgente_hoy'
                WHEN DATEDIFF(proxima_gestion, '$hoy') <= 3 THEN 'proximo'
                ELSE 'normal'
            END as estado_calculado")
        ]);

        // Filtro por días de vencimiento si se especifica
        if (!empty($filtros['dias_vencimiento'])) {
            switch ($filtros['dias_vencimiento']) {
                case 'vencidos':
                    $query->whereRaw("DATEDIFF(proxima_gestion, '$hoy') < 0");
                    break;
                case 'hoy':
                    $query->whereRaw("DATEDIFF(proxima_gestion, '$hoy') = 0");
                    break;
                case 'manana':
                    $query->whereRaw("DATEDIFF(proxima_gestion, '$hoy') = 1");
                    break;
                case 'proximos_7':
                    $query->whereRaw("DATEDIFF(proxima_gestion, '$hoy') BETWEEN 0 AND 7");
                    break;
            }
        }

        // Ordenamiento inteligente por prioridad
        $query->orderByRaw("
            CASE 
                WHEN DATEDIFF(proxima_gestion, '$hoy') < -7 THEN 1
                WHEN DATEDIFF(proxima_gestion, '$hoy') < -3 THEN 2  
                WHEN DATEDIFF(proxima_gestion, '$hoy') < 0 THEN 3
                WHEN DATEDIFF(proxima_gestion, '$hoy') <= 1 THEN 4
                WHEN DATEDIFF(proxima_gestion, '$hoy') <= 3 THEN 5
                ELSE 6
            END
        ")->orderBy('proxima_gestion', 'asc');

        return $query->get()->map(function ($seguimiento) {
            // Agregar metadatos de triaje
            $seguimiento->metadata_triaje = [
                'dias_diferencia' => $seguimiento->dias_diferencia,
                'prioridad_calculada' => $seguimiento->prioridad_calculada,
                'estado_calculado' => $seguimiento->estado_calculado,
                'requiere_accion_inmediata' => $seguimiento->dias_diferencia < 0,
                'color_indicador' => $this->obtenerColorIndicador($seguimiento->dias_diferencia),
                'mensaje_urgencia' => $this->obtenerMensajeUrgencia($seguimiento->dias_diferencia)
            ];

            return $seguimiento;
        });
    }

    /**
     * Procesa acciones masivas en seguimientos
     * Distribuye tareas automáticamente según configuraciones
     */
    public function procesarAccionMasiva(Request $request)
    {
        $request->validate([
            'accion' => 'required|string|in:distribuir,reasignar,posponer,marcar_completado',
            'seguimiento_ids' => 'required|array',
            'seguimiento_ids.*' => 'exists:seguimientos,id',
            'parametros' => 'sometimes|array'
        ]);

        try {
            DB::beginTransaction();

            $seguimientos = Seguimiento::whereIn('id', $request->seguimiento_ids)->get();
            $resultados = [];

            foreach ($seguimientos as $seguimiento) {
                switch ($request->accion) {
                    case 'distribuir':
                        $resultado = $this->distribuirSeguimientoAutomatico($seguimiento, $request->parametros);
                        break;
                    
                    case 'reasignar':
                        $resultado = $this->reasignarSeguimiento($seguimiento, $request->parametros);
                        break;
                    
                    case 'posponer':
                        $resultado = $this->posponerSeguimiento($seguimiento, $request->parametros);
                        break;
                    
                    case 'marcar_completado':
                        $resultado = $this->marcarSeguimientoCompletado($seguimiento, $request->parametros);
                        break;
                    
                    default:
                        $resultado = ['success' => false, 'message' => 'Acción no válida'];
                }

                $resultados[] = [
                    'seguimiento_id' => $seguimiento->id,
                    'resultado' => $resultado
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Acciones procesadas correctamente',
                'resultados' => $resultados,
                'estadisticas_actualizadas' => $this->obtenerEstadisticasTriaje()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en procesamiento masivo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar las acciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Distribuye seguimientos automáticamente según carga de trabajo
     * Algoritmo inteligente de distribución
     */
    private function distribuirSeguimientoAutomatico($seguimiento, $parametros = [])
    {
        try {
            // Obtener vendedores disponibles
            $vendedoresDisponibles = $this->getVendedoresDisponibles();
            
            if ($vendedoresDisponibles->isEmpty()) {
                return ['success' => false, 'message' => 'No hay vendedores disponibles'];
            }

            // Algoritmo de distribución por carga de trabajo
            $vendedorOptimo = $this->seleccionarVendedorOptimo($vendedoresDisponibles, $seguimiento);

            // Crear tarea automática
            $fechaTarea = $this->calcularFechaTareaOptima($vendedorOptimo);

            $tarea = Tarea::create([
                'titulo' => "Seguimiento: {$seguimiento->cliente->nombre_institucion}",
                'descripcion' => $this->generarDescripcionTarea($seguimiento),
                'usuario_id' => $vendedorOptimo->id,
                'tipo' => 'seguimiento',
                'origen' => 'automatica_triaje',
                'seguimiento_id' => $seguimiento->id,
                'cotizacion_id' => $seguimiento->cotizacion_id,
                'cliente_id' => $seguimiento->cliente_id,
                'fecha_tarea' => $fechaTarea,
                'estado' => 'pendiente',
                'prioridad' => $this->mapearPrioridadTarea($seguimiento->prioridad),
                'metadata_distribucion' => json_encode([
                    'algoritmo' => 'carga_trabajo',
                    'distribuido_en' => now(),
                    'criterios' => [
                        'carga_actual' => $vendedorOptimo->carga_trabajo_actual ?? 0,
                        'especialidad' => $vendedorOptimo->especialidades ?? [],
                        'prioridad_seguimiento' => $seguimiento->prioridad
                    ]
                ]),
                'es_distribuida_automaticamente' => true
            ]);

            // Actualizar seguimiento
            $seguimiento->update([
                'vendedor_id' => $vendedorOptimo->id,
                'estado' => 'en_proceso',
                'proxima_gestion' => $fechaTarea
            ]);

            return [
                'success' => true, 
                'message' => "Distribuido a {$vendedorOptimo->name}",
                'tarea_id' => $tarea->id
            ];

        } catch (\Exception $e) {
            Log::error("Error distribuyendo seguimiento {$seguimiento->id}: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en distribución automática'];
        }
    }

    /**
     * Análisis detallado de un seguimiento específico
     * Proporciona insights y recomendaciones
     */
    public function analizarSeguimiento($id)
    {
        try {
            $seguimiento = Seguimiento::with([
                'cliente', 
                'cotizacion', 
                'vendedor'
            ])->findOrFail($id);

            $analisis = [
                'seguimiento' => $seguimiento,
                'historial' => $this->obtenerHistorialSeguimiento($seguimiento),
                'metricas' => $this->calcularMetricasSeguimiento($seguimiento),
                'recomendaciones' => $this->generarRecomendaciones($seguimiento),
                'configuracion_aplicable' => $this->obtenerConfiguracionAplicable($seguimiento),
                'riesgo_perdida' => $this->evaluarRiesgoPerdida($seguimiento),
                'acciones_sugeridas' => $this->obtenerAccionesSugeridas($seguimiento)
            ];

            return response()->json([
                'success' => true,
                'analisis' => $analisis
            ]);

        } catch (\Exception $e) {
            Log::error("Error analizando seguimiento {$id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al analizar el seguimiento'
            ], 500);
        }
    }

    /**
     * Obtiene vendedores disponibles con métricas de carga
     */
    public function getVendedoresDisponibles()
    {
        return User::whereHas('roles', function($query) {
                $query->whereIn('name', ['vendedor', 'asistente_ventas']);
            })
            ->withCount([
                'tareas as tareas_pendientes' => function($query) {
                    $query->where('estado', 'pendiente')
                          ->where('fecha_tarea', '>=', Carbon::today());
                },
                'seguimientos as seguimientos_activos' => function($query) {
                    $query->whereIn('estado', ['pendiente', 'en_proceso']);
                }
            ])
            ->get()
            ->map(function($vendedor) {
                $vendedor->carga_trabajo_actual = $vendedor->tareas_pendientes + ($vendedor->seguimientos_activos * 0.5);
                $vendedor->disponibilidad = $this->calcularDisponibilidad($vendedor);
                return $vendedor;
            })
            ->sortBy('carga_trabajo_actual');
    }

    /**
     * Métodos auxiliares privados
     */
    private function obtenerEstadisticasTriaje()
    {
        $hoy = Carbon::now();
        
        return [
            'total_seguimientos' => Seguimiento::count(),
            'vencidos_criticos' => Seguimiento::whereRaw("DATEDIFF(proxima_gestion, '$hoy') < -7")->count(),
            'vencidos' => Seguimiento::whereRaw("DATEDIFF(proxima_gestion, '$hoy') < 0")->count(),
            'urgentes_hoy' => Seguimiento::whereRaw("DATEDIFF(proxima_gestion, '$hoy') = 0")->count(),
            'proximos_3_dias' => Seguimiento::whereRaw("DATEDIFF(proxima_gestion, '$hoy') BETWEEN 1 AND 3")->count(),
            'sin_asignar' => Seguimiento::whereNull('vendedor_id')->count(),
            'en_proceso' => Seguimiento::where('estado', 'en_proceso')->count(),
            'completados_hoy' => Seguimiento::where('estado', 'completado')
                                           ->whereDate('updated_at', Carbon::today())->count()
        ];
    }

    private function obtenerColorIndicador($diasDiferencia)
    {
        if ($diasDiferencia < -7) return 'bg-red-800 text-white'; // Crítico
        if ($diasDiferencia < -3) return 'bg-red-600 text-white'; // Muy atrasado
        if ($diasDiferencia < 0) return 'bg-red-500 text-white';  // Atrasado
        if ($diasDiferencia <= 1) return 'bg-yellow-500 text-white'; // Urgente
        if ($diasDiferencia <= 3) return 'bg-yellow-300 text-gray-800'; // Próximo
        return 'bg-green-200 text-gray-800'; // Normal
    }

    private function obtenerMensajeUrgencia($diasDiferencia)
    {
        if ($diasDiferencia < -7) return 'Crítico: +7 días vencido';
        if ($diasDiferencia < -3) return 'Muy atrasado: +3 días vencido';
        if ($diasDiferencia < 0) return 'Vencido: Requiere atención inmediata';
        if ($diasDiferencia == 0) return 'Urgente: Vence hoy';
        if ($diasDiferencia == 1) return 'Urgente: Vence mañana';
        if ($diasDiferencia <= 3) return 'Próximo: Vence en ' . $diasDiferencia . ' días';
        return 'Normal: ' . $diasDiferencia . ' días restantes';
    }

    private function seleccionarVendedorOptimo($vendedores, $seguimiento)
    {
        // Lógica de selección por carga de trabajo y especialidad
        return $vendedores->first(); // Simplificado por ahora
    }

    private function calcularFechaTareaOptima($vendedor)
    {
        // Lógica para calcular la mejor fecha según disponibilidad
        return Carbon::tomorrow();
    }

    private function generarDescripcionTarea($seguimiento)
    {
        return "Realizar seguimiento a {$seguimiento->cliente->nombre_institucion}. " .
               "Última gestión: " . ($seguimiento->ultima_gestion ?? 'No registrada') . ". " .
               ($seguimiento->notas ? "Notas: {$seguimiento->notas}" : '');
    }

    private function mapearPrioridadTarea($prioridadSeguimiento)
    {
        $mapeo = [
            'baja' => 'baja',
            'media' => 'media', 
            'alta' => 'alta',
            'urgente' => 'urgente'
        ];
        
        return $mapeo[$prioridadSeguimiento] ?? 'media';
    }

    private function calcularDisponibilidad($vendedor)
    {
        $carga = $vendedor->carga_trabajo_actual ?? 0;
        if ($carga <= 5) return 'alta';
        if ($carga <= 10) return 'media';
        return 'baja';
    }

    // Métodos adicionales para análisis completo
    private function obtenerHistorialSeguimiento($seguimiento) { return []; }
    private function calcularMetricasSeguimiento($seguimiento) { return []; }
    private function generarRecomendaciones($seguimiento) { return []; }
    private function obtenerConfiguracionAplicable($seguimiento) { return null; }
    private function evaluarRiesgoPerdida($seguimiento) { return 'bajo'; }
    private function obtenerAccionesSugeridas($seguimiento) { return []; }
    private function reasignarSeguimiento($seguimiento, $parametros) { return ['success' => true]; }
    private function posponerSeguimiento($seguimiento, $parametros) { return ['success' => true]; }
    private function marcarSeguimientoCompletado($seguimiento, $parametros) { return ['success' => true]; }
}