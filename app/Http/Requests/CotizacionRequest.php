<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CotizacionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Aquí defines si el usuario actual tiene permiso para realizar esta solicitud.
        // Por ahora, lo dejaremos como true para que puedas probar la funcionalidad.
        // En un entorno de producción, aquí podrías verificar roles o permisos.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        \Log::info('Datos recibidos en CotizacionRequest:', $this->all());

        return [
            'nombre_institucion' => 'required|string|max:255',
            'nombre_contacto' => 'required|string|max:255',
            'cliente_id' => 'required|exists:clientes,id', // Valida que el ID del cliente exista en tu tabla 'clientes'
            'validez_oferta' => 'required|date',
            'forma_pago' => 'nullable|string|max:255',
            'plazo_entrega' => 'nullable|string|max:255',
            'garantia_tecnica' => 'nullable|string',
            'informacion_adicional' => 'nullable|string',
            'descripcion_opcionales' => 'nullable|string',
            'nombre_cotizacion' => 'nullable|string',
            'codigo' => 'nullable|string|max:255',
            'productos_cotizados' => 'required|array|min:1', // El array de productos es obligatorio y debe tener al menos uno
            'productos_cotizados.*.id_producto' => 'required|exists:productos,id',
            'productos_cotizados.*.nombre_producto' => 'required|string|max:255',
            'productos_cotizados.*.descripcion_corta' => 'nullable|string',
            'productos_cotizados.*.precio_unitario' => 'required|numeric|min:0', // Asegura que sea un número y no negativo
            'productos_cotizados.*.cantidad' => 'required|integer|min:1',     // Asegura que sea un entero y al menos 1
            
           
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'productos_cotizados.min' => 'Debes añadir al menos un producto a la cotización.',
            'productos_cotizados.*.precio_unitario.required' => 'El precio unitario es obligatorio para cada producto.',
            'productos_cotizados.*.precio_unitario.numeric' => 'El precio unitario debe ser un número.',
            'productos_cotizados.*.cantidad.required' => 'La cantidad es obligatoria para cada producto.',
            'productos_cotizados.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'productos_cotizados.*.cantidad.min' => 'La cantidad mínima es 1 para cada producto.',
            // Puedes añadir mensajes personalizados para cada regla de validación aquí
        ];
    }
}