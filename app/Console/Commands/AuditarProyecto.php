<?php
// app/Console/Commands/AuditarProyecto.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class AuditarProyecto extends Command
{
    protected $signature = 'bioscom:auditar-proyecto';
    protected $description = 'Genera un reporte completo de auditoría del proyecto CRM Bioscom';
    
    public function handle(): int
    {
        $this->info('🔍 INICIANDO AUDITORÍA COMPLETA DEL CRM BIOSCOM...');
        
        $reportePath = storage_path('app/auditoria_crm_bioscom.md');
        $reporte = $this->generarReporte();
        
        File::put($reportePath, $reporte);
        
        $this->info("✅ Auditoría completada!");
        $this->info("📄 Reporte guardado en: {$reportePath}");
        
        return Command::SUCCESS;
    }
    
    private function generarReporte(): string
    {
        $fecha = now()->format('Y-m-d H:i:s');
        
        return "# 🔍 AUDITORÍA COMPLETA CRM BIOSCOM
Generada: {$fecha}

## 📁 ESTRUCTURA DE ARCHIVOS

{$this->obtenerEstructuraArchivos()}

## 🗄️ INFORMACIÓN DE BASE DE DATOS

{$this->obtenerInfoBaseDatos()}

## 📊 TABLAS Y ESTRUCTURA

{$this->obtenerTablas()}

## 🏗️ MODELOS DETECTADOS

{$this->obtenerModelos()}

## 🎮 CONTROLADORES DETECTADOS

{$this->obtenerControladores()}

## ⚡ COMPONENTES VUE DETECTADOS

{$this->obtenerComponentesVue()}

## 🛣️ RUTAS REGISTRADAS

{$this->obtenerRutas()}

## ⚠️ ANÁLISIS DE ERRORES POTENCIALES

{$this->analizarErrores()}

## 📈 MÉTRICAS DEL PROYECTO

{$this->obtenerMetricas()}
";
    }
    
    private function obtenerEstructuraArchivos(): string
    {
        $estructura = '';
        $this->recorrerDirectorio(base_path(), $estructura);
        return "```\n{$estructura}\n```";
    }
    
    private function recorrerDirectorio($directorio, &$estructura, $nivel = 0)
    {
        $archivos = File::files($directorio);
        $carpetas = File::directories($directorio);
        
        $indent = str_repeat('  ', $nivel);
        
        foreach ($carpetas as $carpeta) {
            $nombre = basename($carpeta);
            if (!in_array($nombre, ['node_modules', 'vendor', '.git', 'storage/app', 'storage/framework'])) {
                $estructura .= "{$indent}📁 {$nombre}/\n";
                $this->recorrerDirectorio($carpeta, $estructura, $nivel + 1);
            }
        }
        
        foreach ($archivos as $archivo) {
            $extension = pathinfo($archivo, PATHINFO_EXTENSION);
            $icono = $this->obtenerIconoArchivo($extension);
            $nombre = basename($archivo);
            $estructura .= "{$indent}{$icono} {$nombre}\n";
        }
    }
    
    private function obtenerIconoArchivo($extension): string
    {
        return match($extension) {
            'php' => '🐘',
            'vue' => '💚',
            'js' => '💛',
            'blade.php' => '🌿',
            'css' => '🎨',
            'md' => '📝',
            'json' => '📋',
            'sql' => '🗄️',
            default => '📄'
        };
    }
    
    private function obtenerInfoBaseDatos(): string
    {
        try {
            $database = DB::connection()->getDatabaseName();
            $tables = DB::select('SHOW TABLES');
            $totalTablas = count($tables);
            
            return "**Base de datos:** {$database}
**Total de tablas:** {$totalTablas}";
            
        } catch (\Exception $e) {
            return "❌ Error al conectar con la base de datos: " . $e->getMessage();
        }
    }
    
    private function obtenerTablas(): string
    {
        try {
            $tablas = '';
            $tableNames = Schema::getAllTables();
            
            foreach ($tableNames as $table) {
                $tableName = array_values((array) $table)[0];
                $columns = Schema::getColumnListing($tableName);
                
                $registros = DB::table($tableName)->count();
                
                $tablas .= "\n### `{$tableName}`\n";
                $tablas .= "**Registros:** {$registros}\n\n";
                $tablas .= "**Columnas:**\n";
                
                foreach ($columns as $column) {
                    $tablas .= "- {$column}\n";
                }
                $tablas .= "\n";
            }
            
            return $tablas;
            
        } catch (\Exception $e) {
            return "❌ Error al obtener información de tablas: " . $e->getMessage();
        }
    }
    
    private function obtenerModelos(): string
    {
        $modelosPath = app_path('Models');
        $modelos = '';
        
        if (File::exists($modelosPath)) {
            $archivos = File::files($modelosPath);
            
            foreach ($archivos as $archivo) {
                $nombre = basename($archivo, '.php');
                $modelos .= "- **{$nombre}** (`{$archivo}`)\n";
            }
        }
        
        return $modelos ?: "❌ No se encontraron modelos.";
    }
    
    private function obtenerControladores(): string
    {
        $controladoresPath = app_path('Http/Controllers');
        $controladores = '';
        
        if (File::exists($controladoresPath)) {
            $archivos = File::files($controladoresPath);
            
            foreach ($archivos as $archivo) {
                $nombre = basename($archivo, '.php');
                $controladores .= "- **{$nombre}** (`{$archivo}`)\n";
            }
        }
        
        return $controladores ?: "❌ No se encontraron controladores.";
    }
    
    private function obtenerComponentesVue(): string
    {
        $vueComponents = '';
        $vueFiles = File::allFiles(resource_path('js/components'));
        
        foreach ($vueFiles as $file) {
            if ($file->getExtension() === 'vue') {
                $nombre = basename($file, '.vue');
                $vueComponents .= "- **{$nombre}** (`{$file}`)\n";
            }
        }
        
        return $vueComponents ?: "❌ No se encontraron componentes Vue.";
    }
    
    private function obtenerRutas(): string
    {
        try {
            $rutas = '';
            $routes = \Route::getRoutes();
            
            foreach ($routes as $route) {
                $methods = implode('|', $route->methods());
                $uri = $route->uri();
                $action = $route->getActionName();
                
                $rutas .= "- **{$methods}** `{$uri}` → {$action}\n";
            }
            
            return $rutas;
            
        } catch (\Exception $e) {
            return "❌ Error al obtener rutas: " . $e->getMessage();
        }
    }
    
    private function analizarErrores(): string
    {
        $errores = [];
        
        // Verificar migraciones pendientes
        try {
            $pending = DB::select("SELECT migration FROM migrations");
            if (count($pending) === 0) {
                $errores[] = "⚠️ No hay migraciones registradas.";
            }
        } catch (\Exception $e) {
            $errores[] = "❌ Error al verificar migraciones: " . $e->getMessage();
        }
        
        // Verificar archivos .env
        if (!File::exists(base_path('.env'))) {
            $errores[] = "❌ Archivo .env no encontrado.";
        }
        
        // Verificar permisos de storage
        if (!is_writable(storage_path())) {
            $errores[] = "⚠️ Directorio storage no tiene permisos de escritura.";
        }
        
        return implode("\n", $errores) ?: "✅ No se detectaron errores críticos.";
    }
    
    private function obtenerMetricas(): string
    {
        $phpFiles = count(File::allFiles(app_path()));
        $vueFiles = count(File::allFiles(resource_path('js/components')));
        $bladeFiles = count(File::allFiles(resource_path('views')));
        
        return "- **Archivos PHP:** {$phpFiles}
- **Componentes Vue:** {$vueFiles}  
- **Vistas Blade:** {$bladeFiles}
- **Generado:** " . now()->format('d/m/Y H:i:s');
    }
}