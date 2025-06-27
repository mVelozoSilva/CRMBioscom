<?php
namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index() {
        return view('notificaciones.index');
    }

    public function create() {
        return view('notificaciones.create');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'mensaje' => 'required|string',
            'tipo' => 'nullable|string',
            'urgente' => 'boolean',
        ]);

        $validated['visto'] = false;
        $validated['usuario_id'] = auth()->id() ?? 1;

        Notificacion::create($validated);

        return redirect('/notificaciones')->with('success', 'Notificación creada');
    }
}