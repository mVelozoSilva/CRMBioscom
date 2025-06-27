<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobranza extends Model
{
    use HasFactory;

    protected $table = 'cobranzas'; // Aseguramos que use la tabla correcta

    // Campos que se pueden llenar en masa (formulario, API, etc.)
    protected $fillable = [
        'id_factura',
        'numero_factura',
        'cliente_id',
        'usuario_asignado_id',
        'monto_adeudado',
        'monto_original',
        'monto_pagado',
        'fecha_emision',
        'fecha_vencimiento',
        'ultima_gestion',
        'proxima_gestion',
        'estado',
        'prioridad',
        'numero_orden_transporte',
        'numero_orden_compra',
        'intentos_gestion',
        'max_intentos_gestion',
        'notas',
        'historial_interacciones',
        'observaciones_cliente',
        'metodo_contacto_preferido',
        'motivo_estado',
        'fecha_ultimo_pago',
        'referencia_pago'
    ];

    // Cast para transformar automáticamente campos JSON
    protected $casts = [
        'historial_interacciones' => 'array',
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'ultima_gestion' => 'datetime',
        'proxima_gestion' => 'date',
        'fecha_ultimo_pago' => 'datetime',
    ];

    // Relaciones

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuarioAsignado()
    {
        return $this->belongsTo(User::class, 'usuario_asignado_id');
    }
}
