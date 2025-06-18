<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\User;
use App\Http\Requests\CotizacionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CotizacionController extends Controller
{
    /**
 * Display a listing of the resource.
 */
public function index(Request $request)
{
    // Si es petición AJAX, devolver JSON optimizado
    if ($request->ajax() || $request->expectsJson()) {
        return $this->getCotizacionesOptimizado($request);
    }

    // Vista normal - Página principal con componente Vue
    return view('cotizaciones.index');
}

/**
 * AJAX OPTIMIZADO: Obtener cotizaciones con filtros CORREGIDOS
 */
private function getCotizacionesOptimizado(Request $request)
{
    try {
        \Log::info('🔍 Cargando cotizaciones optimizado:', $request->all());
        
        // Cargar relaciones básicas
        $query = Cotizacion::with(['cliente:id,nombre_institucion', 'vendedor:id,name']);

        // **FILTRO 1: BÚSQUEDA GENERAL**
        if ($request->filled('busqueda') && !empty(trim($request->get('busqueda')))) {
            $busqueda = trim($request->get('busqueda'));
            \Log::info('🔍 Aplicando filtro de búsqueda:', ['busqueda' => $busqueda]);
            
            $query->where(function($q) use ($busqueda) {
                $q->where('codigo', 'like', "%{$busqueda}%")
                  ->orWhere('nombre_cotizacion', 'like', "%{$busqueda}%")
                  ->orWhere('nombre_institucion', 'like', "%{$busqueda}%");
            });
        }

        // **FILTRO 2: ESTADO**
        if ($request->filled('estado') && !empty(trim($request->get('estado')))) {
            $estado = trim($request->get('estado'));
            \Log::info('📊 Aplicando filtro de estado:', ['estado' => $estado]);
            $query->where('estado', $estado);
        }

        // **FILTRO 3: VENDEDOR**
        if ($request->filled('vendedor') && !empty(trim($request->get('vendedor')))) {
            $vendedor = trim($request->get('vendedor'));
            \Log::info('👤 Aplicando filtro de vendedor:', ['vendedor' => $vendedor]);
            $query->where('vendedor_id', $vendedor);
        }

        // **FILTRO 4: FECHA DESDE**
        if ($request->filled('fecha_desde') && !empty(trim($request->get('fecha_desde')))) {
            $fechaDesde = trim($request->get('fecha_desde'));
            \Log::info('📅 Aplicando filtro fecha desde:', ['fecha_desde' => $fechaDesde]);
            $query->whereDate('created_at', '>=', $fechaDesde);
        }

        // **FILTRO 5: FECHA HASTA**
        if ($request->filled('fecha_hasta') && !empty(trim($request->get('fecha_hasta')))) {
            $fechaHasta = trim($request->get('fecha_hasta'));
            \Log::info('📅 Aplicando filtro fecha hasta:', ['fecha_hasta' => $fechaHasta]);
            $query->whereDate('created_at', '<=', $fechaHasta);
        }

        // **FILTRO 6: FILTROS RÁPIDOS**
        if ($request->filled('rapido') && !empty(trim($request->get('rapido')))) {
            $filtroRapido = trim($request->get('rapido'));
            \Log::info('⚡ Aplicando filtro rápido:', ['filtro' => $filtroRapido]);
            
            switch ($filtroRapido) {
                case 'vencidas':
                    // Solo fechas válidas en formato YYYY-MM-DD
                    $query->where('validez_oferta', 'REGEXP', '^[0-9]{4}-[0-9]{2}-[0-9]{2}$')
                          ->whereDate('validez_oferta', '<', now());
                    break;
                    
                case 'mes':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
            }
        }

        // **FILTROS DE COLUMNAS ESTILO EXCEL** 🔥 NUEVO
        if ($request->filled('filtro_codigo')) {
            $codigosSeleccionados = $request->get('filtro_codigo');
            if (is_array($codigosSeleccionados) && !empty($codigosSeleccionados)) {
                \Log::info('🔍 Aplicando filtro de códigos:', ['codigos' => $codigosSeleccionados]);
                $query->where(function($q) use ($codigosSeleccionados) {
                    foreach ($codigosSeleccionados as $codigo) {
                        if ($codigo === 'Sin código') {
                            $q->orWhereNull('codigo')->orWhere('codigo', '');
                        } else {
                            $q->orWhere('codigo', $codigo);
                        }
                    }
                });
            }
        }

        if ($request->filled('filtro_nombre')) {
            $nombresSeleccionados = $request->get('filtro_nombre');
            if (is_array($nombresSeleccionados) && !empty($nombresSeleccionados)) {
                \Log::info('🔍 Aplicando filtro de nombres:', ['nombres' => count($nombresSeleccionados)]);
                $query->whereIn('nombre_cotizacion', $nombresSeleccionados);
            }
        }

        if ($request->filled('filtro_cliente')) {
            $clientesSeleccionados = $request->get('filtro_cliente');
            if (is_array($clientesSeleccionados) && !empty($clientesSeleccionados)) {
                \Log::info('🔍 Aplicando filtro de clientes:', ['clientes' => count($clientesSeleccionados)]);
                $query->whereIn('nombre_institucion', $clientesSeleccionados);
            }
        }

        if ($request->filled('filtro_estado')) {
            $estadosSeleccionados = $request->get('filtro_estado');
            if (is_array($estadosSeleccionados) && !empty($estadosSeleccionados)) {
                \Log::info('🔍 Aplicando filtro de estados:', ['estados' => $estadosSeleccionados]);
                $query->whereIn('estado', $estadosSeleccionados);
            }
        }

        if ($request->filled('filtro_vendedor')) {
            $vendedoresSeleccionados = $request->get('filtro_vendedor');
            if (is_array($vendedoresSeleccionados) && !empty($vendedoresSeleccionados)) {
                \Log::info('🔍 Aplicando filtro de vendedores:', ['vendedores' => count($vendedoresSeleccionados)]);
                // Necesitamos mapear nombres a IDs o usar nombres directamente
                $query->whereHas('vendedor', function($vendedorQuery) use ($vendedoresSeleccionados) {
                    $vendedorQuery->whereIn('name', $vendedoresSeleccionados);
                });
            }
        }

        if ($request->filled('filtro_validez')) {
            $validezSeleccionadas = $request->get('filtro_validez');
            if (is_array($validezSeleccionadas) && !empty($validezSeleccionadas)) {
                \Log::info('🔍 Aplicando filtro de validez:', ['validez' => count($validezSeleccionadas)]);
                $query->where(function($q) use ($validezSeleccionadas) {
                    foreach ($validezSeleccionadas as $validez) {
                        if ($validez === 'Sin fecha') {
                            $q->orWhereNull('validez_oferta')->orWhere('validez_oferta', '');
                        } else {
                            $q->orWhere('validez_oferta', $validez);
                        }
                    }
                });
            }
        }

        // **ORDENAMIENTO SIMPLIFICADO**
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        \Log::info('🔄 Aplicando ordenamiento:', [
            'campo' => $sortField, 
            'direccion' => $sortDirection
        ]);

        // Mapeo seguro de campos
        $camposPermitidos = [
            'created_at' => 'created_at',
            'codigo' => 'codigo', 
            'nombre' => 'nombre_cotizacion',
            'nombre_cotizacion' => 'nombre_cotizacion',
            'estado' => 'estado',
            'total' => 'total_con_iva',
            'total_con_iva' => 'total_con_iva',
            'validez' => 'validez_oferta',
            'validez_oferta' => 'validez_oferta',
            'cliente' => 'nombre_institucion'
        ];

        $campoReal = $camposPermitidos[$sortField] ?? 'created_at';
        $direccionReal = in_array(strtolower($sortDirection), ['asc', 'desc']) ? $sortDirection : 'desc';
        
        $query->orderBy($campoReal, $direccionReal);

        // **CONTAR ANTES DE PAGINAR**
        $totalFiltrado = $query->count();
        \Log::info('📊 Total después de filtros:', ['total' => $totalFiltrado]);

        // **PAGINACIÓN**
        $perPage = min($request->get('per_page', 50), 200);
        $cotizaciones = $query->paginate($perPage);

        // **ESTADÍSTICAS GLOBALES** (sin filtros)
        $estadisticas = [
            'total' => Cotizacion::count(),
            'ganadas' => Cotizacion::where('estado', 'Ganada')->count(),
            'pendientes' => Cotizacion::where('estado', 'Pendiente')->count(),
            'perdidas' => Cotizacion::where('estado', 'Perdida')->count(),
            'vencidas' => Cotizacion::where('validez_oferta', 'REGEXP', '^[0-9]{4}-[0-9]{2}-[0-9]{2}$')
                                   ->whereDate('validez_oferta', '<', now())
                                   ->count()
        ];

        // **FORMATEAR DATOS**
        $cotizacionesFormateadas = $cotizaciones->through(function ($cotizacion) {
            $validezFormateada = $this->formatearValidezOferta($cotizacion->validez_oferta);
            
            return [
                'id' => $cotizacion->id,
                'codigo' => $cotizacion->codigo ?: '',
                'nombre_cotizacion' => $cotizacion->nombre_cotizacion,
                'cliente' => $cotizacion->cliente->nombre_institucion ?? $cotizacion->nombre_institucion,
                'vendedor' => $cotizacion->vendedor->name ?? 'Sin asignar',
                'estado' => $cotizacion->estado,
                'total_formateado' => '$' . number_format($cotizacion->total_con_iva, 0, ',', '.'),
                'validez_oferta' => $validezFormateada['texto'],
                'vencida' => $validezFormateada['vencida'],
                'created_at' => $cotizacion->created_at->format('d/m/Y')
            ];
        });

        \Log::info('✅ Cotizaciones cargadas exitosamente:', [
            'total_db' => $estadisticas['total'],
            'total_filtrado' => $totalFiltrado,
            'pagina_actual' => $cotizacionesFormateadas->currentPage(),
            'por_pagina' => $cotizacionesFormateadas->perPage(),
            'mostrando' => $cotizacionesFormateadas->count()
        ]);

        return response()->json([
            'success' => true,
            'data' => $cotizacionesFormateadas,
            'estadisticas' => $estadisticas,
            'filtros_aplicados' => [
                'total_filtrado' => $totalFiltrado,
                'busqueda' => $request->get('busqueda'),
                'estado' => $request->get('estado'),
                'vendedor' => $request->get('vendedor'),
                'rapido' => $request->get('rapido')
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('❌ Error al cargar cotizaciones optimizado:', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'request' => $request->all()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error al cargar cotizaciones: ' . $e->getMessage()
        ], 500);
    }
}
/**
 * HELPER: Formatear validez_oferta manejando casos problemáticos
 */
private function formatearValidezOferta($validezOferta)
{
    // Si está vacío o es null
    if (empty($validezOferta)) {
        return [
            'texto' => 'Sin fecha',
            'vencida' => false
        ];
    }
    
    // Si contiene texto como "180 días corridos"
    if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $validezOferta)) {
        return [
            'texto' => $validezOferta, // Mostrar el texto original
            'vencida' => false
        ];
    }
    
    // Si es una fecha válida
    try {
        $fecha = \Carbon\Carbon::parse($validezOferta);
        return [
            'texto' => $fecha->format('d/m/Y'),
            'vencida' => $fecha->isPast()
        ];
    } catch (\Exception $e) {
        // Si falla el parsing, devolver el texto original
        return [
            'texto' => $validezOferta,
            'vencida' => false
        ];
    }
}
    /**
     * AJAX: Obtener cotizaciones con filtros y paginación
     */
    private function getCotizacionesAjax(Request $request)
    {
        try {
            $query = Cotizacion::with(['cliente', 'vendedor']);
            //$user = Auth::user();

            // **FILTROS SEGÚN ROL**
            //if ($user->esVendedor() && !$user->esJefe()) {
             //   $query->paraVendedor($user->id);
           // } elseif ($user->esJefe()) {
           //     $vendedorFiltro = $request->get('vendedor');
           //     if ($vendedorFiltro && $vendedorFiltro !== 'todos') {
           //         $query->paraVendedor($vendedorFiltro);
          //      } else {
           //         $equipoIds = $user->vendedoresSupervision()->pluck('users.id')->toArray();
          //          if (!empty($equipoIds)) {
           //             $query->paraJefeVentas($equipoIds);
           //         }
           //     }
         //   }

            // **FILTROS ESPECÍFICOS**
            if ($request->filled('estado')) {
                $query->estado($request->get('estado'));
            }

            if ($request->filled('fecha_desde')) {
                $query->where('created_at', '>=', $request->get('fecha_desde'));
            }

            if ($request->filled('fecha_hasta')) {
                $query->where('created_at', '<=', $request->get('fecha_hasta') . ' 23:59:59');
            }

            if ($request->filled('vencidas')) {
                $query->vencidas();
            }

            if ($request->filled('proximas_vencer')) {
                $query->proximasVencer(7);
            }

            // **BÚSQUEDA**
            if ($request->filled('busqueda')) {
                $query->busqueda($request->get('busqueda'));
            }

            // **ORDENAMIENTO**
            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            
            if ($sortField === 'cliente') {
                $query->join('clientes', 'cotizaciones.cliente_id', '=', 'clientes.id')
                      ->orderBy('clientes.nombre_institucion', $sortDirection)
                      ->select('cotizaciones.*');
            } elseif ($sortField === 'vendedor') {
                $query->join('users', 'cotizaciones.vendedor_id', '=', 'users.id')
                      ->orderBy('users.name', $sortDirection)
                      ->select('cotizaciones.*');
            } else {
                $query->orderBy($sortField, $sortDirection);
            }

            // **PAGINACIÓN**
            $perPage = $request->get('per_page', 20);
            $cotizaciones = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $cotizaciones->through(function ($cotizacion) {
                    return [
                        'id' => $cotizacion->id,
                        'codigo' => $cotizacion->codigo,
                        'nombre_cotizacion' => $cotizacion->nombre_cotizacion,
                        'cliente' => $cotizacion->cliente->nombre_institucion ?? $cotizacion->nombre_institucion,
                        'contacto' => $cotizacion->nombre_contacto,
                        'vendedor' => $cotizacion->vendedor->name ?? 'Sin asignar',
                        'total_formateado' => '$' . number_format($cotizacion->total_con_iva, 0, ',', '.'),
                        'estado' => $cotizacion->estado,
                        'validez_oferta' => is_string($cotizacion->validez_oferta) ? $cotizacion->validez_oferta : $cotizacion->validez_oferta->format('d/m/Y'),
                        'color_vencimiento' => method_exists($cotizacion, 'color_vencimiento') ? $cotizacion->color_vencimiento : 'normal',
                        'vencida' => method_exists($cotizacion, 'vencida') ? $cotizacion->vencida : false,
                        'dias_vencimiento' => method_exists($cotizacion, 'dias_vencimiento') ? $cotizacion->dias_vencimiento : 0,
                        'created_at' => $cotizacion->created_at->format('d/m/Y')
                    ];
                }),
                'pagination' => [
                    'current_page' => $cotizaciones->currentPage(),
                    'last_page' => $cotizaciones->lastPage(),
                    'per_page' => $cotizaciones->perPage(),
                    'total' => $cotizaciones->total()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar cotizaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $clienteId = $request->get('cliente_id');
        $cliente = null;
        
        if ($clienteId) {
            $cliente = Cliente::find($clienteId);
        }

        return view('cotizaciones.create', compact('cliente'));
    }

 /**
 * Store a newly created resource in storage.
 */
public function store(CotizacionRequest $request)
{
    // Log inicial para debug
    \Log::info('🚀 CotizacionController@store iniciando');
    \Log::info('📋 Datos recibidos:', ['data' => $request->all()]);
    \Log::info('🔍 Método HTTP:', ['method' => $request->method()]);
    \Log::info('🌐 URL:', ['url' => $request->url()]);

    try {
        DB::beginTransaction();

        // Obtener datos validados
        $validated = $request->validated();
        \Log::info('✅ Datos validados:', ['data' => $validated]);

        // **ASIGNAR VENDEDOR AUTOMÁTICAMENTE**
        if (!isset($validated['vendedor_id']) || empty($validated['vendedor_id'])) {
            // TEMPORAL: Asignar vendedor por defecto durante desarrollo
            $vendedorPorDefecto = \App\Models\User::first(); // Toma el primer usuario
            $validated['vendedor_id'] = $vendedorPorDefecto ? $vendedorPorDefecto->id : 1;
            \Log::info('👤 Vendedor asignado automáticamente (modo desarrollo):', ['user_id' => $validated['vendedor_id']]);
        }

        // **CÓDIGO DE COTIZACIÓN - Campo opcional para referencia externa**
        // Nota: El código puede estar vacío o repetirse entre cotizaciones
        // Solo es una referencia al portal de mercado público para licitaciones
        $codigoCotizacion = trim($validated['codigo'] ?? '');
        
        if (!empty($codigoCotizacion)) {
            \Log::info('🏛️ Cotización para licitación pública con código:', ['codigo' => $codigoCotizacion]);
        } else {
            \Log::info('🏢 Cotización regular (sin código de licitación)');
            // Mantener el campo vacío - NO generar código automático
            $validated['codigo'] = '';
        }

        // **PROCESAR PRODUCTOS COTIZADOS**
        if (isset($validated['productos_cotizados']) && is_array($validated['productos_cotizados'])) {
            $productos_procesados = [];
            
            foreach ($validated['productos_cotizados'] as $index => $producto) {
                \Log::info("🛒 Procesando producto {$index}:", ['producto' => $producto]);
                
                $producto_data = [
                    'producto_id' => $producto['id_producto'] ?? null,
                    'nombre' => $producto['nombre_producto'],
                    'descripcion' => $producto['descripcion_corta'] ?? '',
                    'cantidad' => intval($producto['cantidad']),
                    'precio_unitario' => floatval($producto['precio_unitario']),
                    'descuento' => floatval($producto['descuento'] ?? 0)
                ];
                
                // Calcular subtotal
                $subtotal = $producto_data['cantidad'] * $producto_data['precio_unitario'];
                $producto_data['subtotal'] = $subtotal - ($subtotal * $producto_data['descuento'] / 100);
                
                $productos_procesados[] = $producto_data;
                \Log::info("✅ Producto procesado:", ['producto_data' => $producto_data]);
            }
            
            $validated['productos_cotizados'] = json_encode($productos_procesados);
            \Log::info('📦 Productos finales (JSON):', ['productos_json' => $validated['productos_cotizados']]);
        }

        // **ESTABLECER ESTADO POR DEFECTO**
        if (!isset($validated['estado'])) {
            $validated['estado'] = 'Pendiente';
        }

        \Log::info('📝 Datos finales antes de crear:', ['data' => $validated]);

        // **CREAR LA COTIZACIÓN**
        $cotizacion = Cotizacion::create($validated);

        \Log::info('🎉 Cotización creada exitosamente:', [
            'id' => $cotizacion->id,
            'codigo' => $cotizacion->codigo,
            'total_con_iva' => $cotizacion->total_con_iva,
            'es_licitacion' => !empty($cotizacion->codigo)
        ]);

        // **CREAR SEGUIMIENTO AUTOMÁTICO SI CORRESPONDE**
        if (in_array($cotizacion->estado, ['Enviada', 'En Revisión'])) {
            try {
                // Solo si existe el método en el modelo
                if (method_exists($cotizacion, 'crearSeguimientoAutomatico')) {
                    $cotizacion->crearSeguimientoAutomatico();
                    \Log::info('📋 Seguimiento automático creado');
                }
            } catch (\Exception $e) {
                \Log::warning('⚠️ No se pudo crear seguimiento automático:', ['error' => $e->getMessage()]);
            }
        }

        DB::commit();

        // **RESPUESTA EXITOSA**
        $response_data = [
            'success' => true,
            'message' => 'Cotización creada correctamente',
            'data' => [
                'id' => $cotizacion->id,
                'codigo' => $cotizacion->codigo,
                'total_con_iva' => $cotizacion->total_con_iva,
                'redirect_url' => route('cotizaciones.show', $cotizacion)
            ]
        ];

        \Log::info('✅ Respuesta exitosa preparada:', ['response' => $response_data]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json($response_data, 201);
        }

        return redirect()->route('cotizaciones.show', $cotizacion)
            ->with('success', 'Cotización creada correctamente');

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollback();
        
        \Log::error('❌ Error de validación:', [
            'errors' => $e->errors(),
            'request_data' => $request->all()
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación en los datos enviados',
                'errors' => $e->errors()
            ], 422);
        }

        return back()->withInput()->withErrors($e->errors());

    } catch (\Exception $e) {
        DB::rollback();
        
        \Log::error('❌ Error general al crear cotización:', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all()
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }

        return back()->withInput()
            ->withErrors(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
    }
}
    /**
 * Display the specified resource.
 */
public function show($id)
{
    // DEBUGGING: Verificar qué ID llega
    \Log::info('🔍 DEBUGGING show() method - ID recibido:', ['id' => $id]);
    
    try {
        // Buscar la cotización por ID manualmente
        $cotizacion = Cotizacion::findOrFail($id);
        
        \Log::info('📋 Cotización encontrada:', [
            'id' => $cotizacion->id,
            'codigo' => $cotizacion->codigo,
            'nombre_cotizacion' => $cotizacion->nombre_cotizacion,
            'total_con_iva' => $cotizacion->total_con_iva
        ]);

        // Cargar relaciones
        $cotizacion->load(['cliente', 'vendedor']);
        
        if ($cotizacion->cliente) {
            \Log::info('👤 Cliente cargado:', ['nombre' => $cotizacion->cliente->nombre_institucion]);
        } else {
            \Log::warning('⚠️ No se encontró cliente relacionado');
        }
        
        if ($cotizacion->vendedor) {
            \Log::info('👨‍💼 Vendedor cargado:', ['nombre' => $cotizacion->vendedor->name]);
        } else {
            \Log::warning('⚠️ No se encontró vendedor relacionado');
        }

        // Debug de productos
        \Log::info('🛒 Productos cotizados (raw):', ['productos' => $cotizacion->productos_cotizados]);

        return view('cotizaciones.show', compact('cotizacion'));
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        \Log::error('❌ Cotización no encontrada:', ['id' => $id]);
        abort(404, 'Cotización no encontrada');
        
    } catch (\Exception $e) {
        \Log::error('❌ Error general en show():', [
            'id' => $id,
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);
        abort(500, 'Error interno del servidor');
    }
}
   /**
 * Show the form for editing the specified resource.
 */
public function edit($id)
{
    \Log::info('🔧 DEBUGGING edit() method - ID recibido:', ['id' => $id]);
    
    try {
        // Buscar la cotización por ID
        $cotizacion = Cotizacion::findOrFail($id);
        
        \Log::info('📋 Cotización encontrada para editar:', [
            'id' => $cotizacion->id,
            'codigo' => $cotizacion->codigo,
            'nombre_cotizacion' => $cotizacion->nombre_cotizacion
        ]);

        // TEMPORAL: Sin verificación de permisos durante desarrollo
        // TODO: Habilitar cuando se implemente el sistema de login
        /*
        $user = Auth::user();
        if ($user->esVendedor() && !$user->esJefe() && $cotizacion->vendedor_id !== $user->id) {
            abort(403, 'No tienes permisos para editar esta cotización');
        }

        // No permitir editar cotizaciones ganadas o perdidas (salvo administradores)
        if (in_array($cotizacion->estado, ['Ganada', 'Perdida']) && !$user->esAdministrador()) {
            return redirect()->route('cotizaciones.show', $cotizacion)
                ->with('warning', 'No se pueden editar cotizaciones finalizadas');
        }
        */

        // Cargar relaciones necesarias
        $cotizacion->load(['cliente', 'vendedor']);

        // Preparar datos para el componente Vue.js
        $cotizacionData = [
            'id' => $cotizacion->id,
            'nombre_cotizacion' => $cotizacion->nombre_cotizacion,
            'codigo' => $cotizacion->codigo,
            'nombre_institucion' => $cotizacion->nombre_institucion,
            'nombre_contacto' => $cotizacion->nombre_contacto,
            'info_contacto_vendedor' => $cotizacion->info_contacto_vendedor,
            'validez_oferta' => $cotizacion->validez_oferta ? 
                (is_string($cotizacion->validez_oferta) ? $cotizacion->validez_oferta : $cotizacion->validez_oferta->format('Y-m-d')) : null,
            'forma_pago' => $cotizacion->forma_pago,
            'plazo_entrega' => $cotizacion->plazo_entrega,
            'garantia_tecnica' => $cotizacion->garantia_tecnica,
            'informacion_adicional' => $cotizacion->informacion_adicional,
            'descripcion_opcionales' => $cotizacion->descripcion_opcionales,
            'cliente_id' => $cotizacion->cliente_id,
            'vendedor_id' => $cotizacion->vendedor_id,
            'total_neto' => $cotizacion->total_neto,
            'iva' => $cotizacion->iva,
            'total_con_iva' => $cotizacion->total_con_iva,
            'estado' => $cotizacion->estado,
        ];

        // Procesar productos cotizados para el formato del componente Vue
        $productos_procesados = [];
        if (!empty($cotizacion->productos_cotizados)) {
            $productos = is_string($cotizacion->productos_cotizados) 
                ? json_decode($cotizacion->productos_cotizados, true) 
                : $cotizacion->productos_cotizados;
            
            if (is_array($productos)) {
                foreach ($productos as $producto) {
                    $productos_procesados[] = [
                        'producto_id' => $producto['producto_id'] ?? null,
                        'nombre' => $producto['nombre'] ?? '',
                        'cantidad' => $producto['cantidad'] ?? 1,
                        'precio_unitario' => $producto['precio_unitario'] ?? 0,
                        'descuento' => $producto['descuento'] ?? 0
                    ];
                }
            }
        }
        
        $cotizacionData['productos_cotizados'] = $productos_procesados;

        // Agregar información del cliente para preselección
        if ($cotizacion->cliente) {
            $cotizacionData['cliente'] = [
                'id' => $cotizacion->cliente->id,
                'nombre_institucion' => $cotizacion->cliente->nombre_institucion,
                'rut' => $cotizacion->cliente->rut,
                'email' => $cotizacion->cliente->email,
                'tipo_cliente' => $cotizacion->cliente->tipo_cliente
            ];
        }

        \Log::info('✅ Datos preparados para edición:', ['cotizacion_data' => $cotizacionData]);

        return view('cotizaciones.edit', compact('cotizacion', 'cotizacionData'));
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        \Log::error('❌ Cotización no encontrada para editar:', ['id' => $id]);
        return redirect()->route('cotizaciones.index')
            ->with('error', 'Cotización no encontrada');
        
    } catch (\Exception $e) {
        \Log::error('❌ Error general en edit():', [
            'id' => $id,
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);
        
        return redirect()->route('cotizaciones.index')
            ->with('error', 'Error al cargar la cotización para editar');
    }
}
    /**
 * Update the specified resource in storage.
 */
public function update(CotizacionRequest $request, $id)
{
    \Log::info('🔧 DEBUGGING update() method - ID recibido:', ['id' => $id]);
    \Log::info('📋 Datos de actualización recibidos:', ['data' => $request->all()]);
    
    try {
        DB::beginTransaction();

        // Buscar la cotización por ID
        $cotizacion = Cotizacion::findOrFail($id);
        
        \Log::info('📋 Cotización encontrada para actualizar:', [
            'id' => $cotizacion->id,
            'codigo_actual' => $cotizacion->codigo,
            'estado_actual' => $cotizacion->estado
        ]);

        // TEMPORAL: Sin verificación de permisos durante desarrollo
        // TODO: Habilitar cuando se implemente el sistema de login
        /*
        $user = Auth::user();
        if ($user->esVendedor() && !$user->esJefe() && $cotizacion->vendedor_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para editar esta cotización'
            ], 403);
        }
        */

        $validated = $request->validated();
        $estadoAnterior = $cotizacion->estado;

        
// **CÓDIGO DE COTIZACIÓN - Campo opcional para referencia externa**
// Nota: El código puede repetirse entre cotizaciones (múltiples propuestas para la misma licitación)
// No se valida unicidad ya que es solo una referencia al portal de mercado público

$codigoNuevo = trim($validated['codigo'] ?? '');

\Log::info('📋 Código de cotización:', [
    'codigo_anterior' => $cotizacion->codigo,
    'codigo_nuevo' => $codigoNuevo,
    'es_licitacion' => !empty($codigoNuevo)
]);

if (!empty($codigoNuevo)) {
    \Log::info('🏛️ Cotización para licitación pública con código: ' . $codigoNuevo);
} else {
    \Log::info('🏢 Cotización regular (sin código de licitación)');
}

        // **PROCESAR PRODUCTOS COTIZADOS**
        if (isset($validated['productos_cotizados']) && is_array($validated['productos_cotizados'])) {
            $productos_procesados = [];
            
            foreach ($validated['productos_cotizados'] as $index => $producto) {
                \Log::info("🛒 Procesando producto {$index} para actualización:", ['producto' => $producto]);
                
                $producto_data = [
                    'producto_id' => $producto['id_producto'] ?? null,
                    'nombre' => $producto['nombre_producto'],
                    'descripcion' => $producto['descripcion_corta'] ?? '',
                    'cantidad' => intval($producto['cantidad']),
                    'precio_unitario' => floatval($producto['precio_unitario']),
                    'descuento' => floatval($producto['descuento'] ?? 0)
                ];
                
                // Calcular subtotal
                $subtotal = $producto_data['cantidad'] * $producto_data['precio_unitario'];
                $producto_data['subtotal'] = $subtotal - ($subtotal * $producto_data['descuento'] / 100);
                
                $productos_procesados[] = $producto_data;
                \Log::info("✅ Producto procesado para actualización:", ['producto_data' => $producto_data]);
            }
            
            $validated['productos_cotizados'] = json_encode($productos_procesados);
            \Log::info('📦 Productos finales para actualización (JSON):', ['productos_json' => $validated['productos_cotizados']]);
        }

        \Log::info('📝 Datos finales antes de actualizar:', ['data' => $validated]);

        // **ACTUALIZAR LA COTIZACIÓN**
        $cotizacion->update($validated);

        \Log::info('🎉 Cotización actualizada exitosamente:', [
            'id' => $cotizacion->id,
            'codigo' => $cotizacion->codigo,
            'total_con_iva' => $cotizacion->total_con_iva,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $cotizacion->estado
        ]);

        // **MANEJAR CAMBIO DE ESTADO**
        if (isset($validated['estado']) && $validated['estado'] !== $estadoAnterior) {
            \Log::info('🔄 Cambio de estado detectado:', [
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $validated['estado']
            ]);
            
            // TODO: Agregar lógica de seguimiento cuando esté implementado
            /*
            $seguimientoActivo = $cotizacion->seguimientos()
                ->whereIn('estado', ['pendiente', 'en_proceso'])
                ->first();
            
            if ($seguimientoActivo) {
                $nota_cambio = "\n[" . now()->format('d/m/Y H:i') . "] Estado cambiado de '{$estadoAnterior}' a '{$validated['estado']}'";
                $seguimientoActivo->update([
                    'notas' => ($seguimientoActivo->notas ?? '') . $nota_cambio
                ]);
            }
            */
        }

        DB::commit();

        // **RESPUESTA EXITOSA**
        $response_data = [
            'success' => true,
            'message' => 'Cotización actualizada correctamente',
            'data' => [
                'id' => $cotizacion->id,
                'codigo' => $cotizacion->codigo,
                'total_con_iva' => $cotizacion->total_con_iva,
                'redirect_url' => route('cotizaciones.show', $cotizacion->id)
            ]
        ];

        \Log::info('✅ Respuesta exitosa de actualización preparada:', ['response' => $response_data]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json($response_data, 200);
        }

        return redirect()->route('cotizaciones.show', $cotizacion->id)
            ->with('success', 'Cotización actualizada correctamente');

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollback();
        
        \Log::error('❌ Error de validación en actualización:', [
            'errors' => $e->errors(),
            'request_data' => $request->all()
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación en los datos enviados',
                'errors' => $e->errors()
            ], 422);
        }

        return back()->withInput()->withErrors($e->errors());

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        DB::rollback();
        
        \Log::error('❌ Cotización no encontrada para actualizar:', ['id' => $id]);
        
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Cotización no encontrada'
            ], 404);
        }

        return redirect()->route('cotizaciones.index')
            ->with('error', 'Cotización no encontrada');

    } catch (\Exception $e) {
        DB::rollback();
        
        \Log::error('❌ Error general al actualizar cotización:', [
            'id' => $id,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all()
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }

        return back()->withInput()
            ->withErrors(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
    }
}
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cotizacion $cotizacion)
    {
        try {
            $user = Auth::user();

            // Solo el vendedor propietario, jefes o administradores pueden eliminar
            if (!$user->esAdministrador() && !$user->esJefe() && $cotizacion->vendedor_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para eliminar esta cotización'
                ], 403);
            }

            // No permitir eliminar cotizaciones ganadas (salvo administradores)
            if ($cotizacion->estado === 'Ganada' && !$user->esAdministrador()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden eliminar cotizaciones ganadas'
                ], 422);
            }

            // Eliminar seguimientos asociados
            $cotizacion->seguimientos()->delete();

            $cotizacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cotización eliminada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar cotización: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
 * ACTUALIZACIÓN MASIVA DE ESTADO - Para resolver el problema de 5,000 cotizaciones desactualizadas
 */
