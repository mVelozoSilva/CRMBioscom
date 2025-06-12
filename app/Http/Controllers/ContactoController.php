<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ContactoController extends Controller
{
    /**
     * Lista todos los contactos con filtros avanzados
     * Incluye paginación y búsqueda inteligente
     */
    public function index(Request $request)
    {
        $query = Contacto::with(['cliente']);

        // Búsqueda general
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%")
                  ->orWhere('cargo', 'like', "%{$search}%")
                  ->orWhere('area', 'like', "%{$search}%")
                  ->orWhereHas('cliente', function($subQ) use ($search) {
                      $subQ->where('nombre_institucion', 'like', "%{$search}%")
                           ->orWhere('rut', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro por cliente
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        // Filtro por área
        if ($request->filled('area')) {
            $query->where('area', $request->area);
        }

        // Filtro por tipo de cliente
        if ($request->filled('tipo_cliente')) {
            $query->whereHas('cliente', function($q) use ($request) {
                $q->where('tipo_cliente', $request->tipo_cliente);
            });
        }

        // Filtro por contactos con email
        if ($request->filled('con_email')) {
            if ($request->con_email === 'si') {
                $query->whereNotNull('email')->where('email', '!=', '');
            } else {
                $query->where(function($q) {
                    $q->whereNull('email')->orWhere('email', '');
                });
            }
        }

        // Filtro por contactos con teléfono
        if ($request->filled('con_telefono')) {
            if ($request->con_telefono === 'si') {
                $query->whereNotNull('telefono')->where('telefono', '!=', '');
            } else {
                $query->where(function($q) {
                    $q->whereNull('telefono')->orWhere('telefono', '');
                });
            }
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'nombre');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        $allowedSorts = ['nombre', 'cargo', 'email', 'telefono', 'area', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            // Ordenamiento por cliente si se solicita
            if ($sortBy === 'cliente') {
                $query->join('clientes', 'contactos.cliente_id', '=', 'clientes.id')
                      ->orderBy('clientes.nombre_institucion', $sortDirection)
                      ->select('contactos.*');
            }
        }

        $contactos = $query->paginate(20);

        // Obtener datos para filtros
        $clientes = Cliente::orderBy('nombre_institucion')->get();
        $areas = Contacto::distinct()->whereNotNull('area')->pluck('area')->filter()->sort();
        $tiposCliente = Cliente::distinct()->whereNotNull('tipo_cliente')->pluck('tipo_cliente')->filter();

        // Estadísticas para la vista
        $estadisticas = [
            'total_contactos' => Contacto::count(),
            'con_email' => Contacto::whereNotNull('email')->where('email', '!=', '')->count(),
            'con_telefono' => Contacto::whereNotNull('telefono')->where('telefono', '!=', '')->count(),
            'areas_diferentes' => $areas->count(),
            'contactos_hoy' => Contacto::whereDate('created_at', today())->count()
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'contactos' => $contactos,
                'estadisticas' => $estadisticas
            ]);
        }

        return view('contactos.index', compact('contactos', 'clientes', 'areas', 'tiposCliente', 'estadisticas'));
    }

    /**
     * Almacena un nuevo contacto con validación avanzada
     * Previene duplicados y valida datos de contacto
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cliente_id' => 'required|exists:clientes,id',
            'nombre' => 'required|string|max:255',
            'cargo' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'notas' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verificar duplicados por email si se proporciona
            if ($request->filled('email')) {
                $duplicadoEmail = Contacto::where('email', $request->email)
                    ->where('cliente_id', '!=', $request->cliente_id)
                    ->exists();
                
                if ($duplicadoEmail) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe un contacto con este email en otro cliente.',
                        'tipo_error' => 'duplicado_email'
                    ], 422);
                }
            }

            // Verificar duplicados por nombre y cliente
            $duplicadoNombre = Contacto::where('nombre', $request->nombre)
                ->where('cliente_id', $request->cliente_id)
                ->exists();
            
            if ($duplicadoNombre) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un contacto con este nombre para este cliente.',
                    'tipo_error' => 'duplicado_nombre'
                ], 422);
            }

            $contacto = Contacto::create([
                'cliente_id' => $request->cliente_id,
                'nombre' => $request->nombre,
                'cargo' => $request->cargo,
                'email' => $request->email,
                'telefono' => $this->formatearTelefono($request->telefono),
                'area' => $request->area,
                'notas' => $request->notas
            ]);

            // Cargar relación para respuesta
            $contacto->load('cliente');

            Log::info("Contacto creado: {$contacto->nombre} para cliente {$contacto->cliente->nombre_institucion}");

            return response()->json([
                'success' => true,
                'message' => 'Contacto creado exitosamente.',
                'contacto' => $contacto
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error al crear contacto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al crear el contacto.'
            ], 500);
        }
    }

    /**
     * Muestra los detalles de un contacto específico
     * Incluye historial de interacciones
     */
    public function show(Contacto $contacto)
    {
        try {
            $contacto->load(['cliente']);

            // Obtener cotizaciones relacionadas
            $cotizaciones = DB::table('cotizaciones')
                ->where('cliente_id', $contacto->cliente_id)
                ->where('nombre_contacto', 'like', "%{$contacto->nombre}%")
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Obtener seguimientos relacionados
            $seguimientos = DB::table('seguimientos')
                ->where('cliente_id', $contacto->cliente_id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Estadísticas del contacto
            $estadisticas = [
                'total_cotizaciones' => DB::table('cotizaciones')
                    ->where('cliente_id', $contacto->cliente_id)
                    ->where('nombre_contacto', 'like', "%{$contacto->nombre}%")
                    ->count(),
                'ultima_cotizacion' => DB::table('cotizaciones')
                    ->where('cliente_id', $contacto->cliente_id)
                    ->where('nombre_contacto', 'like', "%{$contacto->nombre}%")
                    ->orderBy('created_at', 'desc')
                    ->value('created_at'),
                'valor_total_cotizaciones' => DB::table('cotizaciones')
                    ->where('cliente_id', $contacto->cliente_id)
                    ->where('nombre_contacto', 'like', "%{$contacto->nombre}%")
                    ->sum('total_con_iva')
            ];

            return response()->json([
                'success' => true,
                'contacto' => $contacto,
                'cotizaciones' => $cotizaciones,
                'seguimientos' => $seguimientos,
                'estadisticas' => $estadisticas
            ]);

        } catch (\Exception $e) {
            Log::error("Error al mostrar contacto {$contacto->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los detalles del contacto.'
            ], 500);
        }
    }

    /**
     * Actualiza un contacto existente
     * Mantiene validaciones de duplicados
     */
    public function update(Request $request, Contacto $contacto)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'cargo' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'notas' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verificar duplicados por email si se proporciona y cambió
            if ($request->filled('email') && $request->email !== $contacto->email) {
                $duplicadoEmail = Contacto::where('email', $request->email)
                    ->where('id', '!=', $contacto->id)
                    ->where('cliente_id', '!=', $contacto->cliente_id)
                    ->exists();
                
                if ($duplicadoEmail) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe un contacto con este email en otro cliente.',
                        'tipo_error' => 'duplicado_email'
                    ], 422);
                }
            }

            // Verificar duplicados por nombre y cliente si cambió el nombre
            if ($request->nombre !== $contacto->nombre) {
                $duplicadoNombre = Contacto::where('nombre', $request->nombre)
                    ->where('cliente_id', $contacto->cliente_id)
                    ->where('id', '!=', $contacto->id)
                    ->exists();
                
                if ($duplicadoNombre) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe un contacto con este nombre para este cliente.',
                        'tipo_error' => 'duplicado_nombre'
                    ], 422);
                }
            }

            $contacto->update([
                'nombre' => $request->nombre,
                'cargo' => $request->cargo,
                'email' => $request->email,
                'telefono' => $this->formatearTelefono($request->telefono),
                'area' => $request->area,
                'notas' => $request->notas
            ]);

            // Recargar relación
            $contacto->load('cliente');

            Log::info("Contacto actualizado: {$contacto->nombre} (ID: {$contacto->id})");

            return response()->json([
                'success' => true,
                'message' => 'Contacto actualizado exitosamente.',
                'contacto' => $contacto
            ]);

        } catch (\Exception $e) {
            Log::error("Error al actualizar contacto {$contacto->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al actualizar el contacto.'
            ], 500);
        }
    }

    /**
     * Elimina un contacto
     * Verifica dependencias antes de eliminar
     */
    public function destroy(Contacto $contacto)
    {
        try {
            // Verificar si el contacto está siendo usado en cotizaciones
            $cotizacionesActivas = DB::table('cotizaciones')
                ->where('cliente_id', $contacto->cliente_id)
                ->where('nombre_contacto', 'like', "%{$contacto->nombre}%")
                ->whereIn('estado', ['Pendiente', 'En Proceso'])
                ->count();

            if ($cotizacionesActivas > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el contacto porque está asociado a cotizaciones activas.',
                    'tipo_error' => 'dependencias_activas',
                    'dependencias' => $cotizacionesActivas
                ], 422);
            }

            $nombreContacto = $contacto->nombre;
            $nombreCliente = $contacto->cliente->nombre_institucion;

            $contacto->delete();

            Log::info("Contacto eliminado: {$nombreContacto} de {$nombreCliente}");

            return response()->json([
                'success' => true,
                'message' => 'Contacto eliminado exitosamente.'
            ]);

        } catch (\Exception $e) {
            Log::error("Error al eliminar contacto {$contacto->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al eliminar el contacto.'
            ], 500);
        }
    }

    /**
     * Obtiene contactos por cliente específico
     * Endpoint optimizado para formularios
     */
    public function porCliente(Cliente $cliente)
    {
        try {
            $contactos = Contacto::where('cliente_id', $cliente->id)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'cargo', 'email', 'telefono', 'area']);

            return response()->json([
                'success' => true,
                'contactos' => $contactos,
                'total' => $contactos->count()
            ]);

        } catch (\Exception $e) {
            Log::error("Error al obtener contactos del cliente {$cliente->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los contactos del cliente.'
            ], 500);
        }
    }

    /**
     * Obtiene las áreas disponibles para filtros
     * Usado para autocompletado
     */
    public function areas()
    {
        try {
            $areas = Contacto::distinct()
                ->whereNotNull('area')
                ->where('area', '!=', '')
                ->pluck('area')
                ->sort()
                ->values();

            return response()->json([
                'success' => true,
                'areas' => $areas
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener áreas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las áreas.'
            ], 500);
        }
    }

    /**
     * Búsqueda avanzada de contactos
     * Endpoint para autocompletado en formularios
     */
    public function buscar(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $clienteId = $request->get('cliente_id');
            $area = $request->get('area');
            $limite = $request->get('limit', 10);

            $contactos = Contacto::with(['cliente:id,nombre_institucion'])
                ->where(function($q) use ($query) {
                    if ($query) {
                        $q->where('nombre', 'like', "%{$query}%")
                          ->orWhere('email', 'like', "%{$query}%")
                          ->orWhere('cargo', 'like', "%{$query}%");
                    }
                })
                ->when($clienteId, function($q) use ($clienteId) {
                    $q->where('cliente_id', $clienteId);
                })
                ->when($area, function($q) use ($area) {
                    $q->where('area', $area);
                })
                ->orderBy('nombre')
                ->limit($limite)
                ->get()
                ->map(function($contacto) {
                    return [
                        'id' => $contacto->id,
                        'nombre' => $contacto->nombre,
                        'cargo' => $contacto->cargo,
                        'email' => $contacto->email,
                        'telefono' => $contacto->telefono,
                        'area' => $contacto->area,
                        'cliente' => [
                            'id' => $contacto->cliente->id,
                            'nombre' => $contacto->cliente->nombre_institucion
                        ],
                        'texto_completo' => $contacto->nombre . ' - ' . $contacto->cliente->nombre_institucion . 
                                          ($contacto->cargo ? ' (' . $contacto->cargo . ')' : '')
                    ];
                });

            return response()->json([
                'success' => true,
                'contactos' => $contactos,
                'total' => $contactos->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error en búsqueda de contactos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda de contactos.'
            ], 500);
        }
    }

    /**
     * Exporta contactos a diferentes formatos
     * Soporta CSV, Excel y PDF
     */
    public function exportar(Request $request)
    {
        try {
            $formato = $request->get('formato', 'csv');
            $filtros = $request->only(['search', 'cliente_id', 'area', 'tipo_cliente']);

            // Aplicar los mismos filtros que en index
            $query = Contacto::with(['cliente']);

            if (!empty($filtros['search'])) {
                $search = $filtros['search'];
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('telefono', 'like', "%{$search}%")
                      ->orWhere('cargo', 'like', "%{$search}%")
                      ->orWhere('area', 'like', "%{$search}%")
                      ->orWhereHas('cliente', function($subQ) use ($search) {
                          $subQ->where('nombre_institucion', 'like', "%{$search}%");
                      });
                });
            }

            if (!empty($filtros['cliente_id'])) {
                $query->where('cliente_id', $filtros['cliente_id']);
            }

            if (!empty($filtros['area'])) {
                $query->where('area', $filtros['area']);
            }

            if (!empty($filtros['tipo_cliente'])) {
                $query->whereHas('cliente', function($q) use ($filtros) {
                    $q->where('tipo_cliente', $filtros['tipo_cliente']);
                });
            }

            $contactos = $query->orderBy('nombre')->get();

            switch ($formato) {
                case 'csv':
                    return $this->exportarCSV($contactos);
                case 'excel':
                    return $this->exportarExcel($contactos);
                case 'pdf':
                    return $this->exportarPDF($contactos);
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Formato de exportación no válido.'
                    ], 422);
            }

        } catch (\Exception $e) {
            Log::error('Error al exportar contactos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar la exportación.'
            ], 500);
        }
    }

    /**
     * Métodos auxiliares privados
     */

    /**
     * Formatea número telefónico para consistencia
     */
    private function formatearTelefono($telefono)
    {
        if (!$telefono) return null;
        
        // Eliminar caracteres no numéricos excepto + al inicio
        $telefono = preg_replace('/[^\d+]/', '', $telefono);
        
        // Si no tiene código de país, agregar +56 para Chile
        if (!str_starts_with($telefono, '+')) {
            if (strlen($telefono) === 9) {
                $telefono = '+56' . $telefono;
            } elseif (strlen($telefono) === 8) {
                $telefono = '+569' . $telefono; // Móvil
            }
        }
        
        return $telefono;
    }

    /**
     * Exporta a formato CSV
     */
    private function exportarCSV($contactos)
    {
        $filename = 'contactos_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($contactos) {
            $file = fopen('php://output', 'w');
            
            // Encabezados
            fputcsv($file, [
                'ID',
                'Nombre',
                'Cargo',
                'Email',
                'Teléfono',
                'Área',
                'Cliente',
                'Tipo Cliente',
                'Notas',
                'Fecha Creación'
            ]);

            // Datos
            foreach ($contactos as $contacto) {
                fputcsv($file, [
                    $contacto->id,
                    $contacto->nombre,
                    $contacto->cargo,
                    $contacto->email,
                    $contacto->telefono,
                    $contacto->area,
                    $contacto->cliente->nombre_institucion,
                    $contacto->cliente->tipo_cliente,
                    $contacto->notas,
                    $contacto->created_at->format('d/m/Y H:i')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exporta a formato Excel (simplificado como CSV)
     */
    private function exportarExcel($contactos)
    {
        // Para una implementación completa se podría usar PhpSpreadsheet
        return $this->exportarCSV($contactos);
    }

    /**
     * Exporta a formato PDF (simplificado)
     */
    private function exportarPDF($contactos)
    {
        // Para una implementación completa se podría usar DOMPDF o similar
        return response()->json([
            'success' => false,
            'message' => 'Exportación a PDF en desarrollo.'
        ], 422);
    }
}