<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seguimiento;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Cotizacion;
use Carbon\Carbon;

class SeguimientosSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar seguimientos existentes
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Seguimiento::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Obtener los IDs reales existentes
        $clientesIds = Cliente::pluck('id')->toArray();
        $vendedoresIds = User::pluck('id')->toArray();
        $cotizacionesIds = Cotizacion::pluck('id')->toArray();

        $this->command->info('IDs encontrados:');
        $this->command->info('Clientes: ' . implode(', ', $clientesIds));
        $this->command->info('Vendedores: ' . implode(', ', $vendedoresIds));
        $this->command->info('Cotizaciones: ' . count($cotizacionesIds));

        if (empty($clientesIds)) {
            $this->command->error('No hay clientes en la base de datos. Ejecuta primero el seeder de clientes.');
            return;
        }

        if (empty($vendedoresIds)) {
            $this->command->error('No hay usuarios en la base de datos. Creando usuario demo...');
            $vendedor = User::create([
                'name' => 'Vendedor Demo',
                'email' => 'vendedor@bioscom.cl',
                'password' => bcrypt('password'),
            ]);
            $vendedoresIds = [$vendedor->id];
        }

        $estados = ['pendiente', 'en_proceso', 'completado', 'vencido', 'reprogramado'];
        $prioridades = ['baja', 'media', 'alta', 'urgente'];

        $this->command->info('Creando seguimientos con datos reales...');

        // Crear seguimientos específicos para testing
        $seguimientos = [
            // ATRASADOS (críticos)
            [
                'cliente_id' => $clientesIds[0],
                'vendedor_id' => $vendedoresIds[0],
                'estado' => 'pendiente',
                'prioridad' => 'urgente',
                'proxima_gestion' => Carbon::now()->subDays(5),
                'notas' => 'CRÍTICO: Seguimiento atrasado 5 días - Cliente esperando respuesta'
            ],
            [
                'cliente_id' => $clientesIds[1] ?? $clientesIds[0],
                'vendedor_id' => $vendedoresIds[0],
                'estado' => 'en_proceso',
                'prioridad' => 'alta',
                'proxima_gestion' => Carbon::now()->subDays(3),
                'ultima_gestion' => Carbon::now()->subDays(8),
                'resultado_ultima_gestion' => 'Cliente solicitó cotización actualizada',
                'notas' => 'Pendiente envío de nueva propuesta económica'
            ],
            [
                'cliente_id' => $clientesIds[2] ?? $clientesIds[0],
                'vendedor_id' => $vendedoresIds[0],
                'estado' => 'vencido',
                'prioridad' => 'alta',
                'proxima_gestion' => Carbon::now()->subDays(2),
                'notas' => 'Cliente no respondió llamadas - Reagendar urgente'
            ],

            // HOY (urgentes)
            [
                'cliente_id' => $clientesIds[3] ?? $clientesIds[0],
                'vendedor_id' => $vendedoresIds[0],
                'estado' => 'pendiente',
                'prioridad' => 'urgente',
                'proxima_gestion' => Carbon::today(),
                'notas' => 'Reunión programada para HOY - Confirmar asistencia'
            ],
            [
                'cliente_id' => $clientesIds[4] ?? $clientesIds[0],
                'vendedor_id' => $vendedoresIds[0],
                'estado' => 'en_proceso',
                'prioridad' => 'alta',
                'proxima_gestion' => Carbon::today(),
                'ultima_gestion' => Carbon::yesterday(),
                'resultado_ultima_gestion' => 'Cliente muy interesado, solicita propuesta formal',
                'notas' => 'Enviar propuesta antes de las 15:00 hrs'
            ],

            // PRÓXIMOS DÍAS
            [
                'cliente_id' => $clientesIds[0],
                'vendedor_id' => $vendedoresIds[0],
                'estado' => 'pendiente',
                'prioridad' => 'media',
                'proxima_gestion' => Carbon::tomorrow(),
                'notas' => 'Primera visita comercial - Presentar catálogo completo'
            ],
            [
                'cliente_id' => $clientesIds[1] ?? $clientesIds[0],
                'vendedor_id' => $vendedoresIds[0],
                'estado' => 'reprogramado',
                'prioridad' => 'alta',
                'proxima_gestion' => Carbon::now()->addDays(3),
                'ultima_gestion' => Carbon::now()->subDays(2),
                'resultado_ultima_gestion' => 'Cliente reprogramó por agenda ocupada',
                'notas' => 'Reagendado - Cliente confirmó interés en equipos de imagenología'
            ],

            // COMPLETADOS
            [
                'cliente_id' => $clientesIds[2] ?? $clientesIds[0],
                'vendedor_id' => $vendedoresIds[0],
                'estado' => 'completado',
                'prioridad' => 'alta',
                'proxima_gestion' => Carbon::today(),
                'ultima_gestion' => Carbon::today(),
                'resultado_ultima_gestion' => '¡VENTA CERRADA! Cliente firmó contrato por $35.000.000',
                'notas' => 'Venta exitosa - Programar entrega e instalación'
            ],
            [
                'cliente_id' => $clientesIds[3] ?? $clientesIds[0],
                'vendedor_id' => $vendedoresIds[0],
                'estado' => 'completado',
                'prioridad' => 'media',
                'proxima_gestion' => Carbon::today(),
                'ultima_gestion' => Carbon::today(),
                'resultado_ultima_gestion' => 'Seguimiento post-venta realizado exitosamente',
                'notas' => 'Cliente satisfecho con equipos instalados el mes pasado'
            ]
        ];

        // Crear los seguimientos base
        foreach ($seguimientos as $seguimientoData) {
            Seguimiento::create($seguimientoData);
        }

        // Crear seguimientos adicionales aleatorios distribuidos en los clientes reales
        for ($i = 0; $i < 40; $i++) {
            $fechaBase = Carbon::now()->addDays(rand(-15, 30));
            
            Seguimiento::create([
                'cliente_id' => $clientesIds[array_rand($clientesIds)], // Cliente real aleatorio
                'cotizacion_id' => !empty($cotizacionesIds) && rand(0, 1) ? $cotizacionesIds[array_rand($cotizacionesIds)] : null,
                'vendedor_id' => $vendedoresIds[array_rand($vendedoresIds)],
                'estado' => $estados[array_rand($estados)],
                'prioridad' => $prioridades[array_rand($prioridades)],
                'ultima_gestion' => rand(0, 1) ? $fechaBase->copy()->subDays(rand(1, 10)) : null,
                'proxima_gestion' => $fechaBase,
                'notas' => $this->generarNotaAleatoria(),
                'resultado_ultima_gestion' => rand(0, 1) ? $this->generarResultadoAleatorio() : null,
            ]);
        }

        $totalSeguimientos = Seguimiento::count();
        $this->command->info("✅ {$totalSeguimientos} seguimientos creados exitosamente");
        
        // Mostrar estadísticas
        $atrasados = Seguimiento::where('proxima_gestion', '<', Carbon::today())
                                ->whereNotIn('estado', ['completado'])
                                ->count();
        $hoy = Seguimiento::where('proxima_gestion', Carbon::today())->count();
        $proximos = Seguimiento::whereBetween('proxima_gestion', [
            Carbon::tomorrow(),
            Carbon::now()->addDays(7)
        ])->count();

        $this->command->info('');
        $this->command->line('<fg=red>📊 ESTADÍSTICAS GENERADAS:</>');
        $this->command->line("🔴 Atrasados: {$atrasados} (CRÍTICOS)");
        $this->command->line("🟡 Para hoy: {$hoy}");
        $this->command->line("🔵 Próximos 7 días: {$proximos}");
        $this->command->line("✅ Total: {$totalSeguimientos}");
    }

    private function generarNotaAleatoria()
    {
        $notas = [
            'Cliente interesado en equipamiento de imagenología nueva generación',
            'Solicita cotización para renovación completa de equipos',
            'Pendiente validación técnica del comité médico',
            'Cliente requiere condiciones especiales de financiamiento',
            'Evaluando propuesta junto con otras 2 alternativas del mercado',
            'Necesita aprobación del directorio para compras sobre $20M',
            'Solicita demostración in-situ del equipo en sus instalaciones',
            'Cliente satisfecho con propuesta inicial, solicita ajustes menores',
            'Requiere especificaciones técnicas adicionales para comité',
            'Evaluando presupuesto para ejecución en próximo trimestre',
            'Cliente leal con historial de compras, alta probabilidad de cierre',
            'Primera aproximación comercial, cliente receptivo',
            'Seguimiento post-venta de equipos instalados hace 6 meses',
            'Cliente solicita capacitación técnica para nuevo personal',
            'Evaluando ampliación de contrato de mantenimiento anual'
        ];

        return $notas[array_rand($notas)];
    }

    private function generarResultadoAleatorio()
    {
        $resultados = [
            'Cliente solicitó tiempo adicional para evaluar propuesta con directorio',
            'Reunión muy exitosa, enviarán respuesta definitiva la próxima semana',
            'Requieren ajustes en propuesta económica, solicitan descuento adicional',
            'Cliente muy interesado, solicita referencias de otros hospitales',
            'Programada visita técnica especializada para la siguiente semana',
            'Cliente comparando activamente con 2 proveedores competencia',
            'Solicitan demostración práctica del equipo con su personal técnico',
            'Propuesta en evaluación, pendiente aprobación gerencia general',
            'Cliente completamente satisfecho con términos y condiciones',
            'Requieren condiciones especiales de pago a 24 meses sin interés',
            'Muy interesados en contrato integral: equipos + mantenimiento + capacitación',
            'Solicitan incluir capacitación técnica intensiva en la propuesta',
            'Cliente decidió postergar decisión hasta próximo período presupuestario',
            'Reunión reagendada por conflictos en agenda del director médico',
            'Propuesta aceptada preliminarmente, iniciando proceso de compra formal'
        ];

        return $resultados[array_rand($resultados)];
    }
}