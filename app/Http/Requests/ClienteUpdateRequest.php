<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // ¡Importa la clase Rule!

class ClienteUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Puedes ajustar la lógica de autorización aquí
    }

    public function rules(): array
    {
        // El 'cliente' en $this->route('cliente') asume que la ruta usa {cliente}
        // Si tu ruta es diferente, ajusta esto.
        $clienteId = $this->route('cliente')->id;

        return [
            'nombre_institucion' => 'required|string|max:255',
            'rut' => [
                'required',
                'string',
                'max:255',
                Rule::unique('clientes', 'rut')->ignore($clienteId), // Ignora el RUT del cliente actual
            ],
            'tipo_cliente' => 'required|in:Cliente Público,Cliente Privado,Revendedor',
            'nombre_contacto' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefono' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'vendedores_a_cargo' => 'nullable|array',
            'vendedores_a_cargo.*' => 'nullable|string', // Cada elemento del array debe ser una cadena (IDs de vendedor)
            'informacion_adicional' => 'nullable|string',
        ];
    }
}