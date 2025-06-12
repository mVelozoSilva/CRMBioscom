<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Contacto;
use App\Http\Requests\ClienteStoreRequest;
use App\Http\Requests\ClienteUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Si es petición AJAX, devolver JSON
        if ($request->ajax()) {
            return $this->getClientesAjax($request);
        }

        // Vista normal
        $user = Auth::user();
        $clientes = Cliente::with(['contactos'])
            ->when($user->esVendedor() && !$user->esJefe(), function ($query) use ($user) {
                // Vendedores solo ven sus clientes asignados
                return $query->paraVendedor($user->id);
            })
            ->when($user->esJefe(), function ($query) use ($user, $request) {
                // Jefes pueden filtrar por vendedor
                $vendedorFiltro = $request->get('vendedor');
                if ($vendedorFiltro && $vendedorFiltro !== 'todos') {
                    return $query->paraVendedor($vendedorFiltro);
                }
                // O ver todo su equipo
                $equipoIds = $user->vendedoresSupervision()->pluck('users.id')->toArray();
                return $query->paraJefeVentas($equipoIds);
            })
            ->orderBy('nombre_institucion', 'asc')
            ->paginate(20);

        // Estadísticas para el dashboard de clientes
        $estadisticas = [
            'total_clientes' => Cliente::count(),
            'por_tipo' => Cliente::contarPorTipo(),
            'con_mas_cotizaciones' => Cliente::conMasCotizaciones(5)
        ];

        return view('clientes.index', compact('clientes', 'estadisticas'));
    }

    /**
     * AJAX: Obtener clientes con filtros y paginación
     */
    private function getClientesAjax(Request $request)
    {
        try {
            $query = Cliente::with(['contactos']);
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
            if ($request->filled('tipo_cliente')) {
                $query->tipoCliente($request->get('tipo_cliente'));
            }

            // **BÚSQUEDA**
            if ($request->filled('busqueda')) {
                $query->busqueda($request->get('busqueda'));
            }

            // **ORDENAMIENTO**
            $sortField = $request->get('sort', 'nombre_institucion');
            $sortDirection = $request->get('direction', 'asc');
            $query->orderBy($sortField, $sortDirection);

            // **PAGINACIÓN**
            $perPage = $request->get('per_page', 20);
            $clientes = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $clientes->through(function ($cliente) {
                    return [
                        'id' => $cliente->id,
                        'nombre_institucion' => $cliente->nombre_institucion,
                        'rut' => $cliente->rut_formateado,
                        'tipo_cliente' => $cliente->tipo_cliente,
                        'email' => $cliente->email,
                        'telefono' => $cliente->telefono,
                        'nombre_contacto' => $cliente->nombre_contacto,
                        'contactos_count' => $cliente->contactos->count(),
                        'seguimientos_pendientes' => $cliente->seguimientos_pendientes,
                        'ultima_cotizacion' => $cliente->ultima_cotizacion ? $cliente->ultima_cotizacion->codigo : null
                    ];
                }),
                'pagination' => [
                    'current_page' => $clientes->currentPage(),
                    'last_page' => $clientes->lastPage(),
                    'per_page' => $clientes->perPage(),
                    'total' => $clientes->total()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar clientes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Solo administradores y jefes pueden crear clientes
        if (!Auth::user()->esAdministrador() && !Auth::user()->esJefe()) {
            abort(403, 'No tienes permisos para crear clientes');
        }

        return view('clientes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClienteStoreRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $user = Auth::user();

            // **VALIDACIÓN ANTI-DUPLICADOS**
            if (!empty($validated['rut'])) {
                $rutExiste = Cliente::where('rut', $validated['rut'])->exists();
                if ($rutExiste) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe un cliente con este RUT',
                        'errors' => ['rut' => ['El RUT ya está registrado']]
                    ], 422);
                }
            }

            // Si es vendedor, asignarse automáticamente
            if ($user->esVendedor()) {
                $vendedoresArray = $validated['vendedores_a_cargo'] ?? [];
                if (!in_array($user->id, $vendedoresArray)) {
                    $vendedoresArray[] = $user->id;
                }
                $validated['vendedores_a_cargo'] = $vendedoresArray;
            }

            $cliente = Cliente::create($validated);

            // **CREAR CONTACTO PRINCIPAL SI SE PROPORCIONÓ**
            if (!empty($validated['nombre_contacto'])) {
                Contacto::create([
                    'cliente_id' => $cliente->id,
                    'nombre' => $validated['nombre_contacto'],
                    'email' => $validated['email'],
                    'telefono' => $validated['telefono'],
                    'cargo' => 'Contacto Principal',
                    'area' => 'General'
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cliente creado correctamente',
                    'data' => $cliente->toSearchArray()
                ]);
            }

            return redirect()->route('clientes.show', $cliente)
                ->with('success', 'Cliente creado correctamente');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear cliente: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->withErrors(['error' => 'Error al crear cliente: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        // Verificar permisos
        $user = Auth::user();
        if ($user->esVendedor() && !$user->esJefe()) {
            $vendedoresAsignados = $cliente->vendedores_a_cargo ?? [];
            if (!in_array($user->id, $vendedoresAsignados)) {
                abort(403, 'No tienes acceso a este cliente');
            }
        }

        $cliente->load([
            'contactos',
            'cotizaciones' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            },
            'seguimientos' => function ($query) {
                $query->orderBy('proxima_gestion', 'desc')->limit(10);
            }
        ]);

        // Estadísticas del cliente
        $estadisticas = [
            'total_cotizaciones' => $cliente->cotizaciones()->count(),
            'cotizaciones_ganadas' => $cliente->cotizaciones()->where('estado', 'Ganada')->count(),
            'valor_total_ganado' => $cliente->cotizaciones()->where('estado', 'Ganada')->sum('total_con_iva'),
            'seguimientos_activos' => $cliente->seguimientos()->whereIn('estado', ['pendiente', 'en_proceso'])->count()
        ];

        return view('clientes.show', compact('cliente', 'estadisticas'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        // Verificar permisos de edición
        $user = Auth::user();
        if ($user->esVendedor() && !$user->esJefe()) {
            $vendedoresAsignados = $cliente->vendedores_a_cargo ?? [];
            if (!in_array($user->id, $vendedoresAsignados)) {
                abort(403, 'No tienes permisos para editar este cliente');
            }
        }

        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClienteUpdateRequest $request, Cliente $cliente)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $user = Auth::user();

            // Verificar permisos
            if ($user->esVendedor() && !$user->esJefe()) {
                $vendedoresAsignados = $cliente->vendedores_a_cargo ?? [];
                if (!in_array($user->id, $vendedoresAsignados)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes permisos para editar este cliente'
                    ], 403);
                }
            }

            // **VALIDACIÓN ANTI-DUPLICADOS (EXCLUYENDO EL ACTUAL)**
            if (!empty($validated['rut']) && $validated['rut'] !== $cliente->rut) {
                $rutExiste = Cliente::where('rut', $validated['rut'])
                    ->where('id', '!=', $cliente->id)
                    ->exists();
                
                if ($rutExiste) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe otro cliente con este RUT',
                        'errors' => ['rut' => ['El RUT ya está registrado en otro cliente']]
                    ], 422);
                }
            }

            $cliente->update($validated);

            // **ACTUALIZAR CONTACTO PRINCIPAL SI CAMBIÓ**
            if (!empty($validated['nombre_contacto'])) {
                $contactoPrincipal = $cliente->contactos()->where('cargo', 'Contacto Principal')->first();
                
                if ($contactoPrincipal) {
                    $contactoPrincipal->update([
                        'nombre' => $validated['nombre_contacto'],
                        'email' => $validated['email'],
                        'telefono' => $validated['telefono']
                    ]);
                } else {
                    Contacto::create([
                        'cliente_id' => $cliente->id,
                        'nombre' => $validated['nombre_contacto'],
                        'email' => $validated['email'],
                        'telefono' => $validated['telefono'],
                        'cargo' => 'Contacto Principal',
                        'area' => 'General'
                    ]);
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cliente actualizado correctamente',
                    'data' => $cliente->fresh()->toSearchArray()
                ]);
            }

            return redirect()->route('clientes.show', $cliente)
                ->with('success', 'Cliente actualizado correctamente');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar cliente: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->withErrors(['error' => 'Error al actualizar cliente: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        try {
            $user = Auth::user();

            // Solo administradores pueden eliminar
            if (!$user->esAdministrador()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los administradores pueden eliminar clientes'
                ], 403);
            }

            // Verificar si tiene cotizaciones o seguimientos activos
            $tieneCotizaciones = $cliente->cotizaciones()->exists();
            $tieneSeguimientos = $cliente->seguimientos()->whereIn('estado', ['pendiente', 'en_proceso'])->exists();

            if ($tieneCotizaciones || $tieneSeguimientos) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un cliente con cotizaciones o seguimientos activos'
                ], 422);
            }

            $cliente->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cliente eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * BÚSQUEDA RÁPIDA PARA AUTOCOMPLETE EN COTIZACIONES
     * API endpoint para buscar clientes
     */
    public function buscarClientes(Request $request)
    {
        try {
            $termino = $request->get('q', '');
            
            if (strlen($termino) < 2) {
                return response()->json([]);
            }

            $user = Auth::user();
            $query = Cliente::busqueda($termino);

            // Aplicar filtros de rol
            if ($user->esVendedor() && !$user->esJefe()) {
                $query->paraVendedor($user->id);
            } elseif ($user->esJefe()) {
                $equipoIds = $user->vendedoresSupervision()->pluck('users.id')->toArray();
                if (!empty($equipoIds)) {
                    $query->paraJefeVentas($equipoIds);
                }
            }

            $clientes = $query->limit(10)
                ->get()
                ->map(function ($cliente) {
                    return [
                        'id' => $cliente->id,
                        'text' => $cliente->nombre_institucion . ($cliente->rut ? ' (' . $cliente->rut_formateado . ')' : ''),
                        'data' => $cliente->toSearchArray()
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
     * IMPORTACIÓN MASIVA DE CLIENTES (PARA JEFES Y ADMINISTRADORES)
     */
    public function importarMasivo(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->esJefe() && !$user->esAdministrador()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para importar clientes masivamente'
                ], 403);
            }

            $request->validate([
                'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240'
            ]);

            // TODO: Implementar ImportacionClientesJob
            // Excel::import(new ClientesImport(), $request->file('archivo'));

            return response()->json([
                'success' => false,
                'message' => 'Funcionalidad de importación masiva en desarrollo'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al importar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ESTADÍSTICAS PARA DASHBOARD DE ADMINISTRADORES
     */
    public function estadisticas(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->esAdministrador() && !$user->esJefe()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver estadísticas generales'
                ], 403);
            }

            $estadisticas = [
                'total_clientes' => Cliente::count(),
                'clientes_activos_mes' => Cliente::whereHas('cotizaciones', function ($query) {
                    $query->whereMonth('created_at', now()->month);
                })->count(),
                'por_tipo' => Cliente::contarPorTipo(),
                'con_mas_cotizaciones' => Cliente::conMasCotizaciones(5),
                'nuevos_mes' => Cliente::whereMonth('created_at', now()->month)->count()
            ];

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

    /**
     * ASIGNAR/DESASIGNAR VENDEDORES (SOLO JEFES)
     */
    public function asignarVendedores(Request $request, Cliente $cliente)
    {
        try {
            $user = Auth::user();
            
            if (!$user->esJefe() && !$user->esAdministrador()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los jefes pueden asignar vendedores'
                ], 403);
            }

            $validated = $request->validate([
                'vendedores' => 'required|array',
                'vendedores.*' => 'exists:users,id'
            ]);

            $cliente->update([
                'vendedores_a_cargo' => $validated['vendedores']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vendedores asignados correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar vendedores: ' . $e->getMessage()
            ], 500);
        }
    }
}