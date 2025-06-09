<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    /**
     * Ejecutar el seeder - Compatible con tu estructura de productos
     */
    public function run(): void
    {
        // Producto 1: Monitor de Signos Vitales
        Producto::create([
            'nombre' => 'Monitor de Signos Vitales MSV-2000',
            'descripcion' => 'Monitor multiparamétrico para UCI con pantalla táctil de 15 pulgadas. Monitoreo continuo de ECG, presión arterial, SpO2, temperatura y frecuencia respiratoria.',
            'precio_neto' => 2500000.00, // $2.500.000 CLP
            'categoria' => 'Equipos de Monitoreo',
            'imagenes' => [
                'https://ejemplo.com/monitor-signos-vitales-1.jpg',
                'https://ejemplo.com/monitor-signos-vitales-2.jpg'
            ],
            'accesorios' => [
                'Brazalete para presión arterial adulto',
                'Sensor de SpO2 reutilizable',
                'Cable ECG de 5 derivaciones',
                'Termómetro digital',
                'Manual de usuario en español',
                'Garantía técnica 2 años'
            ],
            'opcionales' => [
                'Brazalete pediátrico (+$50.000)',
                'Sensor de CO2 (+$300.000)',
                'Módulo de presión invasiva (+$450.000)',
                'Carro móvil con ruedas (+$120.000)'
            ]
        ]);

        // Producto 2: Desfibrilador
        Producto::create([
            'nombre' => 'Desfibrilador Automático DEA-Pro',
            'descripcion' => 'Desfibrilador automático externo con análisis automático del ritmo cardíaco. Ideal para emergencias médicas y RCP.',
            'precio_neto' => 1800000.00, // $1.800.000 CLP
            'categoria' => 'Equipos de Emergencia',
            'imagenes' => [
                'https://ejemplo.com/desfibrilador-1.jpg',
                'https://ejemplo.com/desfibrilador-2.jpg'
            ],
            'accesorios' => [
                'Electrodos adulto (2 pares)',
                'Batería de larga duración',
                'Maletín de transporte',
                'Manual de operación',
                'Certificado de calibración',
                'Garantía 3 años'
            ],
            'opcionales' => [
                'Electrodos pediátricos (+$80.000)',
                'Batería adicional (+$150.000)',
                'Curso de capacitación (+$200.000)',
                'Soporte mural (+$75.000)'
            ]
        ]);

        // Producto 3: Ventilador Mecánico
        Producto::create([
            'nombre' => 'Ventilador Mecánico VM-Elite',
            'descripcion' => 'Ventilador mecánico de alta gama para cuidados intensivos con modos avanzados de ventilación. Incluye monitoreo de parámetros respiratorios.',
            'precio_neto' => 15000000.00, // $15.000.000 CLP
            'categoria' => 'Equipos de Ventilación',
            'imagenes' => [
                'https://ejemplo.com/ventilador-1.jpg',
                'https://ejemplo.com/ventilador-2.jpg',
                'https://ejemplo.com/ventilador-3.jpg'
            ],
            'accesorios' => [
                'Circuito paciente adulto',
                'Humidificador calentado',
                'Sensores de flujo y presión',
                'Brazo articulado',
                'Software de monitoreo',
                'Garantía técnica 5 años'
            ],
            'opcionales' => [
                'Circuito pediátrico (+$120.000)',
                'Módulo de capnografía (+$500.000)',
                'Carro especializado (+$300.000)',
                'Capacitación especializada (+$400.000)'
            ]
        ]);

        // Producto 4: Electrocardiógrafo
        Producto::create([
            'nombre' => 'Electrocardiógrafo ECG-12D',
            'descripcion' => 'Electrocardiógrafo digital de 12 derivaciones con interpretación automática. Pantalla LCD y memoria para almacenar registros.',
            'precio_neto' => 800000.00, // $800.000 CLP
            'categoria' => 'Equipos de Diagnóstico',
            'imagenes' => [
                'https://ejemplo.com/ecg-1.jpg',
                'https://ejemplo.com/ecg-2.jpg'
            ],
            'accesorios' => [
                'Electrodos desechables (pack 100)',
                'Cables de derivaciones',
                'Papel térmico (10 rollos)',
                'Gel conductor',
                'Manual de interpretación',
                'Garantía 2 años'
            ],
            'opcionales' => [
                'Software de análisis avanzado (+$200.000)',
                'Conexión WiFi (+$100.000)',
                'Carro móvil (+$150.000)',
                'Electrodos pediátricos (+$50.000)'
            ]
        ]);

        // Producto 5: Bomba de Infusión
        Producto::create([
            'nombre' => 'Bomba de Infusión BI-Smart',
            'descripcion' => 'Bomba de infusión volumétrica con pantalla LCD y alarmas de seguridad. Control preciso de flujo y volumen.',
            'precio_neto' => 450000.00, // $450.000 CLP
            'categoria' => 'Equipos de Infusión',
            'imagenes' => [
                'https://ejemplo.com/bomba-infusion-1.jpg'
            ],
            'accesorios' => [
                'Equipo de infusión (pack 10)',
                'Soporte con ruedas',
                'Batería recargable',
                'Cable de alimentación',
                'Manual del usuario',
                'Garantía 18 meses'
            ],
            'opcionales' => [
                'Equipos de micro-infusión (+$30.000)',
                'Batería adicional (+$80.000)',
                'Soporte doble (+$120.000)',
                'Calibración anual (+$50.000)'
            ]
        ]);

        // Producto 6: Oxímetro de Pulso
        Producto::create([
            'nombre' => 'Oxímetro de Pulso OX-Digit',
            'descripcion' => 'Oxímetro de pulso portátil con pantalla OLED y alarmas audibles. Medición precisa de SpO2 y frecuencia cardíaca.',
            'precio_neto' => 85000.00, // $85.000 CLP
            'categoria' => 'Equipos de Monitoreo',
            'imagenes' => [
                'https://ejemplo.com/oximetro-1.jpg'
            ],
            'accesorios' => [
                'Sensor de dedo adulto',
                'Baterías alcalinas',
                'Cordón de seguridad',
                'Estuche protector',
                'Manual de usuario',
                'Garantía 1 año'
            ],
            'opcionales' => [
                'Sensor pediátrico (+$25.000)',
                'Baterías recargables (+$15.000)',
                'Software de descarga de datos (+$40.000)',
                'Sensor de oreja (+$30.000)'
            ]
        ]);

        // Producto 7: Insumos - Guantes
        Producto::create([
            'nombre' => 'Guantes de Nitrilo Talla M (Caja 100 unidades)',
            'descripcion' => 'Guantes desechables de nitrilo sin polvo, libres de látex. Resistentes a perforaciones y productos químicos.',
            'precio_neto' => 12000.00, // $12.000 CLP
            'categoria' => 'Insumos Médicos',
            'imagenes' => [
                'https://ejemplo.com/guantes-nitrilo-1.jpg'
            ],
            'accesorios' => [
                'Caja con 100 guantes',
                'Certificado de calidad',
                'Hoja de seguridad'
            ],
            'opcionales' => [
                'Talla S (misma caja)',
                'Talla L (misma caja)',
                'Talla XL (misma caja)',
                'Color azul (+$1.000)'
            ]
        ]);

        // Producto 8: Termómetro Digital
        Producto::create([
            'nombre' => 'Termómetro Digital Infrarrojo TD-200',
            'descripcion' => 'Termómetro sin contacto con tecnología infrarroja. Medición rápida y precisa en 1 segundo.',
            'precio_neto' => 45000.00, // $45.000 CLP
            'categoria' => 'Equipos de Diagnóstico',
            'imagenes' => [
                'https://ejemplo.com/termometro-1.jpg'
            ],
            'accesorios' => [
                'Baterías AAA (2 unidades)',
                'Manual de usuario',
                'Estuche protector',
                'Garantía 1 año'
            ],
            'opcionales' => [
                'Soporte de pared (+$15.000)',
                'Baterías recargables (+$8.000)',
                'Calibración profesional (+$25.000)'
            ]
        ]);

        echo "✅ Se han creado " . Producto::count() . " productos de ejemplo\n";
        echo "📦 Incluye equipos médicos variados: monitoreo, emergencia, diagnóstico e insumos\n";
        echo "💰 Rango de precios: desde $12.000 hasta $15.000.000 CLP\n";
    }
}