public function updateMasivo(Request $request)
{
    \Log::info('🔧 Iniciando actualización masiva de cotizaciones');
    \Log::info('📋 Datos recibidos:', $request->all());
    
    try {
        // TEMPORAL: Sin verificación de permisos durante desarrollo
        // TODO: Habilitar cuando se implemente el sistema de login
        /*
        $user = Auth::user();
        if (!$user->esJefe() && !$user->esAdministrador()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo jefes y administradores pueden hacer actualizaciones masivas'
            ], 403);
        }
        */

        $validated = $request->validate([
            'cotizacion_ids' => 'required|array|min:1|max:1000', // Máximo 1000 por seguridad
            'cotizacion_ids.*' => 'exists:cotizaciones,id',
            'nuevo_estado' => 'required|in:Pendiente,Enviada,En Revisión,Ganada,Perdida,Vencida',
            'motivo' => 'nullable|string|max:500'
        ]);

        $cotizacionIds = $validated['cotizacion_ids'];
        $nuevoEstado = $validated['nuevo_estado'];
        $motivo = $validated['motivo'] ?? '';

        \Log::info('✅ Validación exitosa:', [
            'total_cotizaciones' => count($cotizacionIds),
            'nuevo_estado' => $nuevoEstado,
            'tiene_motivo' => !empty($motivo)
        ]);

        DB::beginTransaction();

        $actualizado = 0;
        $errores = [];
        
        foreach ($cotizacionIds as $id) {
            try {
                $cotizacion = Cotizacion::find($id);
                
                if ($cotizacion) {
                    $estadoAnterior = $cotizacion->estado;
                    
                    // Solo actualizar si el estado realmente cambió
                    if ($estadoAnterior !== $nuevoEstado) {
                        $cotizacion->update(['estado' => $nuevoEstado]);
                        $actualizado++;
                        
                        \Log::info("📝 Cotización {$id} actualizada:", [
                            'codigo' => $cotizacion->codigo,
                            'estado_anterior' => $estadoAnterior,
                            'estado_nuevo' => $nuevoEstado
                        ]);
                        
                        // TODO: Registrar en seguimiento si existe la funcionalidad
                        /*
                        $seguimiento = $cotizacion->seguimientos()
                            ->whereIn('estado', ['pendiente', 'en_proceso'])
                            ->first();
                        
                        if ($seguimiento) {
                            $nota = "\n[" . now()->format('d/m/Y H:i') . "] Actualización masiva: '{$estadoAnterior}' → '{$nuevoEstado}'";
                            if ($motivo) {
                                $nota .= "\nMotivo: {$motivo}";
                            }
                            
                            $seguimiento->update([
                                'notas' => ($seguimiento->notas ?? '') . $nota
                            ]);
                        }
                        */
                    } else {
                        \Log::info("ℹ️ Cotización {$id} ya tenía el estado '{$nuevoEstado}', omitida");
                    }
                } else {
                    $errores[] = "Cotización con ID {$id} no encontrada";
                }
                
            } catch (\Exception $e) {
                $errores[] = "Error al actualizar cotización {$id}: " . $e->getMessage();
                \Log::error("❌ Error al actualizar cotización {$id}:", [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            }
        }

        DB::commit();

        $mensaje = "✅ Actualización masiva completada: {$actualizado} cotizaciones actualizadas";
        if (count($errores) > 0) {
            $mensaje .= ". " . count($errores) . " errores encontrados.";
        }

        \Log::info('🎉 Actualización masiva finalizada:', [
            'cotizaciones_procesadas' => count($cotizacionIds),
            'cotizaciones_actualizadas' => $actualizado,
            'errores' => count($errores),
            'nuevo_estado' => $nuevoEstado
        ]);

        return response()->json([
            'success' => true,
            'message' => $mensaje,
            'detalles' => [
                'procesadas' => count($cotizacionIds),
                'actualizadas' => $actualizado,
                'errores' => $errores,
                'estado_aplicado' => $nuevoEstado
            ]
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollback();
        
        \Log::error('❌ Error de validación en actualización masiva:', [
            'errors' => $e->errors(),
            'request_data' => $request->all()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error de validación en los datos enviados',
            'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        DB::rollback();
        
        \Log::error('❌ Error general en actualización masiva:', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'request_data' => $request->all()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error interno del servidor: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * GENERAR PDF DE COTIZACIÓN
     */
    public function generarPdf(Cotizacion $cotizacion)
    {
        try {
            $user = Auth::user();
            
            // Verificar permisos
            if ($user->esVendedor() && !$user->esJefe() && $cotizacion->vendedor_id !== $user->id) {
                abort(403, 'No tienes acceso a esta cotización');
            }

            $cotizacion->load(['cliente', 'vendedor']);

            $pdf = Pdf::loadView('cotizaciones.pdf', compact('cotizacion'))
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => true,
                    'defaultFont' => 'Arial'
                ]);

            $filename = "Cotizacion_{$cotizacion->codigo}.pdf";

            return $pdf->download($filename);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al generar PDF: ' . $e->getMessage()]);
        }
    }

    /**
     * DUPLICAR COTIZACIÓN
     */
    public function duplicar(Cotizacion $cotizacion)
    {
        try {
            $user = Auth::user();
            
            // Verificar permisos
            if ($user->esVendedor() && !$user->esJefe() && $cotizacion->vendedor_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta cotización'
                ], 403);
            }

            DB::beginTransaction();

            $datosCotizacion = $cotizacion->toArray();
            
            // Remover campos que no se deben duplicar
            unset($datosCotizacion['id']);
            unset($datosCotizacion['codigo']);
            unset($datosCotizacion['created_at']);
            unset($datosCotizacion['updated_at']);
            unset($datosCotizacion['deleted_at']);
            
            // Generar nuevo código
            $datosCotizacion['codigo'] = Cotizacion::generarCodigo();
            $datosCotizacion['nombre_cotizacion'] = $datosCotizacion['nombre_cotizacion'] . ' (Copia)';
            $datosCotizacion['estado'] = 'Pendiente';
            $datosCotizacion['vendedor_id'] = $user->id;
            
            // Actualizar validez de la oferta a un mes
            $datosCotizacion['validez_oferta'] = now()->addMonth()->toDateString();

            $nuevaCotizacion = Cotizacion::create($datosCotizacion);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cotización duplicada correctamente',
                'data' => [
                    'id' => $nuevaCotizacion->id,
                    'codigo' => $nuevaCotizacion->codigo,
                    'redirect_url' => route('cotizaciones.edit', $nuevaCotizacion)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al duplicar cotización: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ESTADÍSTICAS PARA DASHBOARD
     */
    public function estadisticas(Request $request)
    {
        try {
            $user = Auth::user();
            $query = Cotizacion::query();

            // Aplicar filtros de rol
            if ($user->esVendedor() && !$user->esJefe()) {
                $query->paraVendedor($user->id);
            } elseif ($user->esJefe()) {
                $equipoIds = $user->vendedoresSupervision()->pluck('users.id')->toArray();
                if (!empty($equipoIds)) {
                    $query->paraJefeVentas($equipoIds);
                }
            }

            $estadisticas = [
                'mes_actual' => [
                    'total' => (clone $query)->delMesActual()->count(),
                    'ganadas' => (clone $query)->delMesActual()->where('estado', 'Ganada')->count(),
                    'valor_ganado' => (clone $query)->delMesActual()->where('estado', 'Ganada')->sum('total_con_iva'),
                ],
                'estados' => (clone $query)->selectRaw('estado, COUNT(*) as total')
                    ->groupBy('estado')
                    ->pluck('total', 'estado')
                    ->toArray(),
                'alertas' => [
                    'vencidas' => (clone $query)->vencidas()->count(),
                    'proximas_vencer' => (clone $query)->proximasVencer(7)->count()
                ]
            ];

            if ($user->esJefe() || $user->esAdministrador()) {
                $estadisticas['top_vendedores'] = Cotizacion::topVendedoresMes(5);
            }

            return response()->json([
                'success' => true,
                'data' => $estadisticas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }
}