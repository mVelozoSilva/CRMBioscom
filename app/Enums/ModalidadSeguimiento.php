<?php
// app/Enums/ModalidadSeguimiento.php

namespace App\Enums;

enum ModalidadSeguimiento: string
{
    case INTENSIVO = 'INTENSIVO';
    case REGULAR = 'REGULAR';
    case CONDICIONAL = 'CONDICIONAL';
    case MINIMO = 'MINIMO';
    
    public function label(): string
    {
        return match($this) {
            self::INTENSIVO => 'Intensivo',
            self::REGULAR => 'Regular',
            self::CONDICIONAL => 'Condicional',
            self::MINIMO => 'Mínimo',
        };
    }
    
    public function descripcion(): string
    {
        return match($this) {
            self::INTENSIVO => 'Seguimiento diario con alta prioridad',
            self::REGULAR => 'Seguimiento estándar cada 2-3 días',
            self::CONDICIONAL => 'Seguimiento semanal según condiciones',
            self::MINIMO => 'Seguimiento quincenal o mensual',
        };
    }
    
    public function multiplicadorUrgencia(): float
    {
        return match($this) {
            self::INTENSIVO => 2.0,
            self::REGULAR => 1.0,
            self::CONDICIONAL => 0.7,
            self::MINIMO => 0.3,
        };
    }
    
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return [$case->value => $case->label()];
        })->toArray();
    }
}