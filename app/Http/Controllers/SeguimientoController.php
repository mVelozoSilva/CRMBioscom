<?php

namespace App\Http\Controllers;

use App\Models\Seguimiento;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Cotizacion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SeguimientosImport;

class SeguimientoController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getSeguimientos($request);
        }

        $vendedores = User::select('id', 'name')->get();
        return view('seguimiento.index', compact('vendedores'));
    }

    public function getSeguimientos(Request $request): JsonResponse
    {
        try {
            $query = Seguimiento::with(['cliente', 'cotizacion', 'vendedor'])
                               ->select('seguimientos.*');

            // Filtros
            if ($request->has('filtro')) {
                switch ($request->filtro) {
                    case 'atrasados':
                        $query->atrasados();
                        break;
                    case 'proximos':
                        $query->proximos(7);
                        break;
                    case 'todos':
                        // Sin filtro adicional
                        break;
                }
            }

            if ($request->filled('vendedor_id')) {
                $query->porVendedor($request->vendedor_id);
            }

            if ($request->filled('estado')) {
                $query->porEstado($request->estado);
            }

            if ($request->filled('prioridad')) {
                $query->porPrioridad($request->prioridad);
            }

            if ($request->filled('buscar_cliente')) {
                $query->buscarCliente($request->buscar_cliente);
            }

            if ($request->filled('fecha_desde')) {
                $query->where('proxima_gestion', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->where('proxima_gestion', '<=', $request->fecha_hasta);
            }

            // Ordenamiento
            $query->orderBy('proxima_gestion', 'asc')
                  ->orderBy('prioridad', 'desc');

            $seguimientos = $query->paginate(50);

            // Agregar atributos calculados
            $seguimientos->getCollection()->transform(function ($seguimiento) {
                $seguimiento->estado_color = $seguimiento->estado_color;
                $seguimiento->prioridad_color = $seguimiento->prioridad_color;
                $seguimiento->dias_restantes = $seguimiento->dias_restantes;
                return $seguimiento;
            });

            return response()->json([
                'success' => true,
                'data' => $seguimientos,
                'stats' => $this->getEstadisticas($request)
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener seguimientos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los seguimientos'
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'vendedor_id' => 'required|exists:users,id',
            'proxima_gestion' => 'required|date|after_or_equal:today',
            'estado' => 'in:pendiente,en_proceso,completado,vencido,reprogramado',
            'prioridad' => 'in:baja,media,alta,urgente',
            'cotizacion_id' => 'nullable|exists:cotizaciones,id',
            'notas' => 'nullable|string|max:1000',
        ]);

        try {
            $seguimiento = Seguimiento::create($request->all());
            $seguimiento->load(['cliente', 'cotizacion', 'vendedor']);

            return response()->json([
                'success' => true,
                'message' => 'Seguimiento creado exitosamente',
                'data' => $seguimiento
            ]);

        } catch (\Exception $e) {
            Log::error('Error al crear seguimiento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el seguimiento'
            ], 500);
        }
    }

    public function update(Request $request, Seguimiento $seguimiento): JsonResponse
    {
        $request->validate([
            'estado' => 'in:pendiente,en_proceso,completado,vencido,reprogramado',
            'prioridad' => 'in:baja,media,alta,urgente',
            'proxima_gestion' => 'date',
            'ultima_gestion' => 'nullable|date',
            'notas' => 'nullable|string|max:1000',
            'resultado_ultima_gestion' => 'nullable|string|max:1000',
        ]);

        try {
            // Si se está marcando como completado, actualizar ultima_gestion
            if ($request->estado === 'completado' && !$request->has('ultima_gestion')) {
                $request->merge(['ultima_gestion' => Carbon::today()]);
            }

            $seguimiento->update($request->all());
            $seguimiento->load(['cliente', 'cotizacion', 'vendedor']);

            return response()->json([
                'success' => true,
                'message' => 'Seguimiento actualizado exitosamente',
                'data' => $seguimiento
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar seguimiento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el seguimiento'
            ], 500);
        }
    }

    public function updateMasivo(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:seguimientos,id',
            'estado' => 'nullable|in:pendiente,en_proceso,completado,vencido,reprogramado',
            'prioridad' => 'nullable|in:baja,media,alta,urgente',
            'proxima_gestion' => 'nullable|date',
            'vendedor_id' => 'nullable|exists:users,id',
        ]);

        try {
            $actualizaciones = array_filter([
                'estado' => $request->estado,
                'prioridad' => $request->prioridad,
                'proxima_gestion' => $request->proxima_gestion,
                'vendedor_id' => $request->vendedor_id,
                'updated_at' => now(),
            ]);

            // Si se marca como completado, actualizar ultima_gestion
            if ($request->estado === 'completado') {
                $actualizaciones['ultima_gestion'] = Carbon::today();
            }

            $cantidadActualizada = Seguimiento::whereIn('id', $request->ids)
                                             ->update($actualizaciones);

            return response()->json([
                'success' => true,
                'message' => "{$cantidadActualizada} registros actualizados exitosamente",
                'cantidad' => $cantidadActualizada
            ]);

        } catch (\Exception $e) {
            Log::error('Error en actualización masiva: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en la actualización masiva'
            ], 500);
        }
    }

    public function destroy(Seguimiento $seguimiento): JsonResponse
    {
        try {
            $seguimiento->delete();

            return response()->json([
                'success' => true,
                'message' => 'Seguimiento eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar seguimiento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el seguimiento'
            ], 500);
        }
    }

    public function importar(Request $request): JsonResponse
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            Excel::import(new SeguimientosImport, $request->file('archivo'));

            return response()->json([
                'success' => true,
                'message' => 'Archivo importado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al importar archivo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al importar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getEstadisticas(Request $request): array
    {
        $query = Seguimiento::query();

        // Aplicar los mismos filtros que en getSeguimientos (excepto el filtro principal)
        if ($request->filled('vendedor_id')) {
            $query->porVendedor($request->vendedor_id);
        }

        if ($request->filled('buscar_cliente')) {
            $query->buscarCliente($request->buscar_cliente);
        }

        return [
            'total' => $query->count(),
            'atrasados' => (clone $query)->atrasados()->count(),
            'proximos' => (clone $query)->proximos(7)->count(),
            'completados_hoy' => (clone $query)->where('ultima_gestion', Carbon::today())->count(),
        ];
    }

    // API para buscar clientes (autocompletado)
    public function buscarClientes(Request $request): JsonResponse
    {
        $termino = $request->get('q', '');
        
        $clientes = Cliente::where('nombre', 'LIKE', "%{$termino}%")
                          ->orWhere('rut', 'LIKE', "%{$termino}%")
                          ->select('id', 'nombre', 'rut')
                          ->limit(10)
                          ->get();

        return response()->json($clientes);
    }

    // API para obtener vendedores
    public function getVendedores(): JsonResponse
    {
        $vendedores = User::select('id', 'name')->get();
        return response()->json($vendedores);
    }
}