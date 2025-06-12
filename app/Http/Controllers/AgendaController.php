<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use App\Models\Seguimiento;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\ColaSeguimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AgendaController extends Controller
{
    /**
     * Vista "Mi Día" - Dashboard principal de agenda
     * Muestra todas las tareas del día con información contextual
     */
    public function miDia(Request $request)
    {
        $usuario = Auth::user();
        $fecha = $request->get('fecha', Carbon::today()->format('Y-m-d'));
        $fechaCarbon = Carbon::parse($fecha);

        // Tareas del día
        $tareas = Tarea::with(['cliente', 'seguimiento.cotizacion', 'cotizacion'])
            ->deUsuario($usuario->id)
            ->whereDate('fecha_tarea', $fechaCarbon)
            ->orderBy('prioridad', 'desc')
            ->orderBy('hora_inicio', 'asc')
            ->get();

        // Seguimientos vencidos que pueden convertirse en tareas
        $seguimientosVencidos = Seguimiento::with(['cliente', 'cotizacion'])
            ->where('vendedor_id', $usuario->id)
            ->where('proxima_gestion', '<', Carbon::today())
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->orderBy('proxima_gestion', 'asc')
            ->limit(5)
            ->get();

        // Estadísticas del día
        $estadisticas = [
            'total_tareas' => $tareas->count(),
            'completadas' => $tareas->where('estado', 'completada')->count(),
            'pendientes' => $tareas->where('estado', 'pendiente')->count(),
            'en_progreso' => $tareas->where('estado', 'en_progreso')->count(),
            'vencidas' => $tareas->where('es_vencida', true)->count(),
            'tiempo_estimado_total' => $tareas->sum('duracion_estimada'),
            'seguimientos_vencidos' => $seguimientosVencidos->count()
        ];

        // Carga de trabajo de la semana
        $cargaSemana = $this->obtenerCargaSemana($usuario->id, $fechaCarbon);

        // Próximas tareas (siguiente día laboral)
        $proximasTareas = $this->obtenerProximasTareas($usuario->id, $fechaCarbon);

        // Alertas y notificaciones
        $alertas = $this->generarAlertas($usuario->id, $fechaCarbon);

        return view('agenda.mi-dia', compact(
            'tareas', 
            'seguimientosVencidos', 
            'estadisticas', 
            'cargaSemana', 
            'proximasTareas', 
            'alertas', 
            'fechaCarbon'
        ));
    }

    /**
     * Vista "Mi Semana" - Planificación semanal
     * Vista tipo calendario con distribución de tareas
     */
    public function miSemana(Request $request)
    {
        $usuario = Auth::user();
        $semana = $request->get('semana', Carbon::now()->format('Y-W'));
        
        // Parsear semana (formato: YYYY-WW)
        [$year, $weekNumber] = explode('-', $semana);
        $fechaInicio = Carbon::now()->setISODate($year, $weekNumber)->startOfWeek();
        $fechaFin = clone $fechaInicio;
        $fechaFin->endOfWeek();

        // Tareas de la semana agrupadas por día
        $tareasSemana = Tarea::with(['cliente', 'seguimiento.cotizacion', 'cotizacion'])
            ->deUsuario($usuario->id)
            ->entreFechas($fechaInicio, $fechaFin)
            ->orderBy('fecha_tarea')
            ->orderBy('hora_inicio')
            ->get()
            ->groupBy(function($tarea) {
                return $tarea->fecha_tarea->format('Y-m-d');
            });

        // Estadísticas de la semana
        $estadisticasSemana = [
            'total_tareas' => 0,
            'completadas' => 0,
            'carga_trabajo_total' => 0,
            'dias_con_tareas' => 0,
            'promedio_tareas_dia' => 0
        ];

        foreach ($tareasSemana as $dia => $tareasDia) {
            $estadisticasSemana['total_tareas'] += $tareasDia->count();
            $estadisticasSemana['completadas'] += $tareasDia->where('estado', 'completada')->count();
            $estadisticasSemana['carga_trabajo_total'] += $tareasDia->sum('duracion_estimada');
        }

        $estadisticasSemana['dias_con_tareas'] = count($tareasSemana);
        $estadisticasSemana['promedio_tareas_dia'] = $estadisticasSemana['dias_con_tareas'] > 0 ? 
            round($estadisticasSemana['total_tareas'] / $estadisticasSemana['dias_con_tareas'], 1) : 0;

        // Generar estructura de días para el calendario
        $diasSemana = [];
        for ($i = 0; $i < 7; $i++) {
            $fecha = clone $fechaInicio;
            $fecha->addDays($i);
            $fechaStr = $fecha->format('Y-m-d');
            
            $diasSemana[] = [
                'fecha' => $fecha,
                'fecha_str' => $fechaStr,
                'es_hoy' => $fecha->isToday(),
                'es_fin_semana' => $fecha->isWeekend(),
                'tareas' => $tareasSemana[$fechaStr] ?? collect(),
                'carga_trabajo' => ($tareasSemana[$fechaStr] ?? collect())->sum('duracion_estimada')
            ];
        }

        // Capacidad de trabajo recomendada por día (en minutos)
        $capacidadDiaria = 480; // 8 horas

        return view('agenda.mi-semana', compact(
            'diasSemana', 
            'estadisticasSemana', 
            'fechaInicio', 
            'fechaFin', 
            'capacidadDiaria',
            'semana'
        ));
    }

    /**
     * Crear nueva tarea
     * Soporte para creación rápida y detallada
     */
    public function crear(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'tipo' => 'required|in:' . implode(',', Tarea::TIPOS),
            'fecha_tarea' => 'required|date|after_or_equal:today',
            'hora_inicio' => 'nullable|date_format:H:i',
            'hora_fin' => 'nullable|date_format:H:i|after:hora_inicio',
            'duracion_estimada' => 'nullable|integer|min:5|max:600',
            'prioridad' => 'required|in:' . implode(',', Tarea::PRIORIDADES),
            'cliente_id' => 'nullable|exists:clientes,id',
            'seguimiento_id' => 'nullable|exists:seguimientos,id',
            'cotizacion_id' => 'nullable|exists:cotizaciones,id',
            'tiene_recordatorio' => 'boolean',
            'recordatorio_en' => 'nullable|date|after:now',
            'tipo_recordatorio' => 'nullable|in:' . implode(',', Tarea::TIPOS_RECORDATORIO),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Determinar duración si no se especifica
            $duracionEstimada = $request->duracion_estimada;
            if (!$duracionEstimada && $request->hora_inicio && $request->hora_fin) {
                $inicio = Carbon::createFromFormat('H:i', $request->hora_inicio);
                $fin = Carbon::createFromFormat('H:i', $request->hora_fin);
                $duracionEstimada = $fin->diffInMinutes($inicio);
            }

            // Crear tarea
            $tarea = Tarea::create([
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'usuario_id' => Auth::id(),
                'tipo' => $request->tipo,
                'origen' => 'manual',
                'cliente_id' => $request->cliente_id,
                'seguimiento_id' => $request->seguimiento_id,
                'cotizacion_id' => $request->cotizacion_id,
                'fecha_tarea' => $request->fecha_tarea,
                'hora_inicio' => $request->hora_inicio,
                'hora_fin' => $request->hora_fin,
                'duracion_estimada' => $duracionEstimada,
                'estado' => 'pendiente',
                'prioridad' => $request->prioridad,
                'tiene_recordatorio' => $request->boolean('tiene_recordatorio'),
                'recordatorio_en' => $request->recordatorio_en,
                'tipo_recordatorio' => $request->tipo_recordatorio,
                'es_distribuida_automaticamente' => false,
                'intentos_completar' => 0
            ]);

            // Actualizar seguimiento relacionado si existe
            if ($request->seguimiento_id) {
                $seguimiento = Seguimiento::find($request->seguimiento_id);
                if ($seguimiento && $seguimiento->estado === 'pendiente') {
                    $seguimiento->update(['estado' => 'en_proceso']);
                }
            }

            DB::commit();

            // Cargar relaciones para respuesta
            $tarea->load(['cliente', 'seguimiento', 'cotizacion']);

            Log::info("Tarea creada: {$tarea->titulo} para " . Auth::user()->name);

            return response()->json([
                'success' => true,
                'message' => 'Tarea creada exitosamente.',
                'tarea' => $tarea
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear tarea: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno al crear la tarea.'
            ], 500);
        }
    }

    /**
     * Actualizar tarea existente
     * Soporte para edición completa y cambios de estado
     */
    public function actualizar(Request $request, Tarea $tarea)
    {
        // Verificar permisos
        if ($tarea->usuario_id !== Auth::id() && !Auth::user()->hasRole(['jefe_ventas', 'administrador'])) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para editar esta tarea.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'titulo' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'fecha_tarea' => 'sometimes|required|date',
            'hora_inicio' => 'nullable|date_format:H:i',
            'hora_fin' => 'nullable|date_format:H:i|after:hora_inicio',
            'duracion_estimada' => 'nullable|integer|min:5|max:600',
            'estado' => 'sometimes|required|in:' . implode(',', Tarea::ESTADOS),
            'prioridad' => 'sometimes|required|in:' . implode(',', Tarea::PRIORIDADES),
            'notas' => 'nullable|string|max:1000',
            'resultado' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $datosActualizacion = $request->only([
                'titulo', 'descripcion', 'fecha_tarea', 'hora_inicio', 'hora_fin', 
                'duracion_estimada', 'estado', 'prioridad', 'notas', 'resultado'
            ]);

            // Manejar cambio de estado especial
            if ($request->has('estado')) {
                switch ($request->estado) {
                    case 'completada':
                        $datosActualizacion['completada_en'] = Carbon::now();
                        break;
                    case 'en_progreso':
                        if ($tarea->estado === 'pendiente') {
                            $datosActualizacion['hora_inicio'] = $datosActualizacion['hora_inicio'] ?? Carbon::now()->format('H:i');
                        }
                        break;
                }
            }

            $tarea->update($datosActualizacion);

            // Actualizar seguimiento relacionado si es necesario
            if ($request->estado === 'completada' && $tarea->seguimiento_id) {
                $seguimiento = $tarea->seguimiento;
                if ($seguimiento) {
                    $seguimiento->update([
                        'estado' => 'completado',
                        'ultima_gestion' => Carbon::today(),
                        'resultado_ultima_gestion' => $request->resultado
                    ]);
                }
            }

            DB::commit();

            // Recargar relaciones
            $tarea->load(['cliente', 'seguimiento', 'cotizacion']);

            Log::info("Tarea actualizada: {$tarea->titulo} (ID: {$tarea->id})");

            return response()->json([
                'success' => true,
                'message' => 'Tarea actualizada exitosamente.',
                'tarea' => $tarea
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar tarea {$tarea->id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno al actualizar la tarea.'
            ], 500);
        }
    }

    /**
     * Completar tarea con resultado
     * Método específico para marcar tareas como completadas
     */
    public function completarTarea(Request $request, Tarea $tarea)
    {
        // Verificar permisos
        if ($tarea->usuario_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para completar esta tarea.'
            ], 403);
        }

        if ($tarea->estado === 'completada') {
            return response()->json([
                'success' => false,
                'message' => 'La tarea ya está completada.'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'resultado' => 'required|string|max:1000',
            'hora_fin' => 'nullable|date_format:H:i',
            'notas_adicionales' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Completar tarea
            $tarea->completar($request->resultado, Auth::id());

            // Actualizar hora de fin si se proporciona
            if ($request->hora_fin) {
                $tarea->update(['hora_fin' => $request->hora_fin]);
            }

            // Agregar notas adicionales
            if ($request->notas_adicionales) {
                $notasActuales = $tarea->notas ?? '';
                $tarea->update([
                    'notas' => $notasActuales . "\n[COMPLETADA - " . Carbon::now()->format('d/m/Y H:i') . "] " . $request->notas_adicionales
                ]);
            }

            DB::commit();

            Log::info("Tarea completada: {$tarea->titulo} por " . Auth::user()->name);

            return response()->json([
                'success' => true,
                'message' => 'Tarea completada exitosamente.',
                'tarea' => $tarea->fresh(['cliente', 'seguimiento', 'cotizacion'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al completar tarea {$tarea->id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno al completar la tarea.'
            ], 500);
        }
    }

    /**
     * Posponer tarea a nueva fecha
     * Permite reagendar tareas con motivo
     */
    public function posponerTarea(Request $request, Tarea $tarea)
    {
        // Verificar permisos
        if ($tarea->usuario_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para posponer esta tarea.'
            ], 403);
        }

        if (in_array($tarea->estado, ['completada', 'cancelada'])) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede posponer una tarea completada o cancelada.'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'nueva_fecha' => 'required|date|after:today',
            'motivo' => 'required|string|max:255',
            'nueva_hora_inicio' => 'nullable|date_format:H:i'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Posponer tarea
            $tarea->posponer($request->nueva_fecha, $request->motivo);

            // Actualizar hora de inicio si se proporciona
            if ($request->nueva_hora_inicio) {
                $tarea->update(['hora_inicio' => $request->nueva_hora_inicio]);
            }

            Log::info("Tarea pospuesta: {$tarea->titulo} para {$request->nueva_fecha}");

            return response()->json([
                'success' => true,
                'message' => 'Tarea pospuesta exitosamente.',
                'tarea' => $tarea->fresh(['cliente', 'seguimiento', 'cotizacion'])
            ]);

        } catch (\Exception $e) {
            Log::error("Error al posponer tarea {$tarea->id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno al posponer la tarea.'
            ], 500);
        }
    }

    /**
     * Distribución automática de seguimientos vencidos
     * Sistema inteligente de distribución de carga de trabajo
     */
    public function distribuirSeguimientosVencidos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendedor_id' => 'nullable|exists:users,id',
            'max_tareas_por_dia' => 'required|integer|min:1|max:20',
            'dias_distribucion' => 'required|integer|min:1|max:30',
            'priorizar_por' => 'required|in:fecha_vencimiento,valor_cotizacion,tipo_cliente',
            'incluir_fin_semana' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $vendedorId = $request->vendedor_id ?? Auth::id();
            $maxTareasPorDia = $request->max_tareas_por_dia;
            $diasDistribucion = $request->dias_distribucion;
            $priorizarPor = $request->priorizar_por;
            $incluirFinSemana = $request->boolean('incluir_fin_semana');

            // Obtener seguimientos vencidos del vendedor
            $seguimientosVencidos = Seguimiento::with(['cliente', 'cotizacion'])
                ->where('vendedor_id', $vendedorId)
                ->where('proxima_gestion', '<', Carbon::today())
                ->whereIn('estado', ['pendiente', 'en_proceso'])
                ->get();

            if ($seguimientosVencidos->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No hay seguimientos vencidos para distribuir.',
                    'tareas_creadas' => 0
                ]);
            }

            // Priorizar seguimientos según criterio
            $seguimientosOrdenados = $this->priorizarSeguimientos($seguimientosVencidos, $priorizarPor);

            // Generar fechas de distribución
            $fechasDistribucion = $this->generarFechasDistribucion($diasDistribucion, $incluirFinSemana);

            // Distribuir seguimientos
            $tareasCreadas = 0;
            $fechaIndex = 0;
            $tareasEnFechaActual = 0;

            foreach ($seguimientosOrdenados as $seguimiento) {
                // Cambiar a siguiente fecha si se alcanzó el máximo
                if ($tareasEnFechaActual >= $maxTareasPorDia) {
                    $fechaIndex++;
                    $tareasEnFechaActual = 0;
                }

                // Si no hay más fechas disponibles, parar
                if ($fechaIndex >= count($fechasDistribucion)) {
                    break;
                }

                $fechaTarea = $fechasDistribucion[$fechaIndex];

                // Crear tarea automática
                $tarea = Tarea::create([
                    'titulo' => "Seguimiento: {$seguimiento->cliente->nombre_institucion}",
                    'descripcion' => $this->generarDescripcionSeguimiento($seguimiento),
                    'usuario_id' => $vendedorId,
                    'tipo' => 'seguimiento',
                    'origen' => 'distribucion_masiva',
                    'seguimiento_id' => $seguimiento->id,
                    'cotizacion_id' => $seguimiento->cotizacion_id,
                    'cliente_id' => $seguimiento->cliente_id,
                    'fecha_tarea' => $fechaTarea,
                    'duracion_estimada' => 30, // 30 minutos por defecto
                    'estado' => 'pendiente',
                    'prioridad' => $this->determinarPrioridadTarea($seguimiento),
                    'es_distribuida_automaticamente' => true,
                    'metadata_distribucion' => json_encode([
                        'algoritmo' => 'distribucion_masiva',
                        'criterio_priorizacion' => $priorizarPor,
                        'fecha_distribucion' => now(),
                        'max_tareas_dia' => $maxTareasPorDia
                    ])
                ]);

                // Actualizar seguimiento
                $seguimiento->update([
                    'estado' => 'en_proceso',
                    'proxima_gestion' => $fechaTarea
                ]);

                $tareasCreadas++;
                $tareasEnFechaActual++;
            }

            DB::commit();

            Log::info("Distribución masiva completada: {$tareasCreadas} tareas creadas para usuario {$vendedorId}");

            return response()->json([
                'success' => true,
                'message' => "Distribución completada exitosamente. Se crearon {$tareasCreadas} tareas.",
                'tareas_creadas' => $tareasCreadas,
                'seguimientos_procesados' => $seguimientosVencidos->count(),
                'fechas_utilizadas' => min($fechaIndex + 1, count($fechasDistribucion))
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en distribución masiva: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno en la distribución automática.'
            ], 500);
        }
    }

    /**
     * API: Obtener tareas para calendario/agenda
     * Endpoint optimizado para componentes de frontend
     */
    public function obtenerTareas(Request $request)
    {
        $usuario = Auth::user();
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');
        $estado = $request->get('estado');
        $tipo = $request->get('tipo');

        try {
            $query = Tarea::with(['cliente:id,nombre_institucion', 'seguimiento:id,estado', 'cotizacion:id,codigo,nombre_cotizacion'])
                ->deUsuario($usuario->id);

            if ($fechaInicio && $fechaFin) {
                $query->entreFechas($fechaInicio, $fechaFin);
            } elseif ($fechaInicio) {
                $query->whereDate('fecha_tarea', '>=', $fechaInicio);
            } elseif ($fechaFin) {
                $query->whereDate('fecha_tarea', '<=', $fechaFin);
            }

            if ($estado) {
                $query->where('estado', $estado);
            }

            if ($tipo) {
                $query->where('tipo', $tipo);
            }

            $tareas = $query->orderBy('fecha_tarea')
                          ->orderBy('hora_inicio')
                          ->get()
                          ->map(function($tarea) {
                              return [
                                  'id' => $tarea->id,
                                  'titulo' => $tarea->titulo,
                                  'descripcion' => $tarea->descripcion,
                                  'fecha_tarea' => $tarea->fecha_tarea->format('Y-m-d'),
                                  'hora_inicio' => $tarea->hora_inicio,
                                  'hora_fin' => $tarea->hora_fin,
                                  'duracion_estimada' => $tarea->duracion_estimada,
                                  'duracion_formateada' => $tarea->duracion_formateada,
                                  'estado' => $tarea->estado,
                                  'estado_humano' => $tarea->estado_humano,
                                  'prioridad' => $tarea->prioridad,
                                  'prioridad_humana' => $tarea->prioridad_humana,
                                  'tipo' => $tarea->tipo,
                                  'tipo_humano' => $tarea->tipo_humano,
                                  'color_estado' => $tarea->color_estado,
                                  'color_prioridad' => $tarea->color_prioridad,
                                  'es_vencida' => $tarea->es_vencida,
                                  'es_hoy' => $tarea->es_hoy,
                                  'dias_restantes' => $tarea->dias_restantes,
                                  'cliente' => $tarea->cliente ? [
                                      'id' => $tarea->cliente->id,
                                      'nombre' => $tarea->cliente->nombre_institucion
                                  ] : null,
                                  'cotizacion' => $tarea->cotizacion ? [
                                      'id' => $tarea->cotizacion->id,
                                      'codigo' => $tarea->cotizacion->codigo,
                                      'nombre' => $tarea->cotizacion->nombre_cotizacion
                                  ] : null,
                                  'es_automatica' => $tarea->es_distribuida_automaticamente,
                                  'puede_editar' => $tarea->puedeSerEditada(),
                                  'puede_eliminar' => $tarea->puedeSerEliminada(),
                                  'resumen' => $tarea->resumen
                              ];
                          });

            return response()->json([
                'success' => true,
                'tareas' => $tareas,
                'total' => $tareas->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener tareas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las tareas.'
            ], 500);
        }
    }

    /**
     * Métodos auxiliares privados
     */

    private function obtenerCargaSemana($usuarioId, $fecha)
    {
        $inicioSemana = $fecha->copy()->startOfWeek();
        $finSemana = $fecha->copy()->endOfWeek();

        return Tarea::deUsuario($usuarioId)
            ->entreFechas($inicioSemana, $finSemana)
            ->selectRaw('
                DATE(fecha_tarea) as fecha,
                COUNT(*) as total_tareas,
                SUM(duracion_estimada) as tiempo_total,
                SUM(CASE WHEN estado = "completada" THEN 1 ELSE 0 END) as completadas
            ')
            ->groupBy('fecha')
            ->get()
            ->keyBy('fecha');
    }

    private function obtenerProximasTareas($usuarioId, $fecha)
    {
        $proximoDiaLaboral = $fecha->copy()->addWeekday();
        
        return Tarea::with(['cliente', 'cotizacion'])
            ->deUsuario($usuarioId)
            ->whereDate('fecha_tarea', $proximoDiaLaboral)
            ->whereIn('estado', ['pendiente', 'en_progreso'])
            ->orderBy('prioridad', 'desc')
            ->orderBy('hora_inicio')
            ->limit(5)
            ->get();
    }

    private function generarAlertas($usuarioId, $fecha)
    {
        $alertas = [];

        // Alertas de sobrecarga
        $cargaHoy = Tarea::deUsuario($usuarioId)
            ->hoy()
            ->sum('duracion_estimada');

        if ($cargaHoy > 480) { // Más de 8 horas
            $alertas[] = [
                'tipo' => 'warning',
                'titulo' => 'Sobrecarga de trabajo',
                'mensaje' => 'Tienes más de 8 horas de tareas programadas para hoy.',
                'accion' => 'redistribuir'
            ];
        }

        // Alertas de tareas vencidas
        $tareasVencidas = Tarea::deUsuario($usuarioId)
            ->vencidas()
            ->count();

        if ($tareasVencidas > 0) {
            $alertas[] = [
                'tipo' => 'error',
                'titulo' => 'Tareas vencidas',
                'mensaje' => "Tienes {$tareasVencidas} tarea(s) vencida(s).",
                'accion' => 'revisar_vencidas'
            ];
        }

        // Alertas de seguimientos sin gestionar
        $seguimientosVencidos = Seguimiento::where('vendedor_id', $usuarioId)
            ->where('proxima_gestion', '<', Carbon::today())
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->count();

        if ($seguimientosVencidos > 5) {
            $alertas[] = [
                'tipo' => 'info',
                'titulo' => 'Seguimientos pendientes',
                'mensaje' => "Tienes {$seguimientosVencidos} seguimientos vencidos que pueden convertirse en tareas.",
                'accion' => 'distribuir_seguimientos'
            ];
        }

        return $alertas;
    }

    private function priorizarSeguimientos($seguimientos, $criterio)
    {
        switch ($criterio) {
            case 'fecha_vencimiento':
                return $seguimientos->sortBy('proxima_gestion');
            
            case 'valor_cotizacion':
                return $seguimientos->sortByDesc(function($seg) {
                    return $seg->cotizacion ? $seg->cotizacion->total_con_iva : 0;
                });
            
            case 'tipo_cliente':
                return $seguimientos->sortBy(function($seg) {
                    $orden = ['Cliente Público' => 1, 'Revendedor' => 2, 'Cliente Privado' => 3];
                    return $orden[$seg->cliente->tipo_cliente] ?? 4;
                });
            
            default:
                return $seguimientos;
        }
    }

    private function generarFechasDistribucion($dias, $incluirFinSemana)
    {
        $fechas = [];
        $fecha = Carbon::tomorrow();

        while (count($fechas) < $dias) {
            if ($incluirFinSemana || !$fecha->isWeekend()) {
                $fechas[] = $fecha->copy();
            }
            $fecha->addDay();
        }

        return $fechas;
    }

    private function generarDescripcionSeguimiento($seguimiento)
    {
        $descripcion = "Realizar seguimiento a {$seguimiento->cliente->nombre_institucion}.";
        
        if ($seguimiento->cotizacion) {
            $descripcion .= " Cotización: {$seguimiento->cotizacion->nombre_cotizacion}.";
        }

        if ($seguimiento->ultima_gestion) {
            $descripcion .= " Última gestión: {$seguimiento->ultima_gestion}.";
        }

        if ($seguimiento->notas) {
            $descripcion .= " Notas: " . Str::limit($seguimiento->notas, 100);
        }

        return $descripcion;
    }

    private function determinarPrioridadTarea($seguimiento)
    {
        $diasVencidos = Carbon::now()->diffInDays($seguimiento->proxima_gestion);
        
        if ($diasVencidos > 7) return 'urgente';
        if ($diasVencidos > 3) return 'alta';
        if ($diasVencidos > 1) return 'media';
        return 'baja';
    }
}