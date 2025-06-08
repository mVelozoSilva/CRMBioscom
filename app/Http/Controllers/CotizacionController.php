<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Cliente;
use App\Models\Producto; // Asegúrate de que este modelo esté importado si lo usas
use Illuminate\Http\Request;
use Illuminate\Support\Str; // Para generar el código de cotización
use Illuminate\Validation\ValidationException; // Para manejar errores de validación
use App\Http\Requests\CotizacionRequest; // Tu Form Request de validación
use Illuminate\Support\Facades\Log; // Para registrar información de depuración

class CotizacionController extends Controller
{
    /**
     * Muestra una lista de las cotizaciones.
     */
    public function index()
    {
        // Asegúrate de cargar la relación 'cliente' para mostrar nombre_institucion en el listado
        $cotizaciones = Cotizacion::with('cliente')->orderBy('created_at', 'desc')->get();
        return view('cotizaciones.index', compact('cotizaciones'));
    }

    /**
     * Muestra el formulario para crear una nueva cotización.
     */
    public function create()
    {
        $clientes = Cliente::all(); // Obtener todos los clientes para el selector
        $codigoCotizacion = 'COT-' . Str::upper(Str::random(6)); // Esto solo es un valor sugerido, el frontend lo manejará

        return view('cotizaciones.create', compact('clientes', 'codigoCotizacion'));
    }

