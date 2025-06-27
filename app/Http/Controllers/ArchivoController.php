<?php
namespace App\Http\Controllers;

use App\Models\Archivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchivoController extends Controller
{
    public function index() {
        return view('archivos.index');
    }

    public function create() {
        return view('archivos.create');
    }

    public function store(Request $request) {
        $request->validate([
            'archivo' => 'required|file|max:10240'
        ]);

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $path = $file->store('archivos');

            Archivo::create([
                'nombre' => $file->getClientOriginalName(),
                'ruta' => $path,
                'tipo' => $file->getClientMimeType(),
            ]);
        }

        return redirect('/archivos')->with('success', 'Archivo subido correctamente');
    }
}