<?php

namespace App\Http\Controllers;

use App\Models\Contacto; // Importa el modelo Contacto
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException; // Para manejar errores de validación

class ContactoController extends Controller
{
    /**
     * Muestra una lista de contactos (opcionalmente filtrada por cliente_id o búsqueda).
     */
    public function index(Request $request)
    {
        $query = Contacto::query();

        // Filtro por cliente_id
        if ($request->has('cliente_id')) {
            $query->where('cliente_id', $request->input('cliente_id'));
        }

        // Búsqueda por nombre de contacto
        if ($request->has('q')) {
            $query->where('nombre', 'like', '%' . $request->input('q') . '%');
        }

        $contactos = $query->limit(20)->get(); // Limita resultados

        return response()->json($contactos);
    }

    /**
     * Almacena un nuevo contacto en la base de datos.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'cliente_id' => 'required|exists:clientes,id',
                'nombre' => 'required|string|max:255',
                'cargo' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'telefono' => 'nullable|string|max:255',
                'area' => 'nullable|string|max:255',
                'notas' => 'nullable|string',
            ]);

            $contacto = Contacto::create($validatedData);

            return response()->json(['message' => 'Contacto creado exitosamente!', 'contacto' => $contacto], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Error al guardar el contacto: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Hubo un error al guardar el contacto.', 'error' => $e->getMessage()], 500);
        }
    }

    // Puedes añadir aquí los métodos show, update, destroy si los necesitas más adelante
    // o si los dejó el generador --api. Por ahora, estos son suficientes para la cotización.
}