    /**
     * Almacena una nueva cotización en la base de datos.
     * Usamos CotizacionRequest para la validación.
     */
    public function store(CotizacionRequest $request)
    {
        $validatedData = $request->validated();

        $totalNeto = 0;
        $ivaPorcentaje = 0.19; // 19% IVA en Chile
        $productosCotizados = [];

        // IMPORTANTE: Asegúrate de importar el modelo Producto si no lo has hecho.
        // use App\Models\Producto; // <-- Añade esta línea al principio del archivo si no está.

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
                    // Usar ?? null si accesorios/imagenes son nullable en el modelo Producto o en la DB
                    'lo_que_incluye_equipo' => $productoOriginal->accesorios ?? null,
                    'imagenes_producto' => $productoOriginal->imagenes ?? null,
                    'descripcion_original_producto' => $productoOriginal->descripcion ?? null,
                    'opcionales_producto' => $productoOriginal->opcionales ?? null,
                ];
            }
        }

        $iva = $totalNeto * $ivaPorcentaje;
        $totalConIva = $totalNeto + $iva;

        $cotizacionData = [
            'cliente_id' => $validatedData['cliente_id'],
            'nombre_institucion' => $validatedData['nombre_institucion'],
            'nombre_contacto' => $validatedData['nombre_contacto'],
            'nombre_cotizacion' => $validatedData['nombre_cotizacion'],
            'codigo' => $validatedData['codigo'] ?? null,
            'info_contacto_vendedor' => $validatedData['info_contacto_vendedor'] ?? null,
            'validez_oferta' => $validatedData['validez_oferta'],
            'forma_pago' => $validatedData['forma_pago'] ?? null,
            'plazo_entrega' => $validatedData['plazo_entrega'] ?? null,
            'garantia_tecnica' => $validatedData['garantia_tecnica'] ?? null,
            'informacion_adicional' => $validatedData['informacion_adicional'] ?? null,
            'descripcion_opcionales' => $validatedData['descripcion_opcionales'] ?? null,
            'productos_cotizados' => json_encode($productosCotizados), // ¡Almacena el array como JSON!
            'total_neto' => round($totalNeto, 2),
            'iva' => round($iva, 2),
            'total_con_iva' => round($totalConIva, 2),
            'estado' => 'Pendiente',
        ];

        try {
            Cotizacion::create($cotizacionData);
            return response()->json(['message' => 'Cotización creada exitosamente!'], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Error al guardar la cotización: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Hubo un error al guardar la cotización.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Muestra los detalles de una cotización específica.
     */
       public function show($id) // Acepta el $id directamente
    {
        // >>>>>>> ESTA ES LA LÍNEA CRÍTICA DE ASIGNACIÓN <<<<<<<
        $cotizacion = Cotizacion::find($id); // Busca la cotización por su ID
        // <<<<<<< HASTA AQUÍ <<<<<<<

        // Si la cotización no se encuentra, abortar con un 404
        if (!$cotizacion) {
            abort(404, 'Cotización no encontrada.');
        }

        // Cargar la relación 'cliente' para asegurar que esté disponible
        $cotizacion->load('cliente');

        return view('cotizaciones.show', compact('cotizacion'));
    }
 /**
     * Muestra el formulario para editar una cotización existente.
     */
   public function edit($id) // <--- Cambiamos a aceptar el $id directamente
    {
        // Busca la cotización manualmente por el ID
        $cotizacion = Cotizacion::find($id);

        // Si la cotización no se encuentra, redirigir o mostrar un 404
        if (!$cotizacion) {
            abort(404, 'Cotización no encontrada para edición.');
        }

        // Cargar la relación 'cliente' para el frontend
        $cotizacion->load('cliente');

        // Asegúrate de que los campos JSON estén decodificados para Vue si es necesario (casts ya lo hacen)
        // dd($cotizacion->toArray()); // Línea de depuración temporal, puedes usar si lo necesitas
        return view('cotizaciones.edit', ['initialCotizacion' => $cotizacion]);
    }
    /**
     * Actualiza una cotización existente en la base de datos.
     */
    public function update(CotizacionRequest $request, Cotizacion $cotizacion) // Usa CotizacionRequest para validación
        {
             dd([
        'request_all' => $request->all(),
        'validated_data' => $request->validated(),
        'cotizacion_object' => $cotizacion->toArray(),
    ]);
            try {
                $validatedData = $request->validated();

                $productosCotizados = [];
                $totalNeto = 0;
                $ivaPorcentaje = 0.19;

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

                $iva = $totalNeto * $ivaPorcentaje;
                $totalConIva = $totalNeto + $iva;

                // Prepara los datos para la actualización
                $cotizacionData = [
                    'cliente_id' => $validatedData['cliente_id'],
                    'nombre_institucion' => $validatedData['nombre_institucion'],
                    'nombre_contacto' => $validatedData['nombre_contacto'],
                    'nombre_cotizacion' => $validatedData['nombre_cotizacion'],
                    'codigo' => $validatedData['codigo'] ?? null,
                    'info_contacto_vendedor' => $validatedData['info_contacto_vendedor'] ?? null,
                    'validez_oferta' => $validatedData['validez_oferta'],
                    'forma_pago' => $validatedData['forma_pago'] ?? null,
                    'plazo_entrega' => $validatedData['plazo_entrega'] ?? null,
                    'garantia_tecnica' => $validatedData['garantia_tecnica'] ?? null,
                    'informacion_adicional' => $validatedData['informacion_adicional'] ?? null,
                    'descripcion_opcionales' => $validatedData['descripcion_opcionales'] ?? null,
                    'productos_cotizados' => json_encode($productosCotizados),
                    'total_neto' => round($totalNeto, 2),
                    'iva' => round($iva, 2),
                    'total_con_iva' => round($totalConIva, 2),
                    'estado' => $validatedData['estado'] ?? 'Pendiente', // Mantener el estado, o actualizarlo desde el formulario
                ];

                $cotizacion->update($cotizacionData); // Actualiza la cotización existente

                return response()->json(['message' => 'Cotización actualizada exitosamente!', 'cotizacion_id' => $cotizacion->id], 200);
            } catch (ValidationException $e) {
                return response()->json(['errors' => $e->errors()], 422);
            } catch (\Exception $e) {
                \Log::error('Error al actualizar la cotización: ' . $e->getMessage(), ['exception' => $e]);
                return response()->json(['message' => 'Hubo un error al actualizar la cotización.', 'error' => $e->getMessage()], 500);
            }
        }
    /**
     * Elimina la cotización especificada.
     */
    public function destroy(Cotizacion $cotizacion)
    {
        $cotizacion->delete();
        return redirect()->route('cotizaciones.index')
                         ->with('success', 'Cotización eliminada exitosamente.');
    }
}