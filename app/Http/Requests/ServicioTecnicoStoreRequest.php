<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServicioTecnicoStoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'titulo' => 'required|string|max:255',
            'estado' => 'required|string',
            'descripcion' => 'nullable|string',
        ];
    }
}