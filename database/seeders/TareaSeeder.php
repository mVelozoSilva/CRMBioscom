<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tarea;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Seguimiento;
use Carbon\Carbon;

class TareaSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();
        $clienteIds = Cliente::pluck('id')->toArray();
        $cotizacionIds = Cotizacion::pluck('id')->toArray();
        $seguimientoIds = Seguimiento::pluck('id')->toArray();

        if (empty($userIds)) {
            $this->command->warn('No hay usuarios en la base de datos. Creando tareas sin asignación específica.');
            return;
        }

        $plantillasTareas = [
            [
                'titulo' => 'Seguimiento cotización equipo radiológico',
                'descripcion' => 'Contactar cliente para conocer decisión sobre propuesta de equipo de rayos X',
                'tipo' => 'seguimiento',
                'prioridad' => 'alta'
            ],
            [
                'titulo' => 'Seguimiento propuesta ventiladores',
                'descripcion' => 'Llamar al jefe de UCI para revisar propuesta de ventiladores mecánicos',
                'tipo' => 'seguimiento',
                'prioridad' => 'media'
            ],
            [
                'titulo' => 'Preparar cotización monitores paciente',
                'descripcion' => 'Elaborar propuesta para 20 monitores de signos vitales para nuevo pabellón',
                'tipo' => 'cotizacion',
                'prioridad' => 'alta'
            ],
            [
                'titulo' => 'Mantención preventiva equipo anestesia',
                'descripcion' => 'Programar y ejecutar mantención trimestral de máquina de anestesia',
                'tipo' => 'mantencion',
                'prioridad' => 'alta'
            ],
            [
                'titulo' => 'Reunión con jefe de adquisiciones',
                'descripcion' => 'Presentar propuesta anual de equipamiento médico',
                'tipo' => 'reunion',
                'prioridad' => 'alta'
            ],
            [
                'titulo' => 'Gestión cobranza factura vencida',
                'descripcion' => 'Contactar administración para pago de factura 30 días vencida',
                'tipo' => 'cobranza',
                'prioridad' => 'urgente'
            ],
            [
                'titulo' => 'Actualizar inventario insumos',
                'descripcion' => 'Revisar y actualizar stock de insumos médicos en bodega',
                'tipo' => 'administrativa',
                'prioridad' => 'media'
            ],
            [
                'titulo' => 'Llamar proveedor internacional',
                'descripcion' => 'Coordinar importación de equipos especializados',
                'tipo' => 'llamada',
                'prioridad' => 'alta'
            ]
        ];

        $tareasCreadas = 0;
        
        for ($i = 0; $i < 50; $i++) {
            $plantilla = $plantillasTareas[array_rand($plantillasTareas)];
            
            $fechaBase = match($i % 10) {
                0, 1, 2 => Carbon::today(),
                3, 4 => Carbon::yesterday(),
                5, 6 => Carbon::tomorrow(),
                7 => Carbon::today()->addDays(2),
                8 => Carbon::today()->addDays(rand(3, 7)),
                9 => Carbon::today()->addDays(rand(8, 14)),
            };

            $fechaVencimiento = $fechaBase->copy()->addHours(rand(-12, 12));

            $estado = 'pendiente';
            if ($fechaVencimiento < Carbon::now()) {
                $estado = match(rand(1, 4)) {
                    1 => 'vencida',
                    2 => 'completada',
                    3 => 'en_progreso',
                    4 => 'pospuesta'
                };
            }

            $tareaData = [
                'titulo' => $plantilla['titulo'] . ' #' . ($i + 1),
                'descripcion' => $plantilla['descripcion'],
                'usuario_asignado_id' => $userIds[array_rand($userIds)],
                'usuario_creador_id' => $userIds[array_rand($userIds)],
                'tipo' => $plantilla['tipo'],
                'origen' => match(rand(1, 4)) {
                    1 => 'distribucion_automatica',
                    2 => 'sistema',
                    3, 4 => 'manual'
                },
                'fecha_vencimiento' => $fechaVencimiento->toDateString(),
                'hora_estimada' => sprintf('%02d:%02d', rand(8, 17), rand(0, 3) * 15),
                'duracion_estimada_minutos' => rand(30, 120),
                'estado' => $estado,
                'prioridad' => $plantilla['prioridad'],
                'es_distribuida_automaticamente' => rand(1, 4) == 1,
                'notas' => 'Tarea de prueba generada automáticamente',
                'metadatos' => json_encode(['test' => true, 'numero' => $i + 1])
            ];

            if (!empty($clienteIds) && rand(1, 3) == 1) {
                $tareaData['cliente_id'] = $clienteIds[array_rand($clienteIds)];
            }
            
            if (!empty($cotizacionIds) && rand(1, 2) == 1) {
                $tareaData['cotizacion_id'] = $cotizacionIds[array_rand($cotizacionIds)];
            }
            
            if (!empty($seguimientoIds) && rand(1, 2) == 1) {
                $tareaData['seguimiento_id'] = $seguimientoIds[array_rand($seguimientoIds)];
            }

            if ($estado == 'completada') {
                $tareaData['resultado'] = 'Tarea completada exitosamente en modo de prueba';
                $tareaData['fecha_completada'] = $fechaVencimiento->copy()->addHours(rand(1, 8));
            }

            Tarea::create($tareaData);
            $tareasCreadas++;
        }

        $this->command->info("✅ Se crearon {$tareasCreadas} tareas de prueba para el módulo de Agenda.");
    }
}