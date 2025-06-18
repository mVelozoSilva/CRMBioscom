#!/bin/bash

# Script para exportar el proyecto CRM Bioscom a formato texto
# Uso: ejecutar desde la raíz del proyecto C:\laragon\www\crm-bioscom

echo "🚀 Iniciando exportación del proyecto CRM Bioscom..."

# Crear carpeta de exportación con timestamp
EXPORT_DIR="crm-bioscom-export-$(date +%Y%m%d_%H%M%S)"
mkdir -p "$EXPORT_DIR"

echo "📁 Creando carpeta: $EXPORT_DIR"

# Función para copiar archivos PHP, JS, Vue, Blade
copy_code_files() {
    local source_dir=$1
    local dest_dir=$2
    local file_pattern=$3
    
    if [ -d "$source_dir" ]; then
        echo "📂 Procesando: $source_dir"
        find "$source_dir" -name "$file_pattern" -type f | while read file; do
            # Crear estructura de directorios
            rel_path=$(realpath --relative-to="." "$file")
            dest_file="$dest_dir/$rel_path.txt"
            dest_folder=$(dirname "$dest_file")
            
            mkdir -p "$dest_folder"
            
            # Copiar archivo con extensión .txt
            cp "$file" "$dest_file"
            echo "✅ Copiado: $rel_path -> $rel_path.txt"
        done
    fi
}

# Copiar archivos de código principales
echo "🔥 Copiando archivos de código..."

# Controllers
copy_code_files "app/Http/Controllers" "$EXPORT_DIR" "*.php"

# Models
copy_code_files "app/Models" "$EXPORT_DIR" "*.php"

# Views (Blade templates)
copy_code_files "resources/views" "$EXPORT_DIR" "*.blade.php"

# Vue Components
copy_code_files "resources/js/components" "$EXPORT_DIR" "*.vue"

# JavaScript files
copy_code_files "resources/js" "$EXPORT_DIR" "*.js"

# CSS files
copy_code_files "resources/css" "$EXPORT_DIR" "*.css"

# Routes
copy_code_files "routes" "$EXPORT_DIR" "*.php"

# Migrations
copy_code_files "database/migrations" "$EXPORT_DIR" "*.php"

# Seeders
copy_code_files "database/seeders" "$EXPORT_DIR" "*.php"

# Config files
copy_code_files "config" "$EXPORT_DIR" "*.php"

# Copiar archivos de configuración importantes
echo "⚙️ Copiando archivos de configuración..."

# Composer.json
if [ -f "composer.json" ]; then
    cp "composer.json" "$EXPORT_DIR/composer.json.txt"
    echo "✅ Copiado: composer.json"
fi

# Package.json
if [ -f "package.json" ]; then
    cp "package.json" "$EXPORT_DIR/package.json.txt"
    echo "✅ Copiado: package.json"
fi

# .env.example (no copiar .env por seguridad)
if [ -f ".env.example" ]; then
    cp ".env.example" "$EXPORT_DIR/env.example.txt"
    echo "✅ Copiado: .env.example"
fi

# Artisan
if [ -f "artisan" ]; then
    cp "artisan" "$EXPORT_DIR/artisan.txt"
    echo "✅ Copiado: artisan"
fi

# Crear archivo de estructura del proyecto
echo "📋 Generando estructura del proyecto..."
tree -I 'node_modules|vendor|storage|bootstrap/cache|.git' > "$EXPORT_DIR/estructura_proyecto.txt" 2>/dev/null || \
find . -type d -name node_modules -prune -o -name vendor -prune -o -name .git -prune -o -type f -print | sort > "$EXPORT_DIR/estructura_proyecto.txt"

# Crear resumen del proyecto
cat > "$EXPORT_DIR/README_EXPORTACION.txt" << EOF
# 📁 EXPORTACIÓN CRM BIOSCOM
Generado: $(date)
Proyecto: CRM Bioscom Chile SpA

## 📋 CONTENIDO DE LA EXPORTACIÓN

### 🎮 Controladores (app/Http/Controllers/)
- DashboardController.php.txt
- ClienteController.php.txt
- ProductoController.php.txt
- CotizacionController.php.txt
- SeguimientoController.php.txt
- ContactoController.php.txt
- AgendaController.php.txt
- TriajeController.php.txt

### 🏗️ Modelos (app/Models/)
- User.php.txt
- Cliente.php.txt
- Producto.php.txt
- Cotizacion.php.txt
- Seguimiento.php.txt
- Contacto.php.txt
- Tarea.php.txt
- ColaSeguimiento.php.txt
- ConfiguracionSeguimiento.php.txt

### 🖼️ Vistas (resources/views/)
- layouts/app.blade.php.txt
- dashboard/index.blade.php.txt
- clientes/*.blade.php.txt
- productos/*.blade.php.txt
- cotizaciones/*.blade.php.txt
- seguimiento/*.blade.php.txt

### ⚡ Componentes Vue (resources/js/components/)
- ClienteForm.vue.txt
- CotizacionForm.vue.txt
- SeguimientoTable.vue.txt

### 🛣️ Rutas (routes/)
- web.php.txt
- api.php.txt

### 🗄️ Base de Datos (database/)
- migrations/*.php.txt
- seeders/*.php.txt

### ⚙️ Configuración
- composer.json.txt
- package.json.txt
- env.example.txt
- config/*.php.txt

## 📊 ESTADÍSTICAS
- Total de archivos exportados: $(find "$EXPORT_DIR" -name "*.txt" | wc -l)
- Tamaño total: $(du -sh "$EXPORT_DIR" | cut -f1)

## 🎯 USO
1. Comprimir toda la carpeta $EXPORT_DIR
2. Subir a Google Drive
3. Compartir con Claude AI para análisis y desarrollo

EOF

# Contar archivos y mostrar resumen
total_files=$(find "$EXPORT_DIR" -name "*.txt" | wc -l)
total_size=$(du -sh "$EXPORT_DIR" | cut -f1)

echo ""
echo "🎉 ¡EXPORTACIÓN COMPLETA!"
echo "📁 Carpeta: $EXPORT_DIR"
echo "📄 Archivos exportados: $total_files"
echo "💾 Tamaño total: $total_size"
echo ""
echo "📋 PRÓXIMOS PASOS:"
echo "1. Comprimir la carpeta: zip -r $EXPORT_DIR.zip $EXPORT_DIR"
echo "2. Subir $EXPORT_DIR.zip a Google Drive"
echo "3. Compartir la carpeta con Claude AI"
echo ""
echo "✨ ¡Listo para desarrollo colaborativo!"