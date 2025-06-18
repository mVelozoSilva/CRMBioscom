<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Cotizacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductoController extends Controller
{
    /**
     * Lista todos los productos con filtros avanzados
     * Incluye búsqueda, categorías y estado
     */
    public function index(Request $request)
    {
        $query = Producto::query();

        // Filtros de búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhere('categoria', 'like', "%{$search}%");
            });
        }

        // Filtro por categoría
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por rango de precios
        if ($request->filled('precio_min')) {
            $query->where('precio_neto', '>=', $request->precio_min);
        }
        if ($request->filled('precio_max')) {
            $query->where('precio_neto', '<=', $request->precio_max);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'nombre');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        $allowedSorts = ['nombre', 'precio_neto', 'categoria', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $productos = $query->paginate(20);
        
        // Obtener categorías para el filtro
        $categorias = Producto::distinct()->pluck('categoria')->filter()->sort();

        return view('productos.index', compact('productos', 'categorias'));
    }

    /**
     * Muestra el formulario para crear un nuevo producto
     * Incluye plantillas de constructor visual
     */
    public function create()
    {
        $categorias = $this->obtenerCategorias();
        $plantillasConstructor = $this->obtenerPlantillasConstructor();
        
        return view('productos.create', compact('categorias', 'plantillasConstructor'));
    }

    /**
     * Almacena un nuevo producto con constructor visual
     * Maneja imágenes, accesorios y bloques de contenido
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:productos,nombre',
            'descripcion' => 'nullable|string',
            'precio_neto' => 'required|numeric|min:0',
            'categoria' => 'required|string|max:255',
            'estado' => 'required|in:Activo,Inactivo',
            
            // Constructor visual
            'bloques_contenido' => 'nullable|json',
            'plantilla_id' => 'nullable|string',
            
            // Archivos
            'imagenes.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'documentos.*' => 'file|mimes:pdf,doc,docx|max:5120',
            
            // Datos estructurados
            'accesorios' => 'nullable|json',
            'opcionales' => 'nullable|json',
            'especificaciones_tecnicas' => 'nullable|json',
            'garantias' => 'nullable|json'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Procesar imágenes
            $imagenes = $this->procesarImagenes($request);
            
            // Procesar documentos
            $documentos = $this->procesarDocumentos($request);
            
            // Procesar constructor visual
            $constructorData = $this->procesarConstructorVisual($request);

            $producto = Producto::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'precio_neto' => $request->precio_neto,
                'categoria' => $request->categoria,
                'estado' => $request->estado,
                
                // Datos JSON
                'imagenes' => json_encode($imagenes),
                'documentos' => json_encode($documentos),
                'accesorios' => $request->accesorios,
                'opcionales' => $request->opcionales,
                'especificaciones_tecnicas' => $request->especificaciones_tecnicas,
                'garantias' => $request->garantias,
                
                // Constructor visual
                'bloques_contenido' => $constructorData['bloques'],
                'plantilla_base' => $constructorData['plantilla'],
                'configuracion_visual' => json_encode($constructorData['configuracion'])
            ]);

            // Log de actividad
            Log::info("Producto creado: {$producto->nombre} (ID: {$producto->id})");

            return redirect()->route('productos.show', $producto)
                ->with('success', 'Producto creado exitosamente con constructor visual.');

        } catch (\Exception $e) {
            Log::error('Error al crear producto: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al crear el producto. Por favor, intente nuevamente.')
                ->withInput();
        }
    }

    /**
     * Muestra los detalles de un producto específico
     * Incluye previsualización del constructor visual
     */
    public function show(Producto $producto)
    {
        // Cargar cotizaciones relacionadas
        $cotizaciones = Cotizacion::whereJsonContains('productos_cotizados', $producto->id)
            ->latest()
            ->limit(10)
            ->get();

        // Estadísticas del producto
        $estadisticas = [
            'total_cotizaciones' => Cotizacion::whereJsonContains('productos_cotizados', $producto->id)->count(),
            'ventas_ultimo_mes' => Cotizacion::whereJsonContains('productos_cotizados', $producto->id)
                ->where('estado', 'Ganada')
                ->where('created_at', '>=', now()->subMonth())
                ->sum('total_con_iva'),
            'precio_promedio_venta' => $this->calcularPrecioPromedioVenta($producto)
        ];

        // Procesar contenido visual para mostrar
        $contenidoVisual = $this->procesarContenidoVisualParaMostrar($producto);

        return view('productos.show', compact('producto', 'cotizaciones', 'estadisticas', 'contenidoVisual'));
    }

    /**
     * Muestra el formulario para editar un producto
     */
    public function edit(Producto $producto)
    {
        $categorias = $this->obtenerCategorias();
        $plantillasConstructor = $this->obtenerPlantillasConstructor();
        
        // Decodificar datos JSON para el formulario
        $producto->imagenes_array = json_decode($producto->imagenes, true) ?? [];
        $producto->accesorios_array = json_decode($producto->accesorios, true) ?? [];
        $producto->opcionales_array = json_decode($producto->opcionales, true) ?? [];
        
        return view('productos.edit', compact('producto', 'categorias', 'plantillasConstructor'));
    }

    /**
     * Actualiza un producto existente
     */
    public function update(Request $request, Producto $producto)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:productos,nombre,' . $producto->id,
            'descripcion' => 'nullable|string',
            'precio_neto' => 'required|numeric|min:0',
            'categoria' => 'required|string|max:255',
            'estado' => 'required|in:Activo,Inactivo',
            
            // Constructor visual
            'bloques_contenido' => 'nullable|json',
            'plantilla_id' => 'nullable|string',
            
            // Archivos
            'imagenes.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'documentos.*' => 'file|mimes:pdf,doc,docx|max:5120'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Mantener archivos existentes si no se suben nuevos
            $imagenes = $this->procesarImagenes($request, json_decode($producto->imagenes, true));
            $documentos = $this->procesarDocumentos($request, json_decode($producto->documentos, true));
            
            // Actualizar constructor visual
            $constructorData = $this->procesarConstructorVisual($request);

            $producto->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'precio_neto' => $request->precio_neto,
                'categoria' => $request->categoria,
                'estado' => $request->estado,
                
                // Datos JSON actualizados
                'imagenes' => json_encode($imagenes),
                'documentos' => json_encode($documentos),
                'accesorios' => $request->accesorios,
                'opcionales' => $request->opcionales,
                'especificaciones_tecnicas' => $request->especificaciones_tecnicas,
                'garantias' => $request->garantias,
                
                // Constructor visual actualizado
                'bloques_contenido' => $constructorData['bloques'],
                'plantilla_base' => $constructorData['plantilla'],
                'configuracion_visual' => json_encode($constructorData['configuracion'])
            ]);

            Log::info("Producto actualizado: {$producto->nombre} (ID: {$producto->id})");

            return redirect()->route('productos.show', $producto)
                ->with('success', 'Producto actualizado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al actualizar producto: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al actualizar el producto.')
                ->withInput();
        }
    }

    /**
     * Elimina un producto (soft delete recomendado)
     */
    public function destroy(Producto $producto)
    {
        try {
            // Verificar si el producto está en cotizaciones activas
            $cotizacionesActivas = Cotizacion::whereJsonContains('productos_cotizados', $producto->id)
                ->whereIn('estado', ['Pendiente', 'En Proceso'])
                ->count();

            if ($cotizacionesActivas > 0) {
                return redirect()->back()
                    ->with('error', 'No se puede eliminar el producto porque está en cotizaciones activas.');
            }

            // Cambiar estado en lugar de eliminar
            $producto->update(['estado' => 'Inactivo']);
            
            Log::info("Producto desactivado: {$producto->nombre} (ID: {$producto->id})");

            return redirect()->route('productos.index')
                ->with('success', 'Producto desactivado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al desactivar producto: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al desactivar el producto.');
        }
    }

    /**
     * API: Búsqueda de productos para autocompletado
     * Utilizado en formularios de cotización
     */
    /**
 * Buscar productos para autocompletado (API)
 */
