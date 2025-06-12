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
        // Si es petición AJAX, devolver JSON
        if ($request->ajax()) {
            return $this->getCotizacionesAjax($request);
        }

        $user = Auth::user();
        $cotizaciones = Cotizacion::with(['cliente', 'vendedor'])
            ->when($user->esVendedor() && !$user->esJefe(), function ($query) use ($user) {
                // Vendedores solo ven sus cotizaciones
                return $query->paraVendedor($user->id);
            })
            ->when($user->esJefe(), function ($query) use ($user, $request) {
                // Jefes pueden filtrar por vendedor o ver todo el equipo
                $vendedorFiltro = $request->get('vendedor');
                if ($vendedorFiltro && $vendedorFiltro !== 'todos') {
                    return $query->paraVendedor($vendedorFiltro);
                }
                $equipoIds = $user->vendedoresSupervision()->pluck('users.id')->toArray();
                return $query->paraJefeVentas($equipoIds);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Estadísticas para el dashboard de cotizaciones
        $estadisticas = [
            'total_mes' => Cotizacion::delMesActual()->count(),
            'valor_ganado_mes' => Cotizacion::valorGanadoMes(),
            'por_estado' => Cotizacion::contarPorEstado(),
            'vencidas' => Cotizacion::vencidas()->count(),
            'proximas_vencer' => Cotizacion::proximasVencer(7)->count()
        ];

        return view('cotizaciones.index', compact('cotizaciones', 'estadisticas'));
    }

    /**
     * AJAX: Obtener cotizaciones con filtros y paginación
     */
    private function getCotizacionesAjax(Request $request)
    {
        try {
            $query = Cotizacion::with(['cliente', 'vendedor']);
            $user = Auth::user();

            // **FILTROS SEGÚN ROL**
            if ($user->esVendedor() && !$user->esJefe()) {
                $query->paraVendedor($user->id);
            } elseif ($user->esJefe()) {
                $vendedorFiltro = $request->get('vendedor');
                if ($vendedorFiltro && $vendedorFiltro !== 'todos') {
                    $query->paraVendedor($vendedorFiltro);
                } else {
                    $equipoIds = $user->vendedoresSupervision()->pluck('users.id')->toArray();
                    if (!empty($equipoIds)) {
                        $query->paraJefeVentas($equipoIds);
                    }
                }
            }

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
                        'validez_oferta' => $cotizacion->validez_oferta->format('d/m/Y'),
                        'color_vencimiento' => $cotizacion->color_vencimiento,
                        'vencida' => $cotizacion->vencida,
                        'dias_vencimiento' => $cotizacion->dias_vencimiento,
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
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $user = Auth::user();

            // **ASIGNAR VENDEDOR AUTOMÁTICAMENTE**
            if (!isset($validated['vendedor_id']) || empty($validated['vendedor_id'])) {
                $validated['vendedor_id'] = $user->id;
            }

            // **GENERAR CÓDIGO AUTOMÁTICAMENTE SI NO SE PROPORCIONA**
            if (empty($validated['codigo'])) {
                $validated['codigo'] = Cotizacion::generarCodigo();
            }

            // **VALIDAR UNICIDAD DE CÓDIGO**
            $codigoExiste = Cotizacion::where('codigo', $validated['codigo'])->exists();
            if ($codigoExiste) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una cotización con este código',
                    'errors' => ['codigo' => ['El código ya está en uso']]
                ], 422);
            }

            // **PROCESAR PRODUCTOS COTIZADOS**
            if (isset($validated['productos_cotizados']) && is_array($validated['productos_cotizados'])) {
                $productos_procesados = [];
                
                foreach ($validated['productos_cotizados'] as $producto) {
                    $producto_data = [
                        'producto_id' => $producto['producto_id'],
                        'nombre' => $producto['nombre'],
                        'cantidad' => $producto['cantidad'] ?? 1,
                        'precio_unitario' => $producto['precio_unitario'] ?? 0,
                        'descuento' => $producto['descuento'] ?? 0
                    ];
                    
                    // Calcular subtotal
                    $subtotal = $producto_data['cantidad'] * $producto_data['precio_unitario'];
                    $producto_data['subtotal'] = $subtotal - ($subtotal * $producto_data['descuento'] / 100);
                    
                    $productos_procesados[] = $producto_data;
                }
                
                $validated['productos_cotizados'] = $productos_procesados;
            }

            $cotizacion = Cotizacion::create($validated);

            // **CREAR SEGUIMIENTO AUTOMÁTICO SI CORRESPONDE**
            if (in_array($cotizacion->estado, ['Enviada', 'En Revisión'])) {
                $cotizacion->crearSeguimientoAutomatico();
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cotización creada correctamente',
                    'data' => [
                        'id' => $cotizacion->id,
                        'codigo' => $cotizacion->codigo,
                        'redirect_url' => route('cotizaciones.show', $cotizacion)
                    ]
                ]);
            }

            return redirect()->route('cotizaciones.show', $cotizacion)
                ->with('success', 'Cotización creada correctamente');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear cotización: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->withErrors(['error' => 'Error al crear cotización: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Cotizacion $cotizacion)
    {
        // Verificar permisos
        $user = Auth::user();
        if ($user->esVendedor() && !$user->esJefe() && $cotizacion->vendedor_id !== $user->id) {
            abort(403, 'No tienes acceso a esta cotización');
        }

        $cotizacion->load(['cliente', 'vendedor', 'seguimientos' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        return view('cotizaciones.show', compact('cotizacion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cotizacion $cotizacion)
    {
        // Verificar permisos
        $user = Auth::user();
        if ($user->esVendedor() && !$user->esJefe() && $cotizacion->vendedor_id !== $user->id) {
            abort(403, 'No tienes permisos para editar esta cotización');
        }

        // No permitir editar cotizaciones ganadas o perdidas (salvo administradores)
        if (in_array($cotizacion->estado, ['Ganada', 'Perdida']) && !$user->esAdministrador()) {
            return redirect()->route('cotizaciones.show', $cotizacion)
                ->with('warning', 'No se pueden editar cotizaciones finalizadas');
        }

        return view('cotizaciones.edit', compact('cotizacion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CotizacionRequest $request, Cotizacion $cotizacion)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            // Verificar permisos
            if ($user->esVendedor() && !$user->esJefe() && $cotizacion->vendedor_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para editar esta cotización'
                ], 403);
            }

            $validated = $request->validated();
            $estadoAnterior = $cotizacion->estado;

            // **VALIDAR UNICIDAD DE CÓDIGO (EXCLUYENDO LA ACTUAL)**
            if (isset($validated['codigo']) && $validated['codigo'] !== $cotizacion->codigo) {
                $codigoExiste = Cotizacion::where('codigo', $validated['codigo'])
                    ->where('id', '!=', $cotizacion->id)
                    ->exists();
                
                if ($codigoExiste) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe otra cotización con este código',
                        'errors' => ['codigo' => ['El código ya está en uso']]
                    ], 422);
                }
            }

            // **PROCESAR PRODUCTOS COTIZADOS**
            if (isset($validated['productos_cotizados']) && is_array($validated['productos_cotizados'])) {
                $productos_procesados = [];
                
                foreach ($validated['productos_cotizados'] as $producto) {
                    $producto_data = [
                        'producto_id' => $producto['producto_id'],
                        'nombre' => $producto['nombre'],
                        'cantidad' => $producto['cantidad'] ?? 1,
                        'precio_unitario' => $producto['precio_unitario'] ?? 0,
                        'descuento' => $producto['descuento'] ?? 0
                    ];
                    
                    // Calcular subtotal
                    $subtotal = $producto_data['cantidad'] * $producto_data['precio_unitario'];
                    $producto_data['subtotal'] = $subtotal - ($subtotal * $producto_data['descuento'] / 100);
                    
                    $productos_procesados[] = $producto_data;
                }
                
                $validated['productos_cotizados'] = $productos_procesados;
            }

            $cotizacion->update($validated);

            // **MANEJAR CAMBIO DE ESTADO**
            if (isset($validated['estado']) && $validated['estado'] !== $estadoAnterior) {
                // El modelo automáticamente creará seguimiento si es necesario
                
                // Registrar cambio en notas de seguimiento si existe
                $seguimientoActivo = $cotizacion->seguimientos()
                    ->whereIn('estado', ['pendiente', 'en_proceso'])
                    ->first();
                
                if ($seguimientoActivo) {
                    $nota_cambio = "\n[" . now()->format('d/m/Y H:i') . "] Estado cambiado de '{$estadoAnterior}' a '{$validated['estado']}'";
                    $seguimientoActivo->update([
                        'notas' => ($seguimientoActivo->notas ?? '') . $nota_cambio
                    ]);
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cotización actualizada correctamente',
                    'data' => $cotizacion->fresh()->toSearchArray()
                ]);
            }

            return redirect()->route('cotizaciones.show', $cotizacion)
                ->with('success', 'Cotización actualizada correctamente');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar cotización: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->withErrors(['error' => 'Error al actualizar cotización: ' . $e->getMessage()]);
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
     * ACTUALIZACIÓN MASIVA DE ESTADO - NUEVA FUNCIONALIDAD
     */
    public function updateMasivo(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->esJefe() && !$user->esAdministrador()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo jefes y administradores pueden hacer actualizaciones masivas'
                ], 403);
            }

            $validated = $request->validate([
                'cotizacion_ids' => 'required|array|min:1',
                'cotizacion_ids.*' => 'exists:cotizaciones,id',
                'nuevo_estado' => 'required|in:Pendiente,Enviada,En Revisión,Ganada,Perdida,Vencida',
                'motivo' => 'nullable|string|max:500'
            ]);

            $cotizacionIds = $validated['cotizacion_ids'];
            $nuevoEstado = $validated['nuevo_estado'];
            $motivo = $validated['motivo'] ?? '';

            $actualizado = 0;
            
            foreach ($cotizacionIds as $id) {
                $cotizacion = Cotizacion::find($id);
                
                if ($cotizacion) {
                    $estadoAnterior = $cotizacion->estado;
                    $cotizacion->update(['estado' => $nuevoEstado]);
                    
                    // Registrar en seguimiento si existe
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
                    
                    $actualizado++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Se actualizaron {$actualizado} cotizaciones a estado '{$nuevoEstado}'"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en actualización masiva: ' . $e->getMessage()
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