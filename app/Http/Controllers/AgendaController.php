<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Seguimiento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class AgendaController extends Controller
{
    /**
     * Constructor del controlador
     */
    public function __construct()
    {
        // TODO: Descomentar cuando se implemente el sistema de roles spatie/laravel-permission
        // $this->middleware('auth');
    }

    /**
     * Vista principal de la agenda
     */
    public function index()
    {
        return view('agenda.index');
    }

    /**
     * Vista "Mi Día" - Tareas del día actual
     */
    public function miDia()
    {
        return view('agenda.mi-dia');
    }

    /**
     * Mostrar vista de Mi Semana
     */
    public function miSemana()
    {
        return view('agenda.mi-semana');
    }

    /**
     * API para obtener tareas de la semana
     */
    public function obtenerTareasSemana(Request $request): JsonResponse
    {
        try {
            Carbon::setLocale('es');
            // Obtener parámetros
            
            $semana = $request->get('semana', 'actual'); // 'actual', 'siguiente', 'anterior'
            $usuario_id = $request->get('usuario_id', 1); // TODO: usar auth()->id()
            
            // Calcular fechas de la semana
            $hoy = Carbon::now();
            
            switch ($semana) {
                case 'siguiente':
                    $inicioSemana = $hoy->copy()->addWeek()->startOfWeek();
                    break;
                case 'anterior':
                    $inicioSemana = $hoy->copy()->subWeek()->startOfWeek();
                    break;
                default: // 'actual'
                    $inicioSemana = $hoy->copy()->startOfWeek();
            }
            
            $finSemana = $inicioSemana->copy()->endOfWeek();
            
            // Obtener tareas de la semana
            $tareas = Tarea::with(['usuarioAsignado', 'usuarioCreador', 'cliente', 'cotizacion'])
                ->where('usuario_asignado_id', $usuario_id)
                ->whereBetween('fecha_vencimiento', [$inicioSemana->format('Y-m-d'), $finSemana->format('Y-m-d')])
                ->orderBy('fecha_vencimiento')
                ->orderBy('hora_estimada')
                ->get();
                
            // Agrupar tareas por día
            $tareasPorDia = [];
            for ($i = 0; $i < 7; $i++) {
                $fecha = $inicioSemana->copy()->addDays($i);
                $fechaStr = $fecha->format('Y-m-d');
                
                // Filtrar tareas de este día específico
                $tareasDelDia = $tareas->filter(function($tarea) use ($fechaStr) {
                    return $tarea->fecha_vencimiento && $tarea->fecha_vencimiento->format('Y-m-d') === $fechaStr;
                })->values();

                $tareasPorDia[$fechaStr] = [
                    'fecha' => $fechaStr,
                    'dia_nombre' => $fecha->locale('es')->translatedFormat('l'),
                    'dia_numero' => $fecha->format('d'),
                    'mes_nombre' => $fecha->locale('es')->translatedFormat('M'),
                    'es_hoy' => $fecha->isToday(),
                    'es_pasado' => $fecha->isPast(),
                    'tareas' => $tareasDelDia
                ];
            }
            
            // Estadísticas de la semana
            $estadisticas = [
                'total_semana' => $tareas->count(),
                'completadas' => $tareas->where('estado', 'completada')->count(),
                'pendientes' => $tareas->whereIn('estado', ['pendiente', 'en_progreso'])->count(),
                'vencidas' => $tareas->where('estado', 'vencida')->count(),
                'fecha_inicio' => $inicioSemana->format('d/m/Y'),
                'fecha_fin' => $finSemana->format('d/m/Y')
            ];

            return response()->json([
                'success' => true,
                'data' => $tareasPorDia,
                'estadisticas' => $estadisticas,
                'message' => 'Tareas de la semana obtenidas exitosamente'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tareas de la semana: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * API: Obtener tareas con filtros y paginación
     */
    public function obtenerTareas(Request $request): JsonResponse
    {
        try {
            $query = Tarea::with(['usuarioAsignado', 'usuarioCreador', 'cliente', 'cotizacion', 'seguimiento']);

            // TODO: Filtrar por usuario actual cuando se implemente autenticación
            // $query->where('usuario_asignado_id', auth()->id());

            // Filtros de búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('titulo', 'like', "%{$search}%")
                      ->orWhere('descripcion', 'like', "%{$search}%")
                      ->orWhere('notas', 'like', "%{$search}%");
                });
            }

            // Filtro por fecha
            if ($request->filled('fecha')) {
                $fecha = $request->fecha;
                if ($fecha === 'hoy') {
                    $query->hoy();
                } elseif ($fecha === 'manana') {
                    $query->whereDate('fecha_vencimiento', Carbon::tomorrow());
                } elseif ($fecha === 'esta_semana') {
                    $query->estaSemana();
                } elseif ($fecha === 'vencidas') {
                    $query->vencidas();
                } else {
                    $query->whereDate('fecha_vencimiento', $fecha);
                }
            }

            // Filtro por estado
            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            // Filtro por prioridad
            if ($request->filled('prioridad')) {
                $query->where('prioridad', $request->prioridad);
            }

            // Filtro por tipo
            if ($request->filled('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            // Filtro por usuario asignado (para jefes/administradores)
            if ($request->filled('usuario_asignado_id')) {
                $query->where('usuario_asignado_id', $request->usuario_asignado_id);
            }

            // Ordenamiento
            $sortField = $request->get('sort_field', 'fecha_vencimiento');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortField, $sortDirection);

            // Ordenamiento secundario por prioridad y hora
            $query->orderByRaw("FIELD(prioridad, 'urgente', 'alta', 'media', 'baja')")
                  ->orderBy('hora_estimada', 'asc');

            // Paginación
            $perPage = $request->get('per_page', 20);
            $tareas = $query->paginate($perPage);

            // Estadísticas rápidas
            $estadisticas = [
                'total' => Tarea::count(),
                'pendientes_hoy' => Tarea::hoy()->pendientes()->count(),
                'vencidas' => Tarea::vencidas()->count(),
                'completadas_hoy' => Tarea::hoy()->where('estado', 'completada')->count(),
                'esta_semana' => Tarea::estaSemana()->pendientes()->count(),
                'en_progreso_hoy' => Tarea::hoy()->where('estado', 'en_progreso')->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $tareas,
                'estadisticas' => $estadisticas,
                'message' => 'Tareas obtenidas exitosamente'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las tareas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Crear nueva tarea
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'titulo' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'usuario_asignado_id' => 'required|exists:users,id',
                'tipo' => 'required|in:' . implode(',', Tarea::TIPOS_TAREA),
                'prioridad' => 'required|in:' . implode(',', Tarea::PRIORIDADES_TAREA),
                'fecha_vencimiento' => 'required|date|after_or_equal:today',
                'hora_estimada' => 'nullable|date_format:H:i',
                'duracion_estimada_minutos' => 'nullable|integer|min:1|max:1440',
                'cliente_id' => 'nullable|exists:clientes,id',
                'cotizacion_id' => 'nullable|exists:cotizaciones,id',
                'seguimiento_id' => 'nullable|exists:seguimientos,id',
                'notas' => 'nullable|string',
                'metadatos' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            
            // TODO: Usar usuario autenticado cuando se implemente auth()
            // $data['usuario_creador_id'] = auth()->id();
            $data['usuario_creador_id'] = $data['usuario_asignado_id']; // Temporal

            $tarea = Tarea::create($data);
            $tarea->load(['usuarioAsignado', 'usuarioCreador', 'cliente', 'cotizacion', 'seguimiento']);

            return response()->json([
                'success' => true,
                'data' => $tarea,
                'message' => 'Tarea creada exitosamente'
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la tarea: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obtener tarea específica
     */
    public function show(Tarea $tarea): JsonResponse
    {
        try {
            $tarea->load(['usuarioAsignado', 'usuarioCreador', 'cliente', 'cotizacion', 'seguimiento']);

            return response()->json([
                'success' => true,
                'data' => $tarea,
                'message' => 'Tarea obtenida exitosamente'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la tarea: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Actualizar tarea
     */
    public function update(Request $request, Tarea $tarea): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'titulo' => 'sometimes|required|string|max:255',
                'descripcion' => 'nullable|string',
                'usuario_asignado_id' => 'sometimes|required|exists:users,id',
                'tipo' => 'sometimes|required|in:' . implode(',', Tarea::TIPOS_TAREA),
                'prioridad' => 'sometimes|required|in:' . implode(',', Tarea::PRIORIDADES_TAREA),
                'estado' => 'sometimes|required|in:' . implode(',', Tarea::ESTADOS_TAREA),
                'fecha_vencimiento' => 'sometimes|required|date',
                'hora_estimada' => 'nullable|date_format:H:i',
                'duracion_estimada_minutos' => 'nullable|integer|min:1|max:1440',
                'cliente_id' => 'nullable|exists:clientes,id',
                'cotizacion_id' => 'nullable|exists:cotizaciones,id',
                'seguimiento_id' => 'nullable|exists:seguimientos,id',
                'notas' => 'nullable|string',
                'resultado' => 'nullable|string',
                'metadatos' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Si se está marcando como completada, establecer fecha de completado
            if (isset($data['estado']) && $data['estado'] === 'completada' && $tarea->estado !== 'completada') {
                $data['fecha_completada'] = Carbon::now();
            }

            $tarea->update($data);
            $tarea->load(['usuarioAsignado', 'usuarioCreador', 'cliente', 'cotizacion', 'seguimiento']);

            return response()->json([
                'success' => true,
                'data' => $tarea,
                'message' => 'Tarea actualizada exitosamente'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la tarea: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Eliminar tarea
     */
    public function destroy(Tarea $tarea): JsonResponse
    {
        try {
            $tarea->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tarea eliminada exitosamente'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la tarea: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Completar tarea rápidamente
     */
    public function completarTarea(Request $request, Tarea $tarea): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'resultado' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $resultado = $request->input('resultado');
            $success = $tarea->marcarComoCompletada($resultado);

            if ($success) {
                $tarea->load(['usuarioAsignado', 'usuarioCreador', 'cliente', 'cotizacion', 'seguimiento']);

                return response()->json([
                    'success' => true,
                    'data' => $tarea,
                    'message' => 'Tarea completada exitosamente'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al completar la tarea'
                ], 500);
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al completar la tarea: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Posponer tarea
     */
    public function posponerTarea(Request $request, Tarea $tarea): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nueva_fecha' => 'required|date|after:today',
                'motivo' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $nuevaFecha = $request->input('nueva_fecha');
            $motivo = $request->input('motivo');
            
            $success = $tarea->posponer($nuevaFecha, $motivo);

            if ($success) {
                $tarea->load(['usuarioAsignado', 'usuarioCreador', 'cliente', 'cotizacion', 'seguimiento']);

                return response()->json([
                    'success' => true,
                    'data' => $tarea,
                    'message' => 'Tarea pospuesta exitosamente'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al posponer la tarea'
                ], 500);
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al posponer la tarea: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Distribución automática de seguimientos vencidos
     */
    public function distribuirSeguimientosVencidos(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'usuario_asignado_id' => 'required|exists:users,id',
                'tareas_por_dia' => 'required|integer|min:1|max:50',
                'dias_habiles' => 'required|integer|min:1|max:30'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $usuarioId = $request->input('usuario_asignado_id');
            $tareasPorDia = $request->input('tareas_por_dia');
            $diasHabiles = $request->input('dias_habiles');

            // TODO: Verificar permisos cuando se implemente el sistema de roles
            // Solo Jefe de Ventas puede distribuir tareas masivamente

            // Obtener seguimientos vencidos sin tarea asignada
            $seguimientosVencidos = Seguimiento::where('proxima_gestion', '<', Carbon::today())
                ->whereIn('estado', ['pendiente', 'en_proceso'])
                ->whereDoesntHave('tareas')
                ->limit($tareasPorDia * $diasHabiles)
                ->get();

            if ($seguimientosVencidos->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No hay seguimientos vencidos para distribuir',
                    'data' => ['tareas_creadas' => 0]
                ]);
            }

            $tareasCreadas = 0;
            $fechaActual = Carbon::today();

            // Distribuir seguimientos en días hábiles
            foreach ($seguimientosVencidos->chunk($tareasPorDia) as $chunkIndex => $chunk) {
                $fechaTarea = $fechaActual->copy()->addDays($chunkIndex);
                
                // Saltar fines de semana
                while ($fechaTarea->isWeekend()) {
                    $fechaTarea->addDay();
                }

                foreach ($chunk as $seguimiento) {
                    Tarea::create([
                        'titulo' => "Seguimiento: {$seguimiento->cliente->nombre_institucion}",
                        'descripcion' => "Seguimiento vencido desde {$seguimiento->proxima_gestion->format('d/m/Y')}",
                        'usuario_asignado_id' => $usuarioId,
                        'usuario_creador_id' => $usuarioId, // TODO: auth()->id() cuando se implemente
                        'tipo' => 'seguimiento',
                        'origen' => 'distribucion_automatica',
                        'seguimiento_id' => $seguimiento->id,
                        'cliente_id' => $seguimiento->cliente_id,
                        'fecha_vencimiento' => $fechaTarea,
                        'prioridad' => 'alta',
                        'es_distribuida_automaticamente' => true,
                        'metadatos' => [
                            'distribucion_automatica' => true,
                            'fecha_distribucion' => Carbon::now(),
                            'seguimiento_vencido_desde' => $seguimiento->proxima_gestion
                        ]
                    ]);

                    $tareasCreadas++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Se distribuyeron {$tareasCreadas} tareas de seguimiento exitosamente",
                'data' => [
                    'tareas_creadas' => $tareasCreadas,
                    'seguimientos_procesados' => $seguimientosVencidos->count()
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al distribuir seguimientos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obtener datos para formularios (usuarios, clientes, etc.)
     */
    public function obtenerDatosFormulario(): JsonResponse
    {
        try {
            $data = [
                'usuarios' => User::select('id', 'name', 'email')->get(),
                'clientes' => Cliente::select('id', 'nombre_institucion', 'rut')->get(),
                'tipos_tarea' => Tarea::TIPOS_TAREA,
                'prioridades' => Tarea::PRIORIDADES_TAREA,
                'estados' => Tarea::ESTADOS_TAREA
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Datos obtenidos exitosamente'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos: ' . $e->getMessage()
            ], 500);
        }
    }


/**
 * API: Análisis inteligente de carga de trabajo
 */
public function analizarCargaTrabajo(Request $request): JsonResponse
{
    try {
        $usuarioId = $request->get('usuario_id', 1); // TODO: auth()->id()
        $semana = $request->get('semana', 'actual'); // 'actual', 'siguiente'
        
        // Calcular fechas de análisis (próximos 7 días)
        $fechaInicio = Carbon::now();
        $fechaFin = $fechaInicio->copy()->addDays(6);
        
       // Obtener tareas agrupadas por día
        $tareas = Tarea::where('usuario_asignado_id', $usuarioId)
            ->whereBetween('fecha_vencimiento', [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')])
            ->whereIn('estado', ['pendiente', 'en_progreso'])
            ->get()
            ->groupBy(function($tarea) {
                return $tarea->fecha_vencimiento->format('Y-m-d');
            });

        // 🔍 DEBUG TEMPORAL - AGREGAR ESTAS LÍNEAS:
        \Log::info('🧠 ANÁLISIS DEBUG:', [
            'usuario_id' => $usuarioId,
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaFin->format('Y-m-d'),
            'total_tareas_encontradas' => Tarea::where('usuario_asignado_id', $usuarioId)
                ->whereBetween('fecha_vencimiento', [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')])
                ->count(),
            'tareas_por_fecha' => $tareas->map(fn($grupo) => $grupo->count()),
            'todas_las_fechas_de_tareas' => Tarea::where('usuario_asignado_id', $usuarioId)
                ->pluck('fecha_vencimiento', 'titulo')->toArray()
        ]);
        $analisis = [];
        $alertas = [];
        $sugerencias = [];
        
        // Analizar cada día
        for ($i = 0; $i < 7; $i++) {
            $fecha = $fechaInicio->copy()->addDays($i);
            $fechaStr = $fecha->format('Y-m-d');
            $cantidadTareas = $tareas->get($fechaStr, collect())->count();
            
            // Determinar nivel de carga
            $nivelCarga = 'normal';
            if ($cantidadTareas >= 10) {
                $nivelCarga = 'sobrecarga';
            } elseif ($cantidadTareas >= 7) {
                $nivelCarga = 'alta';
            }
            
            $analisis[$fechaStr] = [
                'fecha' => $fechaStr,
                'dia_nombre' => $fecha->translatedFormat('l'),
                'cantidad_tareas' => $cantidadTareas,
                'nivel_carga' => $nivelCarga,
                'es_hoy' => $fecha->isToday(),
                'es_manana' => $fecha->isTomorrow()
            ];
            
            // Generar alertas para sobrecarga
            if ($nivelCarga === 'sobrecarga') {
                $alertas[] = [
                    'tipo' => 'sobrecarga',
                    'mensaje' => $fecha->isToday() ? 
                        "¡Hoy tienes {$cantidadTareas} tareas! Muy sobrecargado." :
                        ($fecha->isTomorrow() ? 
                            "Mañana tienes {$cantidadTareas} tareas. ¡Atención!" :
                            "{$fecha->translatedFormat('l')} tienes {$cantidadTareas} tareas."
                        ),
                    'fecha' => $fechaStr,
                    'prioridad' => $fecha->isToday() ? 'urgente' : ($fecha->isTomorrow() ? 'alta' : 'media')
                ];
            }
        }
        
        // Generar sugerencias de redistribución
        if (!empty($alertas)) {
            $sugerencias = $this->generarSugerenciasRedistribucion($analisis, $tareas);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'analisis_semanal' => $analisis,
                'alertas' => $alertas,
                'sugerencias' => $sugerencias,
                'resumen' => [
                    'dias_sobrecargados' => count(array_filter($analisis, fn($dia) => $dia['nivel_carga'] === 'sobrecarga')),
                    'dias_alta_carga' => count(array_filter($analisis, fn($dia) => $dia['nivel_carga'] === 'alta')),
                    'total_tareas_semana' => array_sum(array_column($analisis, 'cantidad_tareas'))
                ]
            ],
            'message' => 'Análisis de carga completado'
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error en análisis: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Generar sugerencias inteligentes de redistribución
 */
private function generarSugerenciasRedistribucion($analisis, $tareas): array
{
    $sugerencias = [];
    
    // Encontrar días sobrecargados y días con poca carga
    $diasSobrecargados = array_filter($analisis, fn($dia) => $dia['nivel_carga'] === 'sobrecarga');
    $diasLibres = array_filter($analisis, fn($dia) => $dia['cantidad_tareas'] <= 4);
    
    foreach ($diasSobrecargados as $diaSobrecargado) {
        $tareasDelDia = $tareas->get($diaSobrecargado['fecha'], collect());
        $tareasMovibles = $tareasDelDia->where('prioridad', '!=', 'urgente')->take(4);
        
        if ($tareasMovibles->count() > 0 && !empty($diasLibres)) {
            $diaDestino = array_values($diasLibres)[0]; // Primer día libre
            
            $sugerencias[] = [
                'tipo' => 'redistribucion',
                'dia_origen' => $diaSobrecargado['fecha'],
                'dia_destino' => $diaDestino['fecha'],
                'tareas_a_mover' => $tareasMovibles->pluck('id')->toArray(),
                'cantidad_tareas' => $tareasMovibles->count(),
                'beneficio' => "Reducir sobrecarga del {$diaSobrecargado['dia_nombre']} moviendo {$tareasMovibles->count()} tareas al {$diaDestino['dia_nombre']}",
                'impacto' => [
                    'origen_antes' => $diaSobrecargado['cantidad_tareas'],
                    'origen_despues' => $diaSobrecargado['cantidad_tareas'] - $tareasMovibles->count(),
                    'destino_antes' => $diaDestino['cantidad_tareas'],
                    'destino_despues' => $diaDestino['cantidad_tareas'] + $tareasMovibles->count()
                ]
            ];
        }
    }
    
    return $sugerencias;
}
}
