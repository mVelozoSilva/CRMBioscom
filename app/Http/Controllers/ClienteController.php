<?php

namespace App\Http\Controllers;

use App\Models\Cliente; // Importa el modelo Cliente
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException; // Para manejar errores de validación
use App\Http\Requests\ClienteStoreRequest;
use App\Http\Requests\ClienteUpdateRequest;

class ClienteController extends Controller
{
    /**
     * Muestra una lista de los clientes.
     * Esto será un punto de entrada para un futuro componente Vue de listado de clientes.
     */
    public function index()
    {
        // Por ahora, podría mostrar una vista simple o eventualmente cargar un componente Vue
        $clientes = Cliente::all(); // Obtiene todos los clientes de la base de datos
        return view('clientes.index', compact('clientes')); // Pasa los clientes a una vista
    }

    /**
     * Muestra el formulario para crear un nuevo cliente (cargará ClienteForm.vue).
     */
    public function create()
    {
        return view('clientes.create'); // Devuelve la vista Blade que cargará el componente ClienteForm.vue
    }

    /**
     * Guarda un nuevo cliente en la base de datos (consumido por ClienteForm.vue).
     */
    public function store(ClienteStoreRequest $request) // Usamos el FormRequest
    {
        try {
            $validatedData = $request->validated();

            // Convertir la cadena de 'vendedores_a_cargo' a un array JSON
            if (isset($validatedData['vendedores_a_cargo']) && is_string($validatedData['vendedores_a_cargo'])) {
                $validatedData['vendedores_a_cargo'] = array_map('trim', explode(',', $validatedData['vendedores_a_cargo']));
                $validatedData['vendedores_a_cargo'] = array_filter($validatedData['vendedores_a_cargo']); // Elimina vacíos
            } else {
                $validatedData['vendedores_a_cargo'] = []; // Asegura que sea un array vacío si no se envía
            }

            // Si 'nombre_contacto' no existe en la migración de clientes, Laravel lo ignorará si no está en fillable.
            // Si existe en la migración y en fillable, se guardará.

            $cliente = Cliente::create($validatedData);

            return response()->json(['message' => 'Cliente creado exitosamente!', 'cliente_id' => $cliente->id], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Error al guardar el cliente: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Hubo un error al guardar el cliente.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Muestra los detalles de un cliente específico.
     */
    public function show(Cliente $cliente)
    {
        return view('clientes.show', compact('cliente'));
    }

    /**
     * Muestra el formulario para editar un cliente existente (cargará ClienteForm.vue).
     */
    public function edit(Cliente $cliente)
    {
        // Pasa los datos del cliente al componente Vue
        return view('clientes.edit', ['initialCliente' => $cliente]);
    }

    /**
     * Actualiza un cliente existente en la base de datos (consumido por ClienteForm.vue).
     */
    public function update(ClienteUpdateRequest $request, Cliente $cliente) // Usamos el FormRequest
    {
        try {
            $validatedData = $request->validated();

            // Convertir la cadena de 'vendedores_a_cargo' a un array JSON
            if (isset($validatedData['vendedores_a_cargo']) && is_string($validatedData['vendedores_a_cargo'])) {
                $validatedData['vendedores_a_cargo'] = array_map('trim', explode(',', $validatedData['vendedores_a_cargo']));
                $validatedData['vendedores_a_cargo'] = array_filter($validatedData['vendedores_a_cargo']);
            } else {
                $validatedData['vendedores_a_cargo'] = [];
            }

            $cliente->update($validatedData);

            return response()->json(['message' => 'Cliente actualizado exitosamente!', 'cliente_id' => $cliente->id], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar el cliente: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Hubo un error al actualizar el cliente.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Busca clientes por nombre de institución o RUT (API para autocompletado en Cotizaciones).
     */
    public function buscarClientes(Request $request)
    {
        $query = $request->input('q'); // 'q' es el término de búsqueda

        $clientes = Cliente::where('nombre_institucion', 'like', '%' . $query . '%') // Busca por nombre de institución
                            ->orWhere('rut', 'like', '%' . $query . '%')          // O por RUT
                            ->limit(10) // Limita los resultados
                            ->get([
                                'id',
                                'nombre_institucion',
                                'nombre_contacto', // Si esta columna existe en tu tabla 'clientes' y guarda el contacto principal
                                'rut',
                                'tipo_cliente',
                                'email',
                                'telefono',
                            ]);

        return response()->json($clientes);
    }

    /**
     * Elimina el cliente especificado de la base de datos.
     */
    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return redirect()->route('clientes.index')
                         ->with('success', 'Cliente eliminado exitosamente.');
    }
}