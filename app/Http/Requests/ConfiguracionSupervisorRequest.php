<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfiguracionSupervisorRequest extends FormRequest
{
    public function rules()
    {
        return [
            'modo_oscuro' => 'boolean',
            'contraste_alto' => 'boolean',
            'tamano_fuente' => 'required|string|max:20',
            'activar_alertas' => 'boolean',
            'orden_prioridad' => 'nullable|string'
        ];
    }
}