<?php
namespace App\Http\Controllers;

use App\Models\ServicioTecnico;
use Illuminate\Http\Request;

class ServicioTecnicoController extends Controller
{
    public function index() {
        return view('servicio_tecnico.index');
    }

    public function create() {
        return view('servicio_tecnico.create');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'estado' => 'required|string',
            'descripcion' => 'nullable|string',
        ]);

        ServicioTecnico::create($validated);
        return redirect('/servicio-tecnico')->with('success', 'Solicitud registrada');
    }
}