public function buscarProductos(Request $request)
{
    try {
        $query = $request->get('q', '');
        
        \Log::info('🔍 Búsqueda de productos iniciada:', [
            'query' => $query,
            'request_all' => $request->all()
        ]);
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $productos = \App\Models\Producto::where('estado', 'Activo')
            ->where(function($q) use ($query) {
                $q->where('nombre', 'like', "%{$query}%")
                  ->orWhere('descripcion', 'like', "%{$query}%")
                  ->orWhere('categoria', 'like', "%{$query}%");
            })
            ->select('id', 'nombre', 'descripcion', 'precio_neto', 'categoria')
            ->limit(10)
            ->get();

        \Log::info('✅ Resultados de búsqueda de productos:', [
            'query' => $query,
            'resultados_count' => $productos->count(),
            'resultados' => $productos->toArray()
        ]);

        return response()->json($productos);

    } catch (\Exception $e) {
        \Log::error('❌ Error en búsqueda de productos:', [
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
     * API: Obtener detalles completos de un producto
     * Para formularios de cotización
     */
    public function obtenerDetallesCompletos($id)
    {
        try {
            $producto = Producto::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'producto' => [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'precio_neto' => $producto->precio_neto,
                    'categoria' => $producto->categoria,
                    'imagenes' => json_decode($producto->imagenes, true),
                    'accesorios' => json_decode($producto->accesorios, true),
                    'opcionales' => json_decode($producto->opcionales, true),
                    'especificaciones_tecnicas' => json_decode($producto->especificaciones_tecnicas, true),
                    'bloques_contenido' => $producto->bloques_contenido,
                    'plantilla_base' => $producto->plantilla_base,
                    'configuracion_visual' => json_decode($producto->configuracion_visual, true)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }
    }

    /**
     * Previsualización del constructor visual
     * Genera HTML renderizado para cotizaciones
     */
    public function previsualizarConstructorVisual(Request $request)
    {
        try {
            $bloques = $request->input('bloques_contenido');
            $plantilla = $request->input('plantilla_base');
            $configuracion = $request->input('configuracion_visual', []);

            $htmlGenerado = $this->generarHTMLDesdeBloques($bloques, $plantilla, $configuracion);

            return response()->json([
                'success' => true,
                'html' => $htmlGenerado,
                'css_adicional' => $this->generarCSSPersonalizado($configuracion)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar previsualización'
            ], 500);
        }
    }

    /**
     * Métodos privados auxiliares
     */

    private function obtenerCategorias()
    {
        return [
            'Equipamiento Médico',
            'Insumos Médicos',
            'Tecnología Hospitalaria',
            'Mobiliario Clínico',
            'Instrumentos Quirúrgicos',
            'Equipos de Diagnóstico',
            'Consumibles',
            'Repuestos y Accesorios'
        ];
    }

    private function obtenerPlantillasConstructor()
    {
        return [
            'simple' => [
                'nombre' => 'Producto Simple',
                'descripcion' => 'Para insumos y productos básicos',
                'bloques' => ['titulo', 'imagen', 'descripcion', 'precio']
            ],
            'medio' => [
                'nombre' => 'Producto Medio',
                'descripcion' => 'Para equipos con especificaciones',
                'bloques' => ['titulo', 'galeria', 'descripcion', 'especificaciones', 'accesorios', 'precio']
            ],
            'complejo' => [
                'nombre' => 'Producto Complejo',
                'descripcion' => 'Para equipamiento médico avanzado',
                'bloques' => ['titulo', 'galeria', 'descripcion', 'especificaciones', 'caracteristicas', 'accesorios', 'opcionales', 'garantia', 'precio']
            ],
            'servicio' => [
                'nombre' => 'Servicio Técnico',
                'descripcion' => 'Para servicios y mantenciones',
                'bloques' => ['titulo', 'descripcion_servicio', 'alcance', 'frecuencia', 'precio']
            ]
        ];
    }

    private function procesarImagenes(Request $request, $imagenesExistentes = [])
    {
        $imagenes = $imagenesExistentes;

        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $archivo) {
                $nombreArchivo = time() . '_' . Str::random(10) . '.' . $archivo->getClientOriginalExtension();
                $rutaArchivo = $archivo->storeAs('productos/imagenes', $nombreArchivo, 'public');
                
                $imagenes[] = [
                    'nombre_original' => $archivo->getClientOriginalName(),
                    'ruta' => $rutaArchivo,
                    'url' => Storage::url($rutaArchivo),
                    'tamaño' => $archivo->getSize(),
                    'subido_en' => now()->toISOString()
                ];
            }
        }

        return $imagenes;
    }

    private function procesarDocumentos(Request $request, $documentosExistentes = [])
    {
        $documentos = $documentosExistentes;

        if ($request->hasFile('documentos')) {
            foreach ($request->file('documentos') as $archivo) {
                $nombreArchivo = time() . '_' . Str::random(10) . '.' . $archivo->getClientOriginalExtension();
                $rutaArchivo = $archivo->storeAs('productos/documentos', $nombreArchivo, 'public');
                
                $documentos[] = [
                    'nombre_original' => $archivo->getClientOriginalName(),
                    'ruta' => $rutaArchivo,
                    'url' => Storage::url($rutaArchivo),
                    'tipo' => $archivo->getClientMimeType(),
                    'tamaño' => $archivo->getSize(),
                    'subido_en' => now()->toISOString()
                ];
            }
        }

        return $documentos;
    }

    private function procesarConstructorVisual(Request $request)
    {
        $bloquesContenido = $request->input('bloques_contenido');
        $plantillaId = $request->input('plantilla_id');
        $configuracionPersonalizada = $request->input('configuracion_visual', []);

        // Validar y sanitizar bloques
        $bloquesProcesados = $this->validarYSanitizarBloques($bloquesContenido);

        return [
            'bloques' => json_encode($bloquesProcesados),
            'plantilla' => $plantillaId,
            'configuracion' => array_merge($this->obtenerConfiguracionBase(), $configuracionPersonalizada)
        ];
    }

    private function validarYSanitizarBloques($bloques)
    {
        // Implementar validación y sanitización
        return $bloques;
    }

    private function obtenerConfiguracionBase()
    {
        return [
            'tema' => 'bioscom',
            'colores' => [
                'primario' => '#6284b8',
                'secundario' => '#5f87b8',
                'acento' => '#00334e'
            ],
            'tipografia' => [
                'familia' => 'Inter',
                'tamaño_base' => '14px'
            ]
        ];
    }

    private function obtenerImagenPrincipal($producto)
    {
        $imagenes = json_decode($producto->imagenes, true);
        return $imagenes[0]['url'] ?? '/images/producto-default.png';
    }

    private function calcularPrecioPromedioVenta($producto)
    {
        // Lógica para calcular precio promedio de ventas
        return $producto->precio_neto; // Simplificado
    }

    private function procesarContenidoVisualParaMostrar($producto)
    {
        if (!$producto->bloques_contenido) {
            return null;
        }

        $bloques = json_decode($producto->bloques_contenido, true);
        $configuracion = json_decode($producto->configuracion_visual, true);

        return $this->generarHTMLDesdeBloques($bloques, $producto->plantilla_base, $configuracion);
    }

    private function generarHTMLDesdeBloques($bloques, $plantilla, $configuracion)
    {
        // Implementar generador de HTML basado en bloques
        return '<div class="producto-preview">Contenido generado por constructor visual</div>';
    }

    private function generarCSSPersonalizado($configuracion)
    {
        // Generar CSS personalizado basado en configuración
        return '';
    }
}