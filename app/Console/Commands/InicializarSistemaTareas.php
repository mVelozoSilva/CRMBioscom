<?php

namespace App\Console\Commands;

use App\Models\ConfiguracionSeguimiento;
use App\Models\Cotizacion;
use Illuminate\Console\Command;

class InicializarSistemaTareas extends Command
{
    protected $signature = 'bioscom:inicializar-tareas';
    protected $description = 'Inicializa el sistema de tareas y seguimientos del CRM Bioscom';
    
    public function handle(): int
    {
        $this->info('Inicializando sistema de tareas Bioscom...');
        
        // 1. Crear configuraciones por defecto
        $this->info('Creando configuraciones de seguimiento...');
        ConfiguracionSeguimiento::crearConfiguracionesPorDefecto();
        
        // 2. Inicializar seguimientos para cotizaciones existentes
        $this->info('Inicializando seguimientos para cotizaciones existentes...');
        $cotizaciones = Cotizacion::whereNull('tipo_cotizacion')->get();
        
        if ($cotizaciones->count() > 0) {
            $progreso = $this->output->createProgressBar($cotizaciones->count());
            
            foreach ($cotizaciones as $cotizacion) {
                $cotizacion->update(['tipo_cotizacion' => 'COTIZACION_INFORMATIVA']);
                $progreso->advance();
            }
            
            $progreso->finish();
            $this->newLine();
        }
        
        $this->info('Sistema de tareas inicializado exitosamente!');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Configuraciones creadas', ConfiguracionSeguimiento::count()],
                ['Cotizaciones actualizadas', $cotizaciones->count()],
            ]
        );
        
        return Command::SUCCESS;
    }
}