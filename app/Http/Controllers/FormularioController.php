<?php
namespace App\Http\Controllers;

use App\Models\Formulario;
use Illuminate\Http\Request;

class FormularioController extends Controller
{
    public function index() {
        return view('formularios.index');
    }

    public function create() {
        return view('formularios.create');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string'
        ]);

        Formulario::create($validated);
        return redirect('/formularios')->with('success', 'Formulario creado correctamente');
    }
}