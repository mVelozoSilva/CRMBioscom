<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificacionStoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'titulo' => 'required|string|max:255',
            'mensaje' => 'required|string',
            'tipo' => 'required|string'
        ];
    }
}