@echo off
echo.
echo 🔧 REPARACIÓN SIMPLE CRM BIOSCOM
echo ===============================
echo.

:: Verificar proyecto
if not exist "artisan" (
    echo ❌ ERROR: No se encontró artisan
    pause
    exit /b 1
)

echo ✅ Proyecto encontrado
echo.

:: Limpiar cachés UNO POR UNO con timeout
echo 🧹 Limpiando cache...
timeout /t 1 /nobreak >nul
php artisan cache:clear
echo ✅ Cache limpiado

echo 🧹 Limpiando config...  
timeout /t 1 /nobreak >nul
php artisan config:clear
echo ✅ Config limpiado

echo 🧹 Limpiando rutas...
timeout /t 1 /nobreak >nul
php artisan route:clear
echo ✅ Rutas limpiadas

echo 🧹 Limpiando vistas...
timeout /t 1 /nobreak >nul
php artisan view:clear
echo ✅ Vistas limpiadas

echo.
echo 🔑 Verificando clave de aplicación...
findstr /c:"APP_KEY=" .env >nul
if errorlevel 1 (
    echo 🔑 Generando clave...
    php artisan key:generate
) else (
    echo ✅ Clave existe
)

echo.
echo 📦 Actualizando autoloader...
composer dump-autoload
echo ✅ Autoloader actualizado

echo.
echo 🗄️ Estado de la base de datos:
php artisan migrate:status

echo.
echo 🎉 ¡LISTO! Prueba acceder a:
echo http://localhost/crm-bioscom/public
echo.

pause