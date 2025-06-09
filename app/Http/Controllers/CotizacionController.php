<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Cliente;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\CotizacionRequest;
use Illuminate\Support\Facades\Log;

class CotizacionController extends Controller
{
    /**
     * Muestra una lista de las cotizaciones
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $cotizaciones = Cotizacion::with('cliente')
                ->orderBy('created_at', 'desc')
                ->get();
            return response()->json($cotizaciones);
        }

        $cotizaciones = Cotizacion::with('cliente')->orderBy('created_at', 'desc')->get();
        return view('cotizaciones.index', compact('cotizaciones'));
    }

    /**
     * Muestra el formulario para crear una nueva cotización
     */
    public function create()
    {
        $clientes = Cliente::all();
        $codigoCotizacion = 'COT-' . Str::upper(Str::random(6));

        return view('cotizaciones.create', compact('clientes', 'codigoCotizacion'));
    }

    /**
     * Almacena una nueva cotización en la base de datos
     */
    public function store(CotizacionRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $cotizacionData = $this->procesarDatosCotizacion($validatedData);

            $cotizacion = Cotizacion::create($cotizacionData);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cotización creada exitosamente!',
                    'cotizacion' => $cotizacion
                ], 201);
            }

            return redirect()->route('cotizaciones.index')
                ->with('success', 'Cotización creada exitosamente!');

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Error al guardar la cotización: ' . $e->getMessage(), ['exception' => $e]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Hubo un error al guardar la cotización.',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Hubo un error al guardar la cotización.')->withInput();
        }
    }

    /**
     * Muestra los detalles de una cotización específica
     */
    public function show(Cotizacion $cotizacion, Request $request)
    {
        if ($request->expectsJson()) {
            $cotizacion->load('cliente');
            return response()->json($cotizacion);
        }

        $cotizacion->load('cliente');
        return view('cotizaciones.show', compact('cotizacion'));
    }

    /**
     * Muestra el formulario para editar una cotización existente
     */
    public function edit(Cotizacion $cotizacion)
    {
        $cotizacion->load('cliente');
        return view('cotizaciones.edit', ['initialCotizacion' => $cotizacion]);
    }

    /**
     * Actualiza una cotización existente en la base de datos
     */
    public function update(CotizacionRequest $request, Cotizacion $cotizacion)
    {
        try {
            $validatedData = $request->validated();
            $cotizacionData = $this->procesarDatosCotizacion($validatedData);

            $cotizacion->update($cotizacionData);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cotización actualizada exitosamente!',
                    'cotizacion' => $cotizacion->fresh()
                ], 200);
            }

            return redirect()->route('cotizaciones.show', $cotizacion)
                ->with('success', 'Cotización actualizada exitosamente!');

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Error al actualizar la cotización: ' . $e->getMessage(), ['exception' => $e]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Hubo un error al actualizar la cotización.',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Hubo un error al actualizar la cotización.')->withInput();
        }
    }

    /**
     * Elimina la cotización especificada
     */
    public function destroy(Cotizacion $cotizacion, Request $request)
    {
        try {
            $cotizacion->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cotización eliminada exitosamente.'
                ], 200);
            }

            return redirect()->route('cotizaciones.index')
                ->with('success', 'Cotización eliminada exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al eliminar la cotización: ' . $e->getMessage(), ['exception' => $e]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Error al eliminar la cotización.',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error al eliminar la cotización.');
        }
    }

    /**
     * Procesa los datos de la cotización para guardar/actualizar
     * (Método privado para reutilizar lógica)
     */
    private function procesarDatosCotizacion($validatedData)
    {
        $totalNeto = 0;
        $ivaPorcentaje = 0.19; // 19% IVA en Chile
        $productosCotizados = [];

        // Procesar productos cotizados
        if (isset($validatedData['productos_cotizados'])) {
            foreach ($validatedData['productos_cotizados'] as $item) {
                $productoOriginal = Producto::find($item['id_producto']);

                if ($productoOriginal) {
                    $subtotalItem = (float)$item['cantidad'] * (float)$item['precio_unitario'];
                    $totalNeto += $subtotalItem;

                    $productosCotizados[] = [
                        'id_producto' => $productoOriginal->id,
                        'nombre' => $productoOriginal->nombre,
                        'categoria' => $productoOriginal->categoria,
                        'descripcion_corta' => $item['descripcion_corta'] ?? ($productoOriginal->descripcion ?? ''),
                        'precio_unitario' => (float)$item['precio_unitario'],
                        'cantidad' => (int)$item['cantidad'],
                        'subtotal' => $subtotalItem,
                        'lo_que_incluye_equipo' => $productoOriginal->accesorios ?? null,
                        'imagenes_producto' => $productoOriginal->imagenes ?? null,
                        'descripcion_original_producto' => $productoOriginal->descripcion ?? null,
                        'opcionales_producto' => $productoOriginal->opcionales ?? null,
                    ];
                }
            }
        }

        $iva = $totalNeto * $ivaPorcentaje;
        $totalConIva = $totalNeto + $iva;

        return [
            'cliente_id' => $validatedData['cliente_id'],
            'nombre_institucion' => $validatedData['nombre_institucion'],
            'nombre_contacto' => $validatedData['nombre_contacto'],
            'nombre_cotizacion' => $validatedData['nombre_cotizacion'],
            'codigo' => $validatedData['codigo'] ?? 'COT-' . Str::upper(Str::random(6)),
            'info_contacto_vendedor' => $validatedData['info_contacto_vendedor'] ?? null,
            'validez_oferta' => $validatedData['validez_oferta'],
            'forma_pago' => $validatedData['forma_pago'] ?? null,
            'plazo_entrega' => $validatedData['plazo_entrega'] ?? null,
            'garantia_tecnica' => $validatedData['garantia_tecnica'] ?? null,
            'informacion_adicional' => $validatedData['informacion_adicional'] ?? null,
            'descripcion_opcionales' => $validatedData['descripcion_opcionales'] ?? null,
            'productos_cotizados' => $productosCotizados, // Laravel automáticamente convierte a JSON
            'total_neto' => round($totalNeto, 2),
            'iva' => round($iva, 2),
            'total_con_iva' => round($totalConIva, 2),
            'estado' => $validatedData['estado'] ?? 'Pendiente',
        ];
    }
}