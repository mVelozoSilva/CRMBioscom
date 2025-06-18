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

        // Vista normal - simplificada para evitar errores
        $user = Auth::user();
        
        // Por ahora, mostrar todos los clientes sin filtros complejos
        $clientes = Cliente::with(['contactos'])
            ->orderBy('nombre_institucion', 'asc')
            ->paginate(20);

        // Estadísticas básicas
        $estadisticas = [
            'total_clientes' => Cliente::count(),
            'por_tipo' => [
                'Cliente Público' => Cliente::where('tipo_cliente', 'Cliente Público')->count(),
                'Cliente Privado' => Cliente::where('tipo_cliente', 'Cliente Privado')->count(),
                'Revendedor' => Cliente::where('tipo_cliente', 'Revendedor')->count(),
            ],
            'con_mas_cotizaciones' => collect() // Por ahora vacío
        ];

        return view('clientes.index', compact('clientes', 'estadisticas'));
    }
    /**
 * AJAX: Obtener clientes con filtros y paginación
 */
private function getClientesAjax(Request $request)
{
    try {
        \Log::info('🔍 Cargando clientes con filtros:', $request->all());
        
        $query = Cliente::query();

        // **FILTROS BÁSICOS**
        if ($request->filled('busqueda') && !empty(trim($request->get('busqueda')))) {
            $busqueda = trim($request->get('busqueda'));
            \Log::info('🔍 Aplicando búsqueda general:', ['busqueda' => $busqueda]);
            
            $query->where(function($q) use ($busqueda) {
                $q->where('nombre_institucion', 'like', "%{$busqueda}%")
                  ->orWhere('rut', 'like', "%{$busqueda}%")
                  ->orWhere('email', 'like', "%{$busqueda}%")
                  ->orWhere('nombre_contacto', 'like', "%{$busqueda}%");
            });
        }

        if ($request->filled('tipo_cliente') && !empty(trim($request->get('tipo_cliente')))) {
            $tipo = trim($request->get('tipo_cliente'));
            \Log::info('📊 Aplicando filtro de tipo:', ['tipo' => $tipo]);
            $query->where('tipo_cliente', $tipo);
        }

        // **FILTROS DE COLUMNAS ESTILO EXCEL**
        if ($request->filled('filtro_nombre')) {
            $nombresSeleccionados = $request->get('filtro_nombre');
            if (is_array($nombresSeleccionados) && !empty($nombresSeleccionados)) {
                \Log::info('🔍 Aplicando filtro de nombres:', ['nombres' => count($nombresSeleccionados)]);
                $query->whereIn('nombre_institucion', $nombresSeleccionados);
            }
        }

        if ($request->filled('filtro_rut')) {
            $rutsSeleccionados = $request->get('filtro_rut');
            if (is_array($rutsSeleccionados) && !empty($rutsSeleccionados)) {
                \Log::info('🔍 Aplicando filtro de RUTs:', ['ruts' => count($rutsSeleccionados)]);
                $query->where(function($q) use ($rutsSeleccionados) {
                    foreach ($rutsSeleccionados as $rut) {
                        if ($rut === 'Sin RUT') {
                            $q->orWhereNull('rut')->orWhere('rut', '');
                        } else {
                            $q->orWhere('rut', $rut);
                        }
                    }
                });
            }
        }

        if ($request->filled('filtro_tipo')) {
            $tiposSeleccionados = $request->get('filtro_tipo');
            if (is_array($tiposSeleccionados) && !empty($tiposSeleccionados)) {
                \Log::info('🔍 Aplicando filtro de tipos:', ['tipos' => $tiposSeleccionados]);
                $query->whereIn('tipo_cliente', $tiposSeleccionados);
            }
        }

        if ($request->filled('filtro_contacto')) {
            $contactosSeleccionados = $request->get('filtro_contacto');
            if (is_array($contactosSeleccionados) && !empty($contactosSeleccionados)) {
                \Log::info('🔍 Aplicando filtro de contactos:', ['contactos' => count($contactosSeleccionados)]);
                $query->where(function($q) use ($contactosSeleccionados) {
                    foreach ($contactosSeleccionados as $contacto) {
                        if ($contacto === 'Sin contacto') {
                            $q->orWhereNull('nombre_contacto')->orWhere('nombre_contacto', '');
                        } else {
                            $q->orWhere('nombre_contacto', $contacto);
                        }
                    }
                });
            }
        }

        if ($request->filled('filtro_email')) {
            $emailsSeleccionados = $request->get('filtro_email');
            if (is_array($emailsSeleccionados) && !empty($emailsSeleccionados)) {
                \Log::info('🔍 Aplicando filtro de emails:', ['emails' => count($emailsSeleccionados)]);
                $query->whereIn('email', $emailsSeleccionados);
            }
        }

        if ($request->filled('filtro_telefono')) {
            $telefonosSeleccionados = $request->get('filtro_telefono');
            if (is_array($telefonosSeleccionados) && !empty($telefonosSeleccionados)) {
                \Log::info('🔍 Aplicando filtro de teléfonos:', ['telefonos' => count($telefonosSeleccionados)]);
                $query->where(function($q) use ($telefonosSeleccionados) {
                    foreach ($telefonosSeleccionados as $telefono) {
                        if ($telefono === 'Sin teléfono') {
                            $q->orWhereNull('telefono')->orWhere('telefono', '');
                        } else {
                            $q->orWhere('telefono', $telefono);
                        }
                    }
                });
            }
        }

        // **FILTROS RÁPIDOS**
        if ($request->filled('rapido') && !empty(trim($request->get('rapido')))) {
            $filtroRapido = trim($request->get('rapido'));
            \Log::info('⚡ Aplicando filtro rápido:', ['filtro' => $filtroRapido]);
            
            switch ($filtroRapido) {
                case 'recientes':
                    $query->whereDate('created_at', '>=', now()->subDays(30));
                    break;
                    
                case 'activos':
                    $query->whereHas('cotizaciones', function($cotQuery) {
                        $cotQuery->whereIn('estado', ['Pendiente', 'Enviada', 'En Revisión']);
                    });
                    break;
            }
        }

        // **ORDENAMIENTO**
        $sortField = $request->get('sort', 'nombre_institucion');
        $sortDirection = $request->get('direction', 'asc');
        
        \Log::info('🔄 Aplicando ordenamiento:', [
            'campo' => $sortField, 
            'direccion' => $sortDirection
        ]);

        // Mapeo seguro de campos
        $camposPermitidos = [
            'nombre_institucion' => 'nombre_institucion',
            'nombre' => 'nombre_institucion',
            'rut' => 'rut',
            'tipo_cliente' => 'tipo_cliente',
            'tipo' => 'tipo_cliente',
            'email' => 'email',
            'telefono' => 'telefono',
            'created_at' => 'created_at',
            'nombre_contacto' => 'nombre_contacto',
            'contacto' => 'nombre_contacto'
        ];

        $campoReal = $camposPermitidos[$sortField] ?? 'nombre_institucion';
        $direccionReal = in_array(strtolower($sortDirection), ['asc', 'desc']) ? $sortDirection : 'asc';
        
        $query->orderBy($campoReal, $direccionReal);

        // **CONTAR ANTES DE PAGINAR**
        $totalFiltrado = $query->count();
        \Log::info('📊 Total después de filtros:', ['total' => $totalFiltrado]);

        // **PAGINACIÓN**
        $perPage = min($request->get('per_page', 50), 200);
        $clientes = $query->paginate($perPage);

        // **ESTADÍSTICAS GLOBALES** (sin filtros)
        $estadisticas = [
            'total' => Cliente::count(),
            'publicos' => Cliente::where('tipo_cliente', 'Cliente Público')->count(),
            'privados' => Cliente::where('tipo_cliente', 'Cliente Privado')->count(),
            'revendedores' => Cliente::where('tipo_cliente', 'Revendedor')->count()
        ];

        // **FORMATEAR DATOS**
        $clientesFormateados = $clientes->through(function ($cliente) {
            return [
                'id' => $cliente->id,
                'nombre_institucion' => $cliente->nombre_institucion,
                'rut' => $cliente->rut ?: '',
                'tipo_cliente' => $cliente->tipo_cliente ?: '',
                'nombre_contacto' => $cliente->nombre_contacto ?: '',
                'email' => $cliente->email,
                'telefono' => $cliente->telefono ?: '',
                'direccion' => $cliente->direccion ?: '',
                'created_at' => $cliente->created_at->format('d/m/Y')
            ];
        });

        \Log::info('✅ Clientes cargados exitosamente:', [
            'total_db' => $estadisticas['total'],
            'total_filtrado' => $totalFiltrado,
            'pagina_actual' => $clientesFormateados->currentPage(),
            'por_pagina' => $clientesFormateados->perPage(),
            'mostrando' => $clientesFormateados->count()
        ]);

        return response()->json([
            'success' => true,
            'data' => $clientesFormateados,
            'estadisticas' => $estadisticas,
            'filtros_aplicados' => [
                'total_filtrado' => $totalFiltrado,
                'busqueda' => $request->get('busqueda'),
                'tipo_cliente' => $request->get('tipo_cliente'),
                'rapido' => $request->get('rapido')
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('❌ Error al cargar clientes:', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'request' => $request->all()
        ]);
        
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
    // TEMPORAL: Permitir creación sin autenticación para testing
    // TODO: Restaurar permisos cuando tengamos login funcionando
    /*
    if (!Auth::user()->esAdministrador() && !Auth::user()->esJefe()) {
        abort(403, 'No tienes permisos para crear clientes');
    }
    */
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
 * Buscar clientes para autocompletado (API)
 */
public function buscarClientes(Request $request)
{
    try {
        $query = $request->get('q', '');
        
        \Log::info('🔍 Búsqueda de clientes iniciada:', [
            'query' => $query,
            'request_all' => $request->all()
        ]);
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $clientes = \App\Models\Cliente::where(function($q) use ($query) {
            $q->where('nombre_institucion', 'like', "%{$query}%")
              ->orWhere('rut', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('nombre_contacto', 'like', "%{$query}%");
        })
        ->select('id', 'nombre_institucion', 'rut', 'email', 'telefono', 'tipo_cliente', 'nombre_contacto')
        ->limit(10)
        ->get();

        \Log::info('✅ Resultados de búsqueda de clientes:', [
            'query' => $query,
            'resultados_count' => $clientes->count(),
            'resultados' => $clientes->toArray()
        ]);

        return response()->json($clientes);

    } catch (\Exception $e) {
        \Log::error('❌ Error en búsqueda de clientes:', [
            'error' => $e->getMessage(),
            'query' => $request->get('q'),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        return response()->json([
            'error' => 'Error en la búsqueda: ' . $e->getMessage()
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
    // En ClienteController.php - AGREGAR estos métodos al final de la clase:

        /**
         * Verificar si un RUT ya existe
         * GET /api/clientes/verificar-rut?rut=12345678-9&exclude_id=1
         */
        public function verificarRut(Request $request)
        {
            try {
                $rut = $request->get('rut');
                $excludeId = $request->get('exclude_id');
                
                \Log::info('🔍 Verificando RUT:', ['rut' => $rut, 'exclude_id' => $excludeId]);
                
                if (empty($rut)) {
                    return response()->json([
                        'success' => true,
                        'existe' => false,
                        'disponible' => true,
                        'message' => 'RUT no proporcionado'
                    ]);
                }
                
                // Limpiar el RUT (remover puntos y espacios)
                $rutLimpio = preg_replace('/[^0-9kK-]/', '', $rut);
                
                $query = Cliente::where('rut', $rutLimpio);
                
                // Excluir cliente actual en caso de edición
                if ($excludeId) {
                    $query->where('id', '!=', $excludeId);
                }
                
                $cliente = $query->first();
                
                if ($cliente) {
                    \Log::info('⚠️ RUT duplicado encontrado:', ['cliente_id' => $cliente->id]);
                    
                    return response()->json([
                        'success' => true,
                        'existe' => true,
                        'disponible' => false,
                        'message' => 'RUT ya registrado',
                        'cliente' => [
                            'id' => $cliente->id,
                            'nombre_institucion' => $cliente->nombre_institucion,
                            'rut' => $cliente->rut,
                            'email' => $cliente->email
                        ]
                    ]);
                }
                
                \Log::info('✅ RUT disponible');
                return response()->json([
                    'success' => true,
                    'existe' => false,
                    'disponible' => true,
                    'message' => 'RUT disponible'
                ]);
                
            } catch (\Exception $e) {
                \Log::error('❌ Error al verificar RUT:', [
                    'error' => $e->getMessage(),
                    'rut' => $request->get('rut')
                ]);
                
                return response()->json([
                    'success' => false,
                    'existe' => false,
                    'disponible' => true,
                    'message' => 'Error al verificar RUT: ' . $e->getMessage()
                ], 500);
            }
        }

        /**
         * Verificar si un email ya existe
         * GET /api/clientes/verificar-email?email=test@example.com&exclude_id=1
         */
        public function verificarEmail(Request $request)
        {
            try {
                $email = $request->get('email');
                $excludeId = $request->get('exclude_id');
                
                \Log::info('🔍 Verificando Email:', ['email' => $email, 'exclude_id' => $excludeId]);
                
                if (empty($email)) {
                    return response()->json([
                        'success' => true,
                        'existe' => false,
                        'disponible' => true,
                        'message' => 'Email no proporcionado'
                    ]);
                }
                
                // Validar formato de email
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return response()->json([
                        'success' => true,
                        'existe' => false,
                        'disponible' => false,
                        'message' => 'Formato de email inválido'
                    ]);
                }
                
                $query = Cliente::where('email', $email);
                
                // Excluir cliente actual en caso de edición
                if ($excludeId) {
                    $query->where('id', '!=', $excludeId);
                }
                
                $cliente = $query->first();
                
                if ($cliente) {
                    \Log::info('⚠️ Email duplicado encontrado:', ['cliente_id' => $cliente->id]);
                    
                    return response()->json([
                        'success' => true,
                        'existe' => true,
                        'disponible' => false,
                        'message' => 'Email ya registrado',
                        'cliente' => [
                            'id' => $cliente->id,
                            'nombre_institucion' => $cliente->nombre_institucion,
                            'rut' => $cliente->rut,
                            'email' => $cliente->email
                        ]
                    ]);
                }
                
                \Log::info('✅ Email disponible');
                return response()->json([
                    'success' => true,
                    'existe' => false,
                    'disponible' => true,
                    'message' => 'Email disponible'
                ]);
                
            } catch (\Exception $e) {
                \Log::error('❌ Error al verificar Email:', [
                    'error' => $e->getMessage(),
                    'email' => $request->get('email')
                ]);
                
                return response()->json([
                    'success' => false,
                    'existe' => false,
                    'disponible' => true,
                    'message' => 'Error al verificar email: ' . $e->getMessage()
                ], 500);
            }
        }
}