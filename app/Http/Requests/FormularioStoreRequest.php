<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FormularioStoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string'
        ];
    }
}