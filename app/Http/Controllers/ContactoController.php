<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class ContactoController extends Controller
{
    /**
     * Muestra una lista de contactos (filtrada por cliente_id o búsqueda)
     */
    public function index(Request $request)
    {
        $query = Contacto::with('cliente'); // Incluir datos del cliente

        // Filtro por cliente_id (para obtener contactos de un cliente específico)
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        // Búsqueda por nombre de contacto
        if ($request->filled('q')) {
            $busqueda = $request->q;
            $query->where(function($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('cargo', 'like', "%{$busqueda}%")
                  ->orWhere('email', 'like', "%{$busqueda}%")
                  ->orWhere('area', 'like', "%{$busqueda}%");
            });
        }

        // Filtro por área
        if ($request->filled('area')) {
            $query->where('area', $request->area);
        }

        $contactos = $query->orderBy('nombre')
            ->limit(50) // Aumenté el límite para cotizaciones
            ->get();

        // Si es petición AJAX/API, devolver JSON
        if ($request->expectsJson()) {
            return response()->json($contactos);
        }

        // Si es petición web, devolver vista (opcional)
        return view('contactos.index', compact('contactos'));
    }

    /**
     * Almacena un nuevo contacto en la base de datos
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
            $contacto->load('cliente'); // Cargar relación para la respuesta

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Contacto creado exitosamente!',
                    'contacto' => $contacto
                ], 201);
            }

            return redirect()->route('contactos.index')
                ->with('success', 'Contacto creado exitosamente!');

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Error al guardar el contacto: ' . $e->getMessage(), ['exception' => $e]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Hubo un error al guardar el contacto.',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Hubo un error al guardar el contacto.')->withInput();
        }
    }

    /**
     * Muestra los detalles de un contacto específico
     */
    public function show(Contacto $contacto, Request $request)
    {
        $contacto->load('cliente');

        if ($request->expectsJson()) {
            return response()->json($contacto);
        }

        return view('contactos.show', compact('contacto'));
    }

    /**
     * Actualiza un contacto existente en la base de datos
     */
    public function update(Request $request, Contacto $contacto)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
                'cargo' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'telefono' => 'nullable|string|max:255',
                'area' => 'nullable|string|max:255',
                'notas' => 'nullable|string',
            ]);

            $contacto->update($validatedData);
            $contacto->load('cliente');

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Contacto actualizado exitosamente!',
                    'contacto' => $contacto
                ], 200);
            }

            return redirect()->route('contactos.show', $contacto)
                ->with('success', 'Contacto actualizado exitosamente!');

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Error al actualizar el contacto: ' . $e->getMessage(), ['exception' => $e]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Hubo un error al actualizar el contacto.',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Hubo un error al actualizar el contacto.')->withInput();
        }
    }

    /**
     * Elimina el contacto especificado de la base de datos
     */
    public function destroy(Contacto $contacto, Request $request)
    {
        try {
            $contacto->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Contacto eliminado exitosamente.'
                ], 200);
            }

            return redirect()->route('contactos.index')
                ->with('success', 'Contacto eliminado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al eliminar el contacto: ' . $e->getMessage(), ['exception' => $e]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Error al eliminar el contacto.',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error al eliminar el contacto.');
        }
    }

    /**
     * API específica para obtener contactos de un cliente (usado en cotizaciones)
     */
    public function porCliente(Cliente $cliente)
    {
        $contactos = $cliente->contactos()
            ->orderBy('nombre')
            ->get();

        return response()->json($contactos);
    }

    /**
     * API para obtener áreas únicas de contactos
     */
    public function areas()
    {
        $areas = Contacto::distinct()
            ->whereNotNull('area')
            ->pluck('area')
            ->filter()
            ->sort()
            ->values();

        return response()->json($areas);
    }

    /**
     * API para buscar contactos con autocompletado
     */
    public function buscar(Request $request)
    {
        $query = $request->get('q', '');
        $cliente_id = $request->get('cliente_id');

        $contactosQuery = Contacto::with('cliente')
            ->where(function($q) use ($query) {
                $q->where('nombre', 'like', "%{$query}%")
                  ->orWhere('cargo', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            });

        if ($cliente_id) {
            $contactosQuery->where('cliente_id', $cliente_id);
        }

        $contactos = $contactosQuery->limit(10)->get();

        return response()->json($contactos);
    }
}