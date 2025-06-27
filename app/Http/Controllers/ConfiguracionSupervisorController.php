<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionSupervisor; 
use Illuminate\Http\Request;

class ConfiguracionSupervisorController extends Controller
{
    public function index() {
        return view('configuracion.index');
    }

    public function edit() {
        $config = ConfiguracionSupervisor::first() ?? new ConfiguracionSupervisor();
        return view('configuracion.edit', compact('config'));
    }

    public function update(Request $request) {
        $validated = $request->validate([
            'modo_oscuro' => 'boolean',
            'contraste_alto' => 'boolean',
            'tamano_fuente' => 'required|string|max:20',
            'activar_alertas' => 'boolean',
            'orden_prioridad' => 'nullable|string'
        ]);

        $config = ConfiguracionSupervisor::first();
        if (!$config) {
            $config = ConfiguracionSupervisor::create($validated);
        } else {
            $config->update($validated);
        }

        return redirect('/configuracion')->with('success', 'Configuración actualizada');
    }
}