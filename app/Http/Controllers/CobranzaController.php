<?php

namespace App\Http\Controllers;

use App\Models\Cobranza;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CobranzaController extends Controller
{
    /**
     * Devuelve todas las cobranzas (ordenadas por vencimiento)
     */
    public function index(){

    try {
        $cobranzas = Cobranza::with(['cliente', 'usuarioAsignado'])
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        return response()->json($cobranzas, 200);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al cargar cobranzas',
            'detalle' => $e->getMessage(),
            'linea' => $e->getLine()
        ], 500);
    }

    }


    /**
     * Guarda una nueva cobranza
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_factura' => 'required|string|unique:cobranzas,id_factura',
            'cliente_id' => 'required|exists:clientes,id',
            'usuario_asignado_id' => 'nullable|exists:users,id',
            'monto_adeudado' => 'required|numeric',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'required|date',
            'estado' => 'required',
            'prioridad' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errores' => $validator->errors()], 422);
        }

        $cobranza = Cobranza::create($request->all());

        return response()->json($cobranza, 201);
    }

    /**
     * Actualiza una cobranza existente
     */
    public function update(Request $request, $id)
    {
        $cobranza = Cobranza::find($id);

        if (!$cobranza) {
            return response()->json(['error' => 'Cobranza no encontrada'], 404);
        }

        $cobranza->update($request->all());

        return response()->json($cobranza, 200);
    }

    /**
     * Elimina una cobranza
     */
    public function destroy($id)
    {
        $cobranza = Cobranza::find($id);

        if (!$cobranza) {
            return response()->json(['error' => 'Cobranza no encontrada'], 404);
        }

        $cobranza->delete();

        return response()->json(['mensaje' => 'Cobranza eliminada'], 200);
    }

public function updateMasivo(Request $request)
{
    $validated = $request->validate([
        'ids' => 'required|array',
        'estado' => 'required|string'
    ]);

    try {
        Cobranza::whereIn('id', $validated['ids'])->update([
            'estado' => $validated['estado']
        ]);

        return response()->json(['mensaje' => 'Cobranzas actualizadas correctamente ✅']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Error al actualizar cobranzas', 'detalle' => $e->getMessage()], 500);
    }
}
}