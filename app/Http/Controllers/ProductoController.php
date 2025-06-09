<?php

namespace App\Http\Controllers;

use App\Models\Producto; // Importa el modelo Producto
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    /**
     * Muestra una lista de los productos.
     */
    public function index()
    {
        $productos = Producto::all(); // Obtiene todos los productos
        return view('productos.index', compact('productos'));
    }

    /**
     * Muestra el formulario para crear un nuevo producto.
     */
    public function create()
    {
        return view('productos.create');
    }

    /**
     * Almacena un nuevo producto en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_neto' => 'required|numeric|min:0',
            'categoria' => 'nullable|string|max:255',
            // 'imagenes' y 'accesorios'/'opcionales' se manejan como texto plano por ahora,
            // luego se pueden mejorar con subida de archivos o un constructor más complejo.
            'imagenes' => 'nullable|string', // Aceptará un string de URLs/JSON
            'accesorios' => 'nullable|string',
            'opcionales' => 'nullable|string',
        ]);

        // Convertir strings JSON (si vienen del formulario así) a arrays para el modelo
        $data = $request->all();
        $data['imagenes'] = $data['imagenes'] ? json_decode($data['imagenes'], true) : null;
        $data['accesorios'] = $data['accesorios'] ? json_decode($data['accesorios'], true) : null;
        $data['opcionales'] = $data['opcionales'] ? json_decode($data['opcionales'], true) : null;


        Producto::create($data); // Crea el producto en la base de datos

        return redirect()->route('productos.index')
                         ->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Muestra el producto especificado.
     */
    public function show(Producto $producto)
    {
        return view('productos.show', compact('producto'));
    }

    /**
     * Muestra el formulario para editar el producto especificado.
     */
    public function edit(Producto $producto)
    {
        return view('productos.edit', compact('producto'));
    }

    /**
     * Actualiza el producto especificado en la base de datos.
     */
    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_neto' => 'required|numeric|min:0',
            'categoria' => 'nullable|string|max:255',
            'imagenes' => 'nullable|string',
            'accesorios' => 'nullable|string',
            'opcionales' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['imagenes'] = $data['imagenes'] ? json_decode($data['imagenes'], true) : null;
        $data['accesorios'] = $data['accesorios'] ? json_decode($data['accesorios'], true) : null;
        $data['opcionales'] = $data['opcionales'] ? json_decode($data['opcionales'], true) : null;

        $producto->update($data);

        return redirect()->route('productos.index')
                         ->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Elimina el producto especificado de la base de datos.
     */
    public function destroy(Producto $producto)
    {
        $producto->delete();

        return redirect()->route('productos.index')
                         ->with('success', 'Producto eliminado exitosamente.');
    }

    public function buscarProductos(Request $request)
{
    $query = $request->input('q', '');

    if (empty($query)) {
        return response()->json([]);
    }

    $productos = Producto::where('estado', 'Activo')
        ->where(function($q) use ($query) {
            $q->where('nombre', 'like', '%' . $query . '%')
              ->orWhere('categoria', 'like', '%' . $query . '%')
              ->orWhere('descripcion', 'like', '%' . $query . '%');
        })
        ->orderBy('nombre')
        ->limit(10)
        ->get([
            'id',
            'nombre',
            'categoria',
            'precio_neto',
            'descripcion'
        ]);

    return response()->json($productos);
}
}