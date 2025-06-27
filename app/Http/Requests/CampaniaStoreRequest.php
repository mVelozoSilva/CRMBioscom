<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CampaniaStoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ];
    }
}