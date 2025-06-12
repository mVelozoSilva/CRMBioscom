<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ContactoController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET /api/contactos
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Contacto::with(['cliente']);
            
            // Filtro por cliente si se especifica
            if ($request->has('cliente_id') && $request->cliente_id) {
                $query->where('cliente_id', $request->cliente_id);
            }
            
            // Búsqueda por nombre, cargo o email
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('cargo', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('area', 'like', "%{$search}%");
                });
            }
            
            // Filtro por área
            if ($request->has('area') && $request->area) {
                $query->where('area', $request->area);
            }
            
            // Ordenamiento
            $orderBy = $request->get('order_by', 'nombre');
            $orderDirection = $request->get('order_direction', 'asc');
            $query->orderBy($orderBy, $orderDirection);
            
            // Paginación
            $perPage = $request->get('per_page', 15);
            $contactos = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $contactos,
                'message' => 'Contactos obtenidos exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener contactos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * POST /api/contactos
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cliente_id' => 'required|exists:clientes,id',
                'nombre' => 'required|string|max:255',
                'cargo' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'telefono' => 'nullable|string|max:255',
                'area' => 'nullable|string|max:255',
                'notas' => 'nullable|string'
            ]);

            // Verificar duplicados por email si se proporciona
            if (!empty($validated['email'])) {
                $existe = Contacto::where('email', $validated['email'])
                                 ->where('cliente_id', $validated['cliente_id'])
                                 ->exists();
                
                if ($existe) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe un contacto con este email para este cliente',
                        'errors' => ['email' => ['El email ya está registrado para este cliente']]
                    ], 422);
                }
            }

            $contacto = Contacto::create($validated);
            $contacto->load('cliente');

            return response()->json([
                'success' => true,
                'data' => $contacto,
                'message' => 'Contacto creado exitosamente'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear contacto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * GET /api/contactos/{contacto}
     */
    public function show(Contacto $contacto): JsonResponse
    {
        try {
            $contacto->load('cliente');
            
            return response()->json([
                'success' => true,
                'data' => $contacto,
                'message' => 'Contacto obtenido exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener contacto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     * PUT /api/contactos/{contacto}
     */
    public function update(Request $request, Contacto $contacto): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cliente_id' => 'sometimes|required|exists:clientes,id',
                'nombre' => 'sometimes|required|string|max:255',
                'cargo' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'telefono' => 'nullable|string|max:255',
                'area' => 'nullable|string|max:255',
                'notas' => 'nullable|string'
            ]);

            // Verificar duplicados por email si se está actualizando
            if (isset($validated['email']) && !empty($validated['email'])) {
                $clienteId = $validated['cliente_id'] ?? $contacto->cliente_id;
                $existe = Contacto::where('email', $validated['email'])
                                 ->where('cliente_id', $clienteId)
                                 ->where('id', '!=', $contacto->id)
                                 ->exists();
                
                if ($existe) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe un contacto con este email para este cliente',
                        'errors' => ['email' => ['El email ya está registrado para este cliente']]
                    ], 422);
                }
            }

            $contacto->update($validated);
            $contacto->load('cliente');

            return response()->json([
                'success' => true,
                'data' => $contacto,
                'message' => 'Contacto actualizado exitosamente'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar contacto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/contactos/{contacto}
     */
    public function destroy(Contacto $contacto): JsonResponse
    {
        try {
            $contacto->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contacto eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar contacto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get contacts for a specific client.
     * GET /api/contactos/cliente/{cliente}
     */
    public function porCliente(Cliente $cliente): JsonResponse
    {
        try {
            $contactos = Contacto::where('cliente_id', $cliente->id)
                               ->orderBy('nombre')
                               ->get();

            return response()->json([
                'success' => true,
                'data' => $contactos,
                'message' => 'Contactos del cliente obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener contactos del cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unique areas from contacts.
     * GET /api/contactos/areas
     */
    public function areas(): JsonResponse
    {
        try {
            $areas = Contacto::whereNotNull('area')
                           ->where('area', '!=', '')
                           ->distinct()
                           ->pluck('area')
                           ->sort()
                           ->values();

            return response()->json([
                'success' => true,
                'data' => $areas,
                'message' => 'Áreas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener áreas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Advanced search for contacts.
     * GET /api/buscar-contactos
     */
    public function buscar(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');
            
            if (empty($query)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Consulta vacía'
                ]);
            }

            $contactos = Contacto::with(['cliente'])
                               ->where(function($q) use ($query) {
                                   $q->where('nombre', 'like', "%{$query}%")
                                     ->orWhere('cargo', 'like', "%{$query}%")
                                     ->orWhere('email', 'like', "%{$query}%")
                                     ->orWhere('area', 'like', "%{$query}%")
                                     ->orWhereHas('cliente', function($clienteQuery) use ($query) {
                                         $clienteQuery->where('nombre_institucion', 'like', "%{$query}%")
                                                     ->orWhere('rut', 'like', "%{$query}%");
                                     });
                               })
                               ->orderBy('nombre')
                               ->limit(20)
                               ->get();

            return response()->json([
                'success' => true,
                'data' => $contactos,
                'message' => 'Búsqueda realizada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get contact statistics.
     * GET /api/contactos/estadisticas
     */
    public function estadisticas(): JsonResponse
    {
        try {
            $stats = [
                'total_contactos' => Contacto::count(),
                'contactos_con_email' => Contacto::whereNotNull('email')->where('email', '!=', '')->count(),
                'contactos_con_telefono' => Contacto::whereNotNull('telefono')->where('telefono', '!=', '')->count(),
                'contactos_por_area' => Contacto::whereNotNull('area')
                                              ->where('area', '!=', '')
                                              ->groupBy('area')
                                              ->selectRaw('area, count(*) as total')
                                              ->orderBy('total', 'desc')
                                              ->limit(10)
                                              ->get(),
                'clientes_con_multiples_contactos' => Contacto::selectRaw('cliente_id, count(*) as total')
                                                            ->groupBy('cliente_id')
                                                            ->having('total', '>', 1)
                                                            ->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Estadísticas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk operations for contacts.
     * POST /api/contactos/bulk
     */
    public function bulk(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'action' => 'required|in:delete,update_area,update_cliente',
                'contacto_ids' => 'required|array',
                'contacto_ids.*' => 'exists:contactos,id',
                'area' => 'required_if:action,update_area|nullable|string|max:255',
                'cliente_id' => 'required_if:action,update_cliente|nullable|exists:clientes,id'
            ]);

            $contactos = Contacto::whereIn('id', $validated['contacto_ids']);
            $count = $contactos->count();

            switch ($validated['action']) {
                case 'delete':
                    $contactos->delete();
                    $message = "{$count} contactos eliminados exitosamente";
                    break;

                case 'update_area':
                    $contactos->update(['area' => $validated['area']]);
                    $message = "{$count} contactos actualizados con nueva área";
                    break;

                case 'update_cliente':
                    $contactos->update(['cliente_id' => $validated['cliente_id']]);
                    $message = "{$count} contactos reasignados al nuevo cliente";
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => ['affected_count' => $count],
                'message' => $message
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en operación masiva: ' . $e->getMessage()
            ], 500);
        }
    }
}