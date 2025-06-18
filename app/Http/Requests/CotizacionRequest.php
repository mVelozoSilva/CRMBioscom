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
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        \Log::info('🔍 CotizacionRequest - Datos recibidos:', $this->all());

        return [
            // Información básica de la cotización
            'nombre_cotizacion' => 'required|string|max:255',
            
            // CÓDIGO CORREGIDO: Sin validación unique (puede repetirse)
            // El código es solo una referencia externa para licitaciones
            'codigo' => 'nullable|string|max:255',
            
            'estado' => 'sometimes|in:Pendiente,Enviada,En Revisión,Ganada,Perdida,Vencida',
            
            // Información del cliente
            'cliente_id' => 'required|exists:clientes,id',
            'nombre_institucion' => 'required|string|max:255',
            'nombre_contacto' => 'required|string|max:255',
            'info_contacto_vendedor' => 'nullable|string|max:255',
            
            // Fechas y condiciones
            'validez_oferta' => 'required|date|after_or_equal:today',
            'forma_pago' => 'nullable|string|max:255',
            'plazo_entrega' => 'nullable|string|max:255',
            
            // Descripciones
            'garantia_tecnica' => 'nullable|string|max:2000',
            'informacion_adicional' => 'nullable|string|max:2000',
            'descripcion_opcionales' => 'nullable|string|max:2000',
            
            // Vendedor (opcional, se asigna automáticamente si no se proporciona)
            'vendedor_id' => 'nullable|exists:users,id',
            
            // Productos cotizados - ESTRUCTURA CORREGIDA
            'productos_cotizados' => 'required|array|min:1',
            'productos_cotizados.*.id_producto' => 'nullable|exists:productos,id',
            'productos_cotizados.*.nombre_producto' => 'required|string|max:255',
            'productos_cotizados.*.descripcion_corta' => 'nullable|string|max:500',
            'productos_cotizados.*.precio_unitario' => 'required|numeric|min:0',
            'productos_cotizados.*.cantidad' => 'required|integer|min:1',
            'productos_cotizados.*.descuento' => 'nullable|numeric|min:0|max:100',
            
            // Totales (se calculan automáticamente pero se validan si vienen)
            'total_neto' => 'nullable|numeric|min:0',
            'iva' => 'nullable|numeric|min:0',
            'total_con_iva' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     */
    public function messages(): array
    {
        return [
            // Mensajes para información básica
            'nombre_cotizacion.required' => 'El nombre de la cotización es obligatorio.',
            // MENSAJE DE CÓDIGO ELIMINADO (ya no aplica)
            
            // Mensajes para cliente
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'nombre_institucion.required' => 'El nombre de la institución es obligatorio.',
            'nombre_contacto.required' => 'El nombre del contacto es obligatorio.',
            
            // Mensajes para fechas
            'validez_oferta.required' => 'La fecha de validez es obligatoria.',
            'validez_oferta.date' => 'La fecha de validez debe ser una fecha válida.',
            'validez_oferta.after_or_equal' => 'La fecha de validez no puede ser anterior a hoy.',
            
            // Mensajes para productos
            'productos_cotizados.required' => 'Debe agregar al menos un producto a la cotización.',
            'productos_cotizados.min' => 'Debe agregar al menos un producto a la cotización.',
            'productos_cotizados.*.nombre_producto.required' => 'El nombre del producto es obligatorio.',
            'productos_cotizados.*.precio_unitario.required' => 'El precio unitario es obligatorio para cada producto.',
            'productos_cotizados.*.precio_unitario.numeric' => 'El precio unitario debe ser un número.',
            'productos_cotizados.*.precio_unitario.min' => 'El precio unitario no puede ser negativo.',
            'productos_cotizados.*.cantidad.required' => 'La cantidad es obligatoria para cada producto.',
            'productos_cotizados.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'productos_cotizados.*.cantidad.min' => 'La cantidad mínima es 1 para cada producto.',
            'productos_cotizados.*.descuento.numeric' => 'El descuento debe ser un número.',
            'productos_cotizados.*.descuento.max' => 'El descuento no puede ser mayor al 100%.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Log para debug
        \Log::info('🔧 CotizacionRequest - prepareForValidation ejecutándose');
        \Log::info('📋 Datos antes de preparar:', $this->all());

        // Si productos_cotizados viene como string JSON, convertirlo a array
        if ($this->has('productos_cotizados') && is_string($this->productos_cotizados)) {
            $this->merge([
                'productos_cotizados' => json_decode($this->productos_cotizados, true)
            ]);
        }

        // Calcular totales automáticamente si no vienen
        if ($this->has('productos_cotizados') && is_array($this->productos_cotizados)) {
            $total_neto = 0;
            
            foreach ($this->productos_cotizados as $producto) {
                $cantidad = floatval($producto['cantidad'] ?? 0);
                $precio_unitario = floatval($producto['precio_unitario'] ?? 0);
                $descuento = floatval($producto['descuento'] ?? 0);
                
                $subtotal = $cantidad * $precio_unitario;
                $subtotal_con_descuento = $subtotal - ($subtotal * $descuento / 100);
                $total_neto += $subtotal_con_descuento;
            }
            
            $iva = $total_neto * 0.19;
            $total_con_iva = $total_neto + $iva;
            
            $this->merge([
                'total_neto' => round($total_neto, 2),
                'iva' => round($iva, 2),
                'total_con_iva' => round($total_con_iva, 2)
            ]);
        }

        \Log::info('📊 Datos después de preparar:', $this->all());
    }
}