<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\User;

class GenerateProjectDocumentation extends Command
{
    protected $signature = 'doc:generate';
    protected $description = 'Genera documentación automática del proyecto CRM Bioscom';

    public function handle()
    {
        $this->info('🚀 Generando documentación del CRM Bioscom...');
        
        $doc = "# CRM Bioscom - Documentación Automática\n";
        $doc .= "Generado: " . now()->format('Y-m-d H:i:s') . "\n\n";
        
        // 1. Información de la base de datos
        $doc .= $this->getDatabaseInfo();
        
        // 2. Modelos y relaciones
        $doc .= $this->getModelsInfo();
        
        // 3. Controladores y rutas
        $doc .= $this->getControllersInfo();
        
        // 4. Componentes Vue
        $doc .= $this->getVueComponentsInfo();
        
        // 5. Usuarios actuales
        $doc .= $this->getUsersInfo();
        
        // 6. Rutas API
        $doc .= $this->getRoutesInfo();
        
        // Guardar archivo
        $filePath = storage_path('app/crm-bioscom-documentation.md');
        File::put($filePath, $doc);
        
        $this->info("✅ Documentación generada en: {$filePath}");
        $this->info("📄 Para verla: cat storage/app/crm-bioscom-documentation.md");
        
        return 0;
    }
    
    private function getDatabaseInfo()
    {
        $doc = "## 📊 Base de Datos\n\n";
        
        try {
            $dbName = config('database.connections.mysql.database');
            $doc .= "**Base de datos:** {$dbName}\n\n";
            
            // Obtener todas las tablas
            $tables = DB::select('SHOW TABLES');
            
            $doc .= "### Tablas y Estructura:\n\n";
            foreach ($tables as $table) {
                $tableName = $table->{"Tables_in_{$dbName}"};
                $doc .= "#### `{$tableName}`\n";
                
                // Obtener columnas de cada tabla
                $columns = DB::select("DESCRIBE {$tableName}");
                foreach ($columns as $column) {
                    $null = $column->Null === 'YES' ? 'NULL' : 'NOT NULL';
                    $default = $column->Default ? "DEFAULT: {$column->Default}" : '';
                    $extra = $column->Extra ? "({$column->Extra})" : '';
                    $doc .= "- **{$column->Field}**: {$column->Type} {$null} {$default} {$extra}\n";
                }
                
                // Contar registros
                try {
                    $count = DB::table($tableName)->count();
                    $doc .= "- **Registros:** {$count}\n";
                } catch (\Exception $e) {
                    $doc .= "- **Registros:** Error al contar\n";
                }
                
                $doc .= "\n";
            }
            
        } catch (\Exception $e) {
            $doc .= "Error al obtener información de la base de datos: " . $e->getMessage() . "\n\n";
        }
        
        return $doc;
    }
    
    private function getModelsInfo()
    {
        $doc = "## 🏗️ Modelos Laravel\n\n";
        
        $modelsPath = app_path('Models');
        if (!File::exists($modelsPath)) {
            $doc .= "No se encontró el directorio de modelos.\n\n";
            return $doc;
        }
        
        $models = File::files($modelsPath);
        
        foreach ($models as $model) {
            $modelName = pathinfo($model->getFilename(), PATHINFO_FILENAME);
            $doc .= "### {$modelName}\n";
            $doc .= "- **Archivo:** `app/Models/{$modelName}.php`\n";
            
            // Intentar obtener información del modelo
            $modelClass = "App\\Models\\{$modelName}";
            if (class_exists($modelClass)) {
                try {
                    $instance = new $modelClass;
                    
                    // Fillable
                    $fillable = $instance->getFillable();
                    if (!empty($fillable)) {
                        $doc .= "- **Fillable:** " . implode(', ', $fillable) . "\n";
                    }
                    
                    // Tabla
                    $table = $instance->getTable();
                    $doc .= "- **Tabla:** `{$table}`\n";
                    
                } catch (\Exception $e) {
                    $doc .= "- **Error:** No se pudo instanciar el modelo\n";
                }
            }
            $doc .= "\n";
        }
        
        return $doc;
    }
    
