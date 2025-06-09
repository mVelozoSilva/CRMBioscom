<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\ClienteStoreRequest;
use App\Http\Requests\ClienteUpdateRequest;
use Illuminate\Support\Facades\Log;

class ClienteController extends Controller
{
    /**
     * Muestra una lista de los clientes.
     * Para vistas Blade y API
     */
    public function index(Request $request)
    {
        // Si es una petición API (AJAX desde Vue.js)
        if ($request->expectsJson()) {
            $clientes = Cliente::with('contactos')
                ->orderBy('nombre_institucion')
                ->get();
            return response()->json($clientes);
        }

        // Si es una petición web normal (Blade)
        $clientes = Cliente::all();
        return view('clientes.index', compact('clientes'));
    }

    /**
     * Muestra el formulario para crear un nuevo cliente
     */
    public function create()
    {
        return view('clientes.create');
    }

    /**
     * Guarda un nuevo cliente en la base de datos
     */
    public function store(ClienteStoreRequest $request)
    {
        try {
            $validatedData = $request->validated();

            // Procesar vendedores_a_cargo
            if (isset($validatedData['vendedores_a_cargo'])) {
                if (is_string($validatedData['vendedores_a_cargo'])) {
                    $validatedData['vendedores_a_cargo'] = array_map('trim', explode(',', $validatedData['vendedores_a_cargo']));
                    $validatedData['vendedores_a_cargo'] = array_filter($validatedData['vendedores_a_cargo']);
                }
            } else {
                $validatedData['vendedores_a_cargo'] = [];
            }

            $cliente = Cliente::create($validatedData);

            // Respuesta diferente según el tipo de petición
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cliente creado exitosamente!',
                    'cliente' => $cliente
                ], 201);
            }

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente creado exitosamente!');

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Error al guardar el cliente: ' . $e->getMessage(), ['exception' => $e]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Hubo un error al guardar el cliente.',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Hubo un error al guardar el cliente.')->withInput();
        }
    }

    /**
     * Muestra los detalles de un cliente específico
     */
    public function show(Cliente $cliente, Request $request)
    {
        if ($request->expectsJson()) {
            $cliente->load('contactos');
            return response()->json($cliente);
        }

        return view('clientes.show', compact('cliente'));
    }

    /**
     * Muestra el formulario para editar un cliente existente
     */
    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', ['initialCliente' => $cliente]);
    }

    /**
     * Actualiza un cliente existente en la base de datos
     */
    public function update(ClienteUpdateRequest $request, Cliente $cliente)
    {
        try {
            $validatedData = $request->validated();

            // Procesar vendedores_a_cargo
            if (isset($validatedData['vendedores_a_cargo'])) {
                if (is_string($validatedData['vendedores_a_cargo'])) {
                    $validatedData['vendedores_a_cargo'] = array_map('trim', explode(',', $validatedData['vendedores_a_cargo']));
                    $validatedData['vendedores_a_cargo'] = array_filter($validatedData['vendedores_a_cargo']);
                }
            } else {
                $validatedData['vendedores_a_cargo'] = [];
            }

            $cliente->update($validatedData);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cliente actualizado exitosamente!',
                    'cliente' => $cliente->fresh()
                ], 200);
            }

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente actualizado exitosamente!');

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Error al actualizar el cliente: ' . $e->getMessage(), ['exception' => $e]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Hubo un error al actualizar el cliente.',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Hubo un error al actualizar el cliente.')->withInput();
        }
    }

    /**
     * Busca clientes por nombre de institución o RUT (API para autocompletado)
     */
    public function buscarClientes(Request $request)
    {
        $query = $request->input('q', '');

        $clientes = Cliente::where('nombre_institucion', 'like', '%' . $query . '%')
            ->orWhere('rut', 'like', '%' . $query . '%')
            ->limit(10)
            ->get([
                'id',
                'nombre_institucion',
                'nombre_contacto',
                'rut',
                'tipo_cliente',
                'email',
                'telefono',
            ]);

        return response()->json($clientes);
    }

    /**
     * Elimina el cliente especificado de la base de datos
     */
    public function destroy(Cliente $cliente, Request $request)
    {
        try {
            $cliente->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cliente eliminado exitosamente.'
                ], 200);
            }

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente eliminado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al eliminar el cliente: ' . $e->getMessage(), ['exception' => $e]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Error al eliminar el cliente.',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error al eliminar el cliente.');
        }
    }
}