<?php

namespace App\Http\Controllers;

use App\Models\Seguimiento;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Cotizacion;
use App\Imports\SegumientosImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SeguimientoController extends Controller
{
    /**
 * Display a listing of the resource.
 * VISTA PRINCIPAL - TIPO EXCEL EDITABLE
 */
public function index()
{
    try {
        // Obtener vendedores para el componente Vue
        $vendedores = User::select('id', 'name', 'email')
                         ->orderBy('name')
                         ->get();
        
        \Log::info('🔍 Vendedores cargados para seguimiento:', [
            'count' => $vendedores->count(),
            'vendedores' => $vendedores->pluck('name', 'id')->toArray()
        ]);

        return view('seguimiento.index', compact('vendedores'));
        
    } catch (\Exception $e) {
        \Log::error('❌ Error al cargar página de seguimiento:', [
            'error' => $e->getMessage()
        ]);
        
        // En caso de error, pasar array vacío para evitar crashes
        $vendedores = [];
        return view('seguimiento.index', compact('vendedores'));
    }
}

    /**
 * AJAX: Obtener seguimientos para la tabla (con filtros y búsqueda)
 * CRÍTICO: Este método alimenta la vista tipo Excel
 */
public function getSeguimientos(Request $request)
{
    try {
        $query = Seguimiento::with(['cliente', 'cotizacion', 'vendedor']);

        // **FILTROS SEGÚN ROL DEL USUARIO**
        /*
        TEMPORAL: Comentado para desarrollo
        $user = Auth::user();
        
        if ($user->esVendedor() && !$user->esJefe()) {
            // Vendedores solo ven sus seguimientos
            $query->paraVendedor($user->id);
        } elseif ($user->esJefe()) {
            // Jefes ven su equipo
            $vendedorFiltro = $request->get('vendedor');
            if ($vendedorFiltro && $vendedorFiltro !== 'todos') {
                $query->paraVendedor($vendedorFiltro);
            } else {
                // Ver todo el equipo
                $equipoIds = $user->vendedoresSupervision()->pluck('users.id')->toArray();
                if (!empty($equipoIds)) {
                    $query->paraJefeVentas($equipoIds);
                }
            }
        }
        */

        // **FILTROS ESPECÍFICOS DE LA VISTA**
        
        // Filtro por clasificación (CRÍTICO para resolver crisis)
        $clasificacion = $request->get('clasificacion');
        switch ($clasificacion) {
            case 'atrasados':
                $query->where('proxima_gestion', '<', now()->toDateString());
                break;
            case 'proximos':
                 $query->whereBetween('proxima_gestion', [
                    now()->toDateString(), 
                    now()->addDays(7)->toDateString()
                ]);
                break;
            case 'hoy':
                 $query->whereDate('proxima_gestion', now()->toDateString());
                break;
            case 'completados_hoy':
                 $query->where('estado', 'completado')
                      ->whereDate('updated_at', now()->toDateString());
                break;
            // Por defecto no filtramos (mostrar todos)
        } 

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->estado($request->get('estado'));
        }

        // Filtro por prioridad
        if ($request->filled('prioridad')) {
             $query->where('prioridad', $request->get('prioridad'));
        }

        // **BÚSQUEDA RÁPIDA**
        if ($request->filled('busqueda')) {
            $busqueda = $request->get('busqueda');
            $query->where(function($q) use ($busqueda) {
                $q->whereHas('cliente', function($clienteQuery) use ($busqueda) {
                    $clienteQuery->where('nombre_institucion', 'like', "%{$busqueda}%")
                                ->orWhere('rut', 'like', "%{$busqueda}%");
                })
                ->orWhereHas('cotizacion', function($cotizacionQuery) use ($busqueda) {
                    $cotizacionQuery->where('codigo', 'like', "%{$busqueda}%");
                })
                ->orWhere('notas', 'like', "%{$busqueda}%");
            });
        }

        // **ORDENAMIENTO**
        $sortField = $request->get('sort', 'proxima_gestion');
        $sortDirection = $request->get('direction', 'asc');
        
        // Ordenamientos especiales
        if ($sortField === 'cliente') {
            $query->join('clientes', 'seguimientos.cliente_id', '=', 'clientes.id')
                  ->orderBy('clientes.nombre_institucion', $sortDirection)
                  ->select('seguimientos.*');
        } elseif ($sortField === 'vendedor') {
            $query->join('users', 'seguimientos.vendedor_id', '=', 'users.id')
                  ->orderBy('users.name', $sortDirection)
                  ->select('seguimientos.*');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        // **PAGINACIÓN**
        $perPage = $request->get('per_page', 50); // Vista tipo Excel necesita más registros
        $seguimientos = $query->paginate($perPage);

        // **TRANSFORMAR DATOS PARA LA VISTA**
        $data = $seguimientos->through(function ($seguimiento) {
            return [
                'id' => $seguimiento->id,
                'cliente' => $seguimiento->cliente->nombre_institucion ?? 'Sin cliente',
                'rut_cliente' => $seguimiento->cliente->rut ?? '',
                'cotizacion' => $seguimiento->cotizacion->codigo ?? 'Sin cotización',
                'vendedor' => $seguimiento->vendedor->name ?? 'Sin vendedor',
                'estado' => $seguimiento->estado,
                'prioridad' => $seguimiento->prioridad,
                'ultima_gestion' => $seguimiento->ultima_gestion ? 
                    \Carbon\Carbon::parse($seguimiento->ultima_gestion)->format('d/m/Y') : null,
                'proxima_gestion' => $seguimiento->proxima_gestion ? 
                    \Carbon\Carbon::parse($seguimiento->proxima_gestion)->format('Y-m-d') : null, // 🔧 FORMATO CORRECTO
                'notas' => $seguimiento->notas,
                'dias_atraso' => $seguimiento->proxima_gestion ? 
                    max(0, now()->diffInDays(\Carbon\Carbon::parse($seguimiento->proxima_gestion), false)) : 0,
                'color_clasificacion' => $this->getColorClasificacion($seguimiento)
            ];
        });

        // **ESTADÍSTICAS PARA CONTADORES**
        // TEMPORAL: Comentado para desarrollo
        // $estadisticas = $this->calcularEstadisticas($user);
        // **ESTADÍSTICAS PARA CONTADORES**
        $estadisticas = [
            'total' => $seguimientos->total(),
            'atrasados' => Seguimiento::where('proxima_gestion', '<', now()->toDateString())->count(),
            'proximos_7_dias' => Seguimiento::whereBetween('proxima_gestion', [
                now()->toDateString(), 
                now()->addDays(7)->toDateString()
            ])->count(),
            'hoy' => Seguimiento::whereDate('proxima_gestion', now()->toDateString())->count(),
            'completados_hoy' => Seguimiento::where('estado', 'completado')
                                          ->whereDate('updated_at', now()->toDateString())->count(),
            'total_activos' => Seguimiento::whereIn('estado', ['pendiente', 'en_proceso'])->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'estadisticas' => $estadisticas,
            'pagination' => [
                'current_page' => $seguimientos->currentPage(),
                'last_page' => $seguimientos->lastPage(),
                'per_page' => $seguimientos->perPage(),
                'total' => $seguimientos->total()
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Error al cargar seguimientos:', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error al cargar seguimientos: ' . $e->getMessage()
        ], 500);
    }
}
/**
 * Determinar color de clasificación para el seguimiento
 */
private function getColorClasificacion($seguimiento)
{
    if (!$seguimiento->proxima_gestion) {
        return 'neutral';
    }
    
    $dias = now()->diffInDays(\Carbon\Carbon::parse($seguimiento->proxima_gestion), false);
    
    if ($dias < 0) {
        return 'danger'; // Atrasado
    } elseif ($dias <= 3) {
        return 'warning'; // Próximo
    } else {
        return 'success'; // Normal
    }
}
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'cliente_id' => 'required|exists:clientes,id',
                'cotizacion_id' => 'nullable|exists:cotizaciones,id',
                'vendedor_id' => 'required|exists:users,id',
                'estado' => 'required|in:pendiente,en_proceso,completado,vencido,reprogramado',
                'prioridad' => 'required|in:baja,media,alta,urgente',
                'proxima_gestion' => 'required|date',
                'notas' => 'nullable|string|max:1000'
            ]);

            // Verificar permisos
            $user = Auth::user();
            if ($user->esVendedor() && !$user->esJefe()) {
                $validated['vendedor_id'] = $user->id; // Forzar asignación propia
            }

            $seguimiento = Seguimiento::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Seguimiento creado correctamente',
                'data' => $seguimiento->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear seguimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Seguimiento $seguimiento)
{
    try {
        // ✅ 1. Verificar permisos (comentado temporalmente para debug)
        /*
        $user = Auth::user();
        if ($user->esVendedor() && !$user->esJefe() && $seguimiento->vendedor_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar este seguimiento'
            ], 403);
        }
        */

        // ✅ 2. Validación simplificada
        $validated = $request->validate([
            'estado' => 'sometimes|in:pendiente,en_proceso,completado,vencido,reprogramado',
            'prioridad' => 'sometimes|in:baja,media,alta,urgente',
            'proxima_gestion' => 'sometimes|date',
            'notas' => 'nullable|string|max:1000',
            'resultado_ultima_gestion' => 'nullable|string|max:1000'
        ]);

        // ✅ 3. Si se marca como completado, actualizar fecha
        if (isset($validated['estado']) && $validated['estado'] === 'completado') {
            $validated['ultima_gestion'] = now()->toDateString();
        }

        // ✅ 4. Actualizar
        $seguimiento->update($validated);

        // ✅ 5. Respuesta limpia
        return response()->json([
            'success' => true,
            'message' => 'Seguimiento actualizado correctamente',
            'data' => $seguimiento->fresh()->toTableArray(),
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        // ✅ 6. Debug temporal - muestra el error exacto
        \Log::error('Error en update seguimiento:', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'data' => $request->all()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar seguimiento: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Seguimiento $seguimiento)
    {
        try {
            // Verificar permisos
            $user = Auth::user();
            if ($user->esVendedor() && !$user->esJefe() && $seguimiento->vendedor_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar este seguimiento'
                ], 403);
            }

            $seguimiento->delete();

            return response()->json([
                'success' => true,
                'message' => 'Seguimiento eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar seguimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
 * ACTUALIZACIÓN MASIVA - FUNCIONALIDAD CRÍTICA
 * Permite actualizar múltiples seguimientos a la vez
 */
public function updateMasivo(Request $request)
{
    try {
        \Log::info('🔧 Actualización masiva iniciada:', $request->all());
        
        $validated = $request->validate([
            'seguimiento_ids' => 'required|array|min:1',
            'seguimiento_ids.*' => 'integer|exists:seguimientos,id',
            'datos' => 'required|array',
            'datos.estado' => 'sometimes|in:pendiente,en_proceso,completado,vencido,reprogramado',
            'datos.prioridad' => 'sometimes|in:baja,media,alta,urgente',
            'datos.proxima_gestion' => 'sometimes|date_format:Y-m-d',
            'datos.vendedor_id' => 'sometimes|integer|exists:users,id',
            'datos.notas' => 'nullable|string|max:1000'
        ]);

        $seguimientoIds = $validated['seguimiento_ids'];
        $datos = $validated['datos'];
        
        \Log::info('📝 Datos validados:', [
            'ids' => $seguimientoIds,
            'datos' => $datos
        ]);

        // Filtrar solo datos con valores
        $datosParaActualizar = [];
        foreach ($datos as $campo => $valor) {
            if (!empty($valor) && $valor !== '') {
                $datosParaActualizar[$campo] = $valor;
            }
        }
        
        if (empty($datosParaActualizar)) {
            return response()->json([
                'success' => false,
                'message' => 'No hay campos para actualizar'
            ], 422);
        }

        // Realizar actualización
        $actualizados = Seguimiento::whereIn('id', $seguimientoIds)
                                 ->update($datosParaActualizar);

        \Log::info('✅ Actualización completada:', [
            'actualizados' => $actualizados
        ]);

        return response()->json([
            'success' => true,
            'message' => "Se actualizaron {$actualizados} seguimientos correctamente"
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('❌ Error de validación en actualización masiva:', [
            'errors' => $e->errors()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        \Log::error('❌ Error en actualización masiva:', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error en actualización masiva: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * IMPORTACIÓN DE EXCEL - FUNCIONALIDAD CRÍTICA
     * Permite importar seguimientos desde archivo Excel
     */
    public function importar(Request $request)
    {
        try {
            $request->validate([
                'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240' // Max 10MB
            ]);

            $archivo = $request->file('archivo');
            
            DB::beginTransaction();
            
            $import = new SegumientosImport();
            Excel::import($import, $archivo);
            
            DB::commit();

            $resultados = $import->getResultados();

            return response()->json([
                'success' => true,
                'message' => 'Archivo importado correctamente',
                'resultados' => $resultados
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al importar archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * BÚSQUEDA RÁPIDA DE CLIENTES PARA AUTOCOMPLETE
     */
    public function buscarClientes(Request $request)
    {
        try {
            $termino = $request->get('q', '');
            
            if (strlen($termino) < 2) {
                return response()->json([]);
            }

            $clientes = Cliente::busqueda($termino)
                ->limit(10)
                ->get()
                ->map(function ($cliente) {
                    return [
                        'id' => $cliente->id,
                        'text' => $cliente->nombre_institucion . ' (' . $cliente->rut_formateado . ')',
                        'nombre_institucion' => $cliente->nombre_institucion,
                        'rut' => $cliente->rut_formateado
                    ];
                });

            return response()->json($clientes);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * OBTENER VENDEDORES DISPONIBLES SEGÚN ROL
     */
   //* public function getVendedores(Request $request)
   // {
     //   try {
       //     $user = Auth::user();
      //      $query = User::activos()->vendedores();

            // Si es jefe, mostrar su equipo
        //    if ($user->esJefe()) {
          //      $equipoIds = $user->vendedoresSupervision()->pluck('users.id')->toArray();
            //    if (!empty($equipoIds)) {
              //      $query->whereIn('id', $equipoIds);
                //}
            //}
            // Si es vendedor simple, solo él mismo
           // elseif ($user->esVendedor()) {
           //     $query->where('id', $user->id);
         //   }

//            $vendedores = $query->get()->map(function ($vendedor) {
      //          return [
//                    'id' => $vendedor->id,
  //                  'name' => $vendedor->name,
    //                'iniciales' => $vendedor->iniciales
      //          ];
        //    });

        //    return response()->json($vendedores);

//        } catch (\Exception $e) {
         //   return response()->json([
         //       'success' => false,
         //       'message' => 'Error al obtener vendedores: ' . $e->getMessage()
         //   ], 500);
       // }
   // }
/**
 * * Obtener lista de vendedores para selectors
 */
public function getVendedores()
{
    try {
        $vendedores = User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        \Log::info('🔍 Vendedores obtenidos:', [
            'count' => $vendedores->count()
        ]);

        return response()->json($vendedores);

    } catch (\Exception $e) {
        \Log::error('❌ Error al obtener vendedores:', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'error' => 'Error al obtener vendedores: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * CALCULAR ESTADÍSTICAS PARA CONTADORES DE LA VISTA
     * CRÍTICO: Estos números aparecen en tiempo real en la interfaz
     */
    private function calcularEstadisticas($user)
    {
        $query = Seguimiento::query();

        // Aplicar filtros de rol
        if ($user->esVendedor() && !$user->esJefe()) {
            $query->paraVendedor($user->id);
        } elseif ($user->esJefe()) {
            $equipoIds = $user->vendedoresSupervision()->pluck('users.id')->toArray();
            if (!empty($equipoIds)) {
                $query->paraJefeVentas($equipoIds);
            }
        }

        return [
            'atrasados' => (clone $query)->atrasados()->count(),
            'hoy' => (clone $query)->hoy()->count(),
            'proximos_7_dias' => (clone $query)->proximos(7)->count(),
            'completados_hoy' => (clone $query)->completadosHoy()->count(),
            'total_activos' => (clone $query)->whereIn('estado', [
                Seguimiento::ESTADO_PENDIENTE,
                Seguimiento::ESTADO_EN_PROCESO
            ])->count()
        ];
    }

    /**
     * DISTRIBUCIÓN AUTOMÁTICA DE SEGUIMIENTOS VENCIDOS
     * ROADMAP FASE 4 - Para jefes de ventas
     */
    public function distribuirSeguimientosVencidos(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->esJefe()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los jefes pueden usar la distribución automática'
                ], 403);
            }

            $validated = $request->validate([
                'max_por_dia' => 'required|integer|min:1|max:20',
                'dias_distribucion' => 'required|integer|min:1|max:30'
            ]);

            $resultado = $user->configurarDistribucionAutomatica($validated);

            return response()->json([
                'success' => true,
                'message' => 'Distribución automática completada',
                'resultado' => $resultado
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en distribución automática: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * EXPORTAR SEGUIMIENTOS A EXCEL
     * Para respaldo y análisis externo
     */
    public function exportar(Request $request)
    {
        try {
            // TODO: Implementar exportación
            // return Excel::download(new SeguimientosExport($request->all()), 'seguimientos.xlsx');
            
            return response()->json([
                'success' => false,
                'message' => 'Funcionalidad de exportación en desarrollo'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar: ' . $e->getMessage()
            ], 500);
        }
    }
}