    private function getControllersInfo()
    {
        $doc = "## 🎮 Controladores\n\n";
        
        $controllersPath = app_path('Http/Controllers');
        $controllers = File::files($controllersPath);
        
        foreach ($controllers as $controller) {
            $controllerName = pathinfo($controller->getFilename(), PATHINFO_FILENAME);
            $doc .= "### {$controllerName}\n";
            $doc .= "- **Archivo:** `app/Http/Controllers/{$controllerName}.php`\n";
            
            // Obtener métodos del controlador
            $content = File::get($controller->getPathname());
            preg_match_all('/public function (\w+)\([^{]*\{/', $content, $matches);
            if (!empty($matches[1])) {
                $doc .= "- **Métodos:** " . implode(', ', array_unique($matches[1])) . "\n";
            }
            $doc .= "\n";
        }
        
        return $doc;
    }
    
    private function getVueComponentsInfo()
    {
        $doc = "## ⚡ Componentes Vue\n\n";
        
        $componentsPath = resource_path('js/components');
        if (!File::exists($componentsPath)) {
            $doc .= "No se encontró el directorio de componentes Vue.\n\n";
            return $doc;
        }
        
        $components = File::files($componentsPath);
        
        foreach ($components as $component) {
            $componentName = pathinfo($component->getFilename(), PATHINFO_FILENAME);
            $doc .= "### {$componentName}\n";
            $doc .= "- **Archivo:** `resources/js/components/{$component->getFilename()}`\n";
            
            // Obtener información del componente
            $content = File::get($component->getPathname());
            
            // Props
            if (preg_match('/props:\s*{([^}]+)}/s', $content, $matches)) {
                $props = $matches[1];
                // Extraer nombres de props
                preg_match_all('/(\w+):\s*{/', $props, $propMatches);
                if (!empty($propMatches[1])) {
                    $doc .= "- **Props:** " . implode(', ', $propMatches[1]) . "\n";
                }
            }
            
            // Métodos principales
            preg_match_all('/(\w+)\([^)]*\)\s*{/', $content, $methodMatches);
            if (!empty($methodMatches[1])) {
                $methods = array_slice(array_unique($methodMatches[1]), 0, 10); // Primeros 10
                $doc .= "- **Métodos:** " . implode(', ', $methods) . "\n";
            }
            
            $doc .= "\n";
        }
        
        return $doc;
    }
    
    private function getUsersInfo()
    {
        $doc = "## 👥 Usuarios/Vendedores Actuales\n\n";
        
        try {
            $users = User::select('id', 'name', 'email', 'created_at')->get();
            
            $doc .= "**Total de usuarios:** " . $users->count() . "\n\n";
            
            foreach ($users as $user) {
                $created = $user->created_at ? $user->created_at->format('Y-m-d') : 'N/A';
                $doc .= "- **ID {$user->id}:** {$user->name}\n";
                $doc .= "  - Email: {$user->email}\n";
                $doc .= "  - Creado: {$created}\n\n";
            }
            
        } catch (\Exception $e) {
            $doc .= "Error al obtener usuarios: " . $e->getMessage() . "\n\n";
        }
        
        return $doc;
    }
    
    private function getRoutesInfo()
    {
        $doc = "## 🛣️ Rutas Principales\n\n";
        
        // Leer rutas web
        $webRoutes = base_path('routes/web.php');
        if (File::exists($webRoutes)) {
            $doc .= "### Rutas Web (routes/web.php)\n";
            $content = File::get($webRoutes);
            preg_match_all('/Route::(get|post|put|delete|resource)\([\'"]([^\'"]+)/', $content, $matches);
            
            if (!empty($matches[2])) {
                foreach ($matches[2] as $i => $route) {
                    $method = strtoupper($matches[1][$i]);
                    $doc .= "- **{$method}** `/{$route}`\n";
                }
            }
            $doc .= "\n";
        }
        
        // Leer rutas API
        $apiRoutes = base_path('routes/api.php');
        if (File::exists($apiRoutes)) {
            $doc .= "### Rutas API (routes/api.php)\n";
            $content = File::get($apiRoutes);
            preg_match_all('/Route::(get|post|put|delete)\([\'"]([^\'"]+)/', $content, $matches);
            
            if (!empty($matches[2])) {
                foreach ($matches[2] as $i => $route) {
                    $method = strtoupper($matches[1][$i]);
                    $doc .= "- **{$method}** `/api/{$route}`\n";
                }
            }
            $doc .= "\n";
        }
        
        return $doc;
    }
}