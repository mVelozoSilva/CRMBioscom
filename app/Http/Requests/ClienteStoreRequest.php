<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClienteStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Por ahora, lo dejaremos como true para que puedas probar la funcionalidad.
        // En un entorno de producción, aquí podrías verificar roles o permisos.
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_institucion' => 'required|string|max:255',
            'rut' => 'required|string|max:255|unique:clientes,rut', // RUT debe ser único
            'tipo_cliente' => 'required|in:Cliente Público,Cliente Privado,Revendedor',
            'nombre_contacto' => 'required|string|max:255', // Asumiendo que es un campo en tu tabla 'clientes'
            'email' => 'required|email|max:255',
            'telefono' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'vendedores_a_cargo' => 'nullable|array', // Esperamos un array
            'vendedores_a_cargo.*' => 'nullable|string', // Cada elemento del array debe ser una cadena (IDs de vendedor)
            'informacion_adicional' => 'nullable|string',
        ];
    }
}