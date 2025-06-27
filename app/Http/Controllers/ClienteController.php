<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Contacto;
use App\Http\Requests\ClienteStoreRequest;
use App\Http\Requests\ClienteUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getClientesAjax($request);
        }

        $clientes = Cliente::with(['contactos'])
            ->orderBy('nombre_institucion', 'asc')
            ->paginate(20);

        $estadisticas = [
            'total_clientes' => Cliente::count(),
            'por_tipo' => [
                'Cliente Público' => Cliente::where('tipo_cliente', 'Cliente Público')->count(),
                'Cliente Privado' => Cliente::where('tipo_cliente', 'Cliente Privado')->count(),
                'Revendedor' => Cliente::where('tipo_cliente', 'Revendedor')->count(),
            ],
            'con_mas_cotizaciones' => collect()
        ];

        return view('clientes.index', compact('clientes', 'estadisticas'));
    }

    private function getClientesAjax(Request $request)
    {
        try {
            $query = Cliente::query();

            if ($request->filled('busqueda')) {
                $busqueda = trim($request->get('busqueda'));
                $query->where(function ($q) use ($busqueda) {
                    $q->where('nombre_institucion', 'like', "%{$busqueda}%")
                      ->orWhere('rut', 'like', "%{$busqueda}%")
                      ->orWhere('email', 'like', "%{$busqueda}%")
                      ->orWhere('nombre_contacto', 'like', "%{$busqueda}%");
                });
            }

            if ($request->filled('tipo_cliente')) {
                $query->where('tipo_cliente', $request->get('tipo_cliente'));
            }

            $query->orderBy(
                $request->get('sort', 'nombre_institucion'),
                $request->get('direction', 'asc')
            );

            $clientes = $query->paginate($request->get('per_page', 50));

            return response()->json([
                'success' => true,
                'data' => $clientes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener clientes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(ClienteStoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();

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

            // Bloque comentado temporalmente para pruebas sin autenticación
            // if (Auth::check() && Auth::user()->esVendedor()) {
            //     $vendedoresArray = $validated['vendedores_a_cargo'] ?? [];
            //     if (!in_array(Auth::id(), $vendedoresArray)) {
            //         $vendedoresArray[] = Auth::id();
            //     }
            //     $validated['vendedores_a_cargo'] = $vendedoresArray;
            // }

            $cliente = Cliente::create($validated);

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
                    'data' => $cliente->toArray()
                ]);
            }

            return redirect()->route('clientes.show', $cliente)
                ->with('success', 'Cliente creado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear cliente: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->withErrors([
                'error' => 'Error al crear cliente: ' . $e->getMessage()
            ]);
        }
    }
}