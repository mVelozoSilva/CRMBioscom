<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cobranza;
use App\Models\Cliente;
use App\Models\User;
use Carbon\Carbon;

class CobranzaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener clientes y usuarios existentes
        $clientes = Cliente::all();
        $usuarios = User::all();

        if ($clientes->isEmpty() || $usuarios->isEmpty()) {
            $this->command->warn('No hay clientes o usuarios suficientes. Ejecuta primero los seeders de clientes y usuarios.');
            return;
        }

        // Estados con sus probabilidades (realista para cobranzas)
        $estadosConPeso = [
            'pendiente' => 35,      // 35%
            'en_gestion' => 25,     // 25%
            'vencida' => 20,        // 20%
            'pagada' => 15,         // 15%
            'parcialmente_pagada' => 3, // 3%
            'en_disputa' => 1,      // 1%
            'renegociada' => 1      // 1%
        ];

        // Prioridades con pesos
        $prioridadesConPeso = [
            'media' => 50,
            'alta' => 30,
            'baja' => 15,
            'urgente' => 5
        ];

        // Métodos de contacto
        $metodosContacto = ['telefono', 'email', 'whatsapp', 'presencial', 'carta'];

        // Generar cobranzas
        for ($i = 1; $i <= 75; $i++) {
            $cliente = $clientes->random();
            $usuario = $usuarios->random();
            
            // Generar fechas realistas
            $fechaEmision = Carbon::now()->subDays(rand(1, 180));
            $fechaVencimiento = $fechaEmision->copy()->addDays(rand(30, 90));
            
            // Seleccionar estado basado en probabilidades
            $estado = $this->seleccionarConPeso($estadosConPeso);
            $prioridad = $this->seleccionarConPeso($prioridadesConPeso);
            
            // Generar montos realistas para equipamiento médico
            $montoOriginal = $this->generarMontoRealista();
            $montoPagado = 0;
            $montoAdeudado = $montoOriginal;
            
            // Ajustar montos según el estado
            if ($estado === 'pagada') {
                $montoPagado = $montoOriginal;
                $montoAdeudado = 0;
            } elseif ($estado === 'parcialmente_pagada') {
                $montoPagado = $montoOriginal * (rand(10, 80) / 100);
                $montoAdeudado = $montoOriginal - $montoPagado;
            }
            
            // Determinar si está vencida por fecha
            if ($fechaVencimiento->isPast() && !in_array($estado, ['pagada', 'incobrable'])) {
                $estado = 'vencida';
                if (rand(1, 100) <= 30) { // 30% chance de prioridad alta para vencidas
                    $prioridad = 'alta';
                }
            }
            
            $cobranza = Cobranza::create([
                'id_factura' => 'FACT-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'numero_factura' => 'F' . date('Y') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'cliente_id' => $cliente->id,
                'usuario_asignado_id' => $usuario->id,
                'monto_adeudado' => $montoAdeudado,
                'monto_original' => $montoOriginal,
                'monto_pagado' => $montoPagado,
                'fecha_emision' => $fechaEmision,
                'fecha_vencimiento' => $fechaVencimiento,
                'estado' => $estado,
                'prioridad' => $prioridad,
                'numero_orden_transporte' => rand(1, 100) <= 70 ? 'OT-' . rand(1000, 9999) : null,
                'numero_orden_compra' => rand(1, 100) <= 60 ? 'OC-' . rand(10000, 99999) : null,
                'metodo_contacto_preferido' => fake()->randomElement($metodosContacto),
                'intentos_gestion' => rand(0, 5),
                'max_intentos_gestion' => rand(3, 8),
                'notas' => $this->generarNotasRealistas($estado, $cliente->nombre_institucion),
                'observaciones_cliente' => rand(1, 100) <= 40 ? $this->generarObservacionesCliente() : null,
                'ultima_gestion' => rand(1, 100) <= 70 ? Carbon::now()->subDays(rand(1, 30)) : null,
                'proxima_gestion' => $this->generarProximaGestion($estado),
                'fecha_ultimo_pago' => $montoPagado > 0 ? Carbon::now()->subDays(rand(1, 60)) : null,
                'referencia_pago' => $montoPagado > 0 ? 'REF-' . rand(100000, 999999) : null,
                'motivo_estado' => $this->generarMotivoEstado($estado)
            ]);
            
            // Generar historial de interacciones realistas
            $this->generarHistorialInteracciones($cobranza);
        }

        $this->command->info('✅ Se crearon 75 cobranzas de prueba con datos realistas');
    }

    /**
     * Seleccionar elemento basado en pesos/probabilidades
     */
    private function seleccionarConPeso(array $elementos): string
    {
        $random = rand(1, 100);
        $acumulado = 0;
        
        foreach ($elementos as $elemento => $peso) {
            $acumulado += $peso;
            if ($random <= $acumulado) {
                return $elemento;
            }
        }
        
        return array_key_first($elementos);
    }

    /**
     * Generar monto realista para equipamiento médico
     */
    private function generarMontoRealista(): float
    {
        // Simplificar la lógica para evitar errores
        $pesosTipos = [
            'insumos_menores' => 40,
            'equipos_medios' => 35, 
            'equipos_grandes' => 20,
            'equipos_premium' => 5
        ];
        
        $tipoSeleccionado = $this->seleccionarConPeso($pesosTipos);
        
        // Rangos de montos por tipo
        return match($tipoSeleccionado) {
            'insumos_menores' => rand(50000, 300000),
            'equipos_medios' => rand(300000, 2000000), 
            'equipos_grandes' => rand(2000000, 10000000),
            'equipos_premium' => rand(10000000, 50000000),
            default => rand(100000, 1000000)
        };
    }

    /**
     * Generar notas realistas según el estado
     */
    private function generarNotasRealistas(string $estado, string $institucion): string
    {
        $notas = [
            'pendiente' => [
                "Factura emitida, pendiente primera gestión con {$institucion}.",
                "Cliente {$institucion} debe validar internamente antes de procesar pago.",
                "Pendiente confirmación de recepción conforme del equipamiento."
            ],
            'en_gestion' => [
                "En seguimiento activo. Cliente solicita extensión de plazo.",
                "Contacto establecido con área de finanzas de {$institucion}.",
                "Cliente confirma que procesará pago en próxima semana."
            ],
            'vencida' => [
                "Factura vencida. Cliente alega problemas de flujo de caja.",
                "Múltiples intentos de contacto sin respuesta satisfactoria.",
                "Cliente reconoce deuda pero solicita plan de pagos."
            ],
            'pagada' => [
                "Pago recibido y confirmado. Gestión exitosa.",
                "Transferencia recibida según lo acordado.",
                "Cliente cumplió con compromiso de pago en tiempo y forma."
            ],
            'en_disputa' => [
                "Cliente disputa monto por diferencias en especificaciones técnicas.",
                "En proceso de revisión con área técnica y comercial.",
                "Pendiente resolución de garantías de equipamiento."
            ]
        ];
        
        $notasEstado = $notas[$estado] ?? $notas['pendiente'];
        return fake()->randomElement($notasEstado);
    }

    /**
     * Generar observaciones del cliente
     */
    private function generarObservacionesCliente(): string
    {
        $observaciones = [
            "Cliente prefiere contacto solo por las mañanas (9:00-12:00).",
            "Solicitar hablar directamente con Jefe de Administración.",
            "Cliente muy cumplidor, pero procesos internos lentos.",
            "Institución pública - procesos de pago pueden demorar hasta 45 días.",
            "Cliente VIP - manejar con especial cuidado y cortesía.",
            "Contactar preferentemente los martes y jueves.",
            "Cliente sensible al precio - enfatizar calidad y garantías.",
            "Evitar llamadas después de las 16:00 hrs.",
            "Cliente técnico - puede solicitar especificaciones detalladas."
        ];
        
        return fake()->randomElement($observaciones);
    }

    /**
     * Generar próxima gestión según estado
     */
    private function generarProximaGestion(string $estado): ?Carbon
    {
        if (in_array($estado, ['pagada', 'incobrable'])) {
            return null;
        }
        
        $diasAdelante = match($estado) {
            'pendiente' => rand(1, 7),
            'en_gestion' => rand(1, 5),
            'vencida' => rand(1, 3),
            'en_disputa' => rand(7, 14),
            default => rand(1, 7)
        };
        
        return Carbon::now()->addDays($diasAdelante);
    }

    /**
     * Generar motivo del estado
     */
    private function generarMotivoEstado(string $estado): ?string
    {
        $motivos = [
            'vencida' => ['Vencimiento natural del plazo', 'Cliente no respondió gestiones', 'Problemas financieros del cliente'],
            'en_disputa' => ['Diferencias técnicas', 'Monto no coincide', 'Calidad del producto'],
            'renegociada' => ['Solicitud de fraccionamiento', 'Cambio en condiciones', 'Acuerdo mutuo'],
            'pagada' => ['Pago según términos originales', 'Pago anticipado', 'Pago tras gestión']
        ];
        
        if (isset($motivos[$estado])) {
            return fake()->randomElement($motivos[$estado]);
        }
        
        return null;
    }

    /**
     * Generar historial de interacciones
     */
    private function generarHistorialInteracciones(Cobranza $cobranza): void
    {
        $numInteracciones = rand(1, 5);
        $tiposInteraccion = ['llamada', 'email', 'whatsapp', 'presencial'];
        $resultados = ['exitoso', 'sin_respuesta', 'ocupado', 'promesa_pago', 'otros'];
        
        for ($i = 0; $i < $numInteracciones; $i++) {
            $fechaInteraccion = Carbon::now()->subDays(rand(1, 30));
            
            $cobranza->historial_interacciones = array_merge(
                $cobranza->historial_interacciones ?? [],
                [[
                    'fecha' => $fechaInteraccion->toISOString(),
                    'tipo' => fake()->randomElement($tiposInteraccion),
                    'descripcion' => $this->generarDescripcionInteraccion(),
                    'usuario_id' => $cobranza->usuario_asignado_id,
                    'resultado' => fake()->randomElement($resultados),
                    'created_at' => $fechaInteraccion->toISOString()
                ]]
            );
        }
        
        $cobranza->save();
    }

    /**
     * Generar descripción de interacción
     */
    private function generarDescripcionInteraccion(): string
    {
        $descripciones = [
            "Contacto telefónico con área administrativa",
            "Envío de estado de cuenta actualizado",
            "Reunión presencial para revisión de pendientes",
            "Seguimiento vía WhatsApp para confirmar fecha de pago",
            "Coordinación de plan de pagos con administración",
            "Recordatorio de vencimiento próximo",
            "Solicitud de documentos de respaldo",
            "Confirmación de datos bancarios para transferencia"
        ];
        
        return fake()->randomElement($descripciones);
    }
}