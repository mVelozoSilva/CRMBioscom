<?php
namespace App\Http\Controllers;

use App\Models\Campania;
use Illuminate\Http\Request;

class CampaniaController extends Controller
{
    public function index() {
        return view('campanias.index');
    }

    public function create() {
        return view('campanias.create');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        Campania::create($validated);
        return redirect('/campanias')->with('success', 'Campaña creada correctamente');
    }
}