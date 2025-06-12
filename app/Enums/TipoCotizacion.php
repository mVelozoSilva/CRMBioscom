<?php
// app/Enums/TipoCotizacion.php

namespace App\Enums;

enum TipoCotizacion: string
{
    case LICITACION_PUBLICA = 'LICITACION_PUBLICA';
    case COMPRA_AGIL = 'COMPRA_AGIL';
    case CONSULTA_PRESUPUESTO = 'CONSULTA_PRESUPUESTO';
    case COTIZACION_INFORMATIVA = 'COTIZACION_INFORMATIVA';
    case COTIZACION_PRECIERRE = 'COTIZACION_PRECIERRE';
    case RECOMPRA_RECURRENTE = 'RECOMPRA_RECURRENTE';
    case ACTUALIZACION_REGULAR = 'ACTUALIZACION_REGULAR';
    case MANTENCION_PREVENTIVA = 'MANTENCION_PREVENTIVA';
    case MANTENCION_CORRECTIVA = 'MANTENCION_CORRECTIVA';
    case COTIZACION_REVENDEDOR = 'COTIZACION_REVENDEDOR';
    
    public function label(): string
    {
        return match($this) {
            self::LICITACION_PUBLICA => 'Licitación Pública',
            self::COMPRA_AGIL => 'Compra Ágil',
            self::CONSULTA_PRESUPUESTO => 'Consulta de Presupuesto',
            self::COTIZACION_INFORMATIVA => 'Cotización Informativa',
            self::COTIZACION_PRECIERRE => 'Cotización Pre-cierre',
            self::RECOMPRA_RECURRENTE => 'Recompra Recurrente',
            self::ACTUALIZACION_REGULAR => 'Actualización Regular',
            self::MANTENCION_PREVENTIVA => 'Mantención Preventiva',
            self::MANTENCION_CORRECTIVA => 'Mantención Correctiva',
            self::COTIZACION_REVENDEDOR => 'Cotización Revendedor',
        };
    }
    
    public function tipoCliente(): string
    {
        return match($this) {
            self::LICITACION_PUBLICA, 
            self::COMPRA_AGIL, 
            self::CONSULTA_PRESUPUESTO => 'publico',
            
            self::COTIZACION_INFORMATIVA,
            self::COTIZACION_PRECIERRE,
            self::RECOMPRA_RECURRENTE,
            self::ACTUALIZACION_REGULAR => 'privado',
            
            self::COTIZACION_REVENDEDOR => 'revendedor',
            
            self::MANTENCION_PREVENTIVA,
            self::MANTENCION_CORRECTIVA => 'todos',
        };
    }
    
    public function configuracionSeguimientoPorDefecto(): array
    {
        return match($this) {
            self::LICITACION_PUBLICA => [
                'modalidad' => ModalidadSeguimiento::REGULAR,
                'dias_verde' => 30,
                'dias_amarillo' => 120,
                'dias_rojo' => 121,
                'max_intentos' => 8,
                'prioridad_triaje' => 90
            ],
            self::COMPRA_AGIL => [
                'modalidad' => ModalidadSeguimiento::INTENSIVO,
                'dias_verde' => 15,
                'dias_amarillo' => 60,
                'dias_rojo' => 61,
                'max_intentos' => 6,
                'prioridad_triaje' => 85
            ],
            self::CONSULTA_PRESUPUESTO => [
                'modalidad' => ModalidadSeguimiento::CONDICIONAL,
                'dias_verde' => 30,
                'dias_amarillo' => 60,
                'dias_rojo' => 90,
                'max_intentos' => 4,
                'prioridad_triaje' => 60
            ],
            self::COTIZACION_INFORMATIVA => [
                'modalidad' => ModalidadSeguimiento::REGULAR,
                'dias_verde' => 7,
                'dias_amarillo' => 21,
                'dias_rojo' => 22,
                'max_intentos' => 5,
                'prioridad_triaje' => 70
            ],
            self::COTIZACION_PRECIERRE => [
                'modalidad' => ModalidadSeguimiento::INTENSIVO,
                'dias_verde' => 2,
                'dias_amarillo' => 7,
                'dias_rojo' => 8,
                'max_intentos' => 7,
                'prioridad_triaje' => 95
            ],
            self::RECOMPRA_RECURRENTE => [
                'modalidad' => ModalidadSeguimiento::REGULAR,
                'dias_verde' => 3,
                'dias_amarillo' => 10,
                'dias_rojo' => 11,
                'max_intentos' => 3,
                'prioridad_triaje' => 40
            ],
            self::ACTUALIZACION_REGULAR => [
                'modalidad' => ModalidadSeguimiento::REGULAR,
                'dias_verde' => 5,
                'dias_amarillo' => 15,
                'dias_rojo' => 16,
                'max_intentos' => 2,
                'prioridad_triaje' => 20
            ],
            self::MANTENCION_PREVENTIVA => [
                'modalidad' => ModalidadSeguimiento::REGULAR,
                'dias_verde' => 7,
                'dias_amarillo' => 21,
                'dias_rojo' => 22,
                'max_intentos' => 4,
                'prioridad_triaje' => 50
            ],
            self::MANTENCION_CORRECTIVA => [
                'modalidad' => ModalidadSeguimiento::MINIMO,
                'dias_verde' => 30,
                'dias_amarillo' => 60,
                'dias_rojo' => 61,
                'max_intentos' => 10,
                'prioridad_triaje' => 100
            ],
            self::COTIZACION_REVENDEDOR => [
                'modalidad' => ModalidadSeguimiento::REGULAR,
                'dias_verde' => 7,
                'dias_amarillo' => 21,
                'dias_rojo' => 22,
                'max_intentos' => 4,
                'prioridad_triaje' => 55
            ],
        };
    }
    
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return [$case->value => $case->label()];
        })->toArray();
    }
}