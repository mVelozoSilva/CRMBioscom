<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\SeguimientoController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\TriajeController;

/*
|--------------------------------------------------------------------------
| Web Routes - Rutas para vistas Blade (LIMPIADO)
|--------------------------------------------------------------------------
*/

// =============================================================================
// RUTAS PRINCIPALES
// =============================================================================

// --- Dashboard ---
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// --- Módulos CRUD Principales ---
Route::resource('clientes', ClienteController::class);
Route::resource('productos', ProductoController::class);
Route::resource('cotizaciones', CotizacionController::class);

// =============================================================================
// MÓDULO DE SEGUIMIENTO (CRÍTICO)
// =============================================================================

Route::prefix('seguimiento')->name('seguimiento.')->group(function () {
    Route::get('/', [SeguimientoController::class, 'index'])->name('index');
    Route::post('/', [SeguimientoController::class, 'store'])->name('store');
    Route::put('/{seguimiento}', [SeguimientoController::class, 'update'])->name('update');
    Route::delete('/{seguimiento}', [SeguimientoController::class, 'destroy'])->name('destroy');
    Route::post('/update-masivo', [SeguimientoController::class, 'updateMasivo'])->name('update-masivo');
    Route::post('/importar', [SeguimientoController::class, 'importar'])->name('importar');
});

// =============================================================================
// APIS PARA AUTOCOMPLETADO (Usadas por Vue.js)
// =============================================================================

Route::prefix('api')->name('api.')->group(function () {
    // Búsquedas para autocompletado
    Route::get('/buscar-clientes', [ClienteController::class, 'buscarClientes'])->name('buscar-clientes');
    Route::get('/buscar-productos', [ProductoController::class, 'buscarProductos'])->name('buscar-productos');
    
    // Datos específicos para componentes Vue
    Route::get('/cliente/{cliente}', [ClienteController::class, 'show'])->name('cliente.show');
    Route::get('/producto/{producto}', [ProductoController::class, 'show'])->name('producto.show');
    
    // APIs del módulo de seguimiento
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/seguimiento/data', [SeguimientoController::class, 'getSeguimientos'])->name('seguimiento.data');
    Route::post('/seguimiento/update-masivo', [SeguimientoController::class, 'updateMasivo'])->name('seguimiento.update-masivo');
    Route::get('/seguimiento/buscar-clientes', [SeguimientoController::class, 'buscarClientes'])->name('seguimiento.buscar-clientes');
    Route::get('/seguimiento/vendedores', [SeguimientoController::class, 'getVendedores'])->name('seguimiento.vendedores');
    
    // APIs del triaje
    Route::get('/triaje/seguimientos', [TriajeController::class, 'getSeguimientosClasificados'])->name('triaje.seguimientos');
    Route::get('/triaje/vendedores', [TriajeController::class, 'getVendedoresDisponibles'])->name('triaje.vendedores');
    
    // APIs de agenda
    Route::get('/agenda/tareas', [AgendaController::class, 'obtenerTareas'])->name('agenda.tareas');
});

// =============================================================================
// SISTEMA DE TRIAJE INTELIGENTE
// =============================================================================

Route::prefix('triaje')->name('triaje.')->group(function () {
    Route::get('/', [TriajeController::class, 'index'])->name('index');
    Route::post('/clasificar', [TriajeController::class, 'clasificarSeguimientos'])->name('clasificar');
    Route::post('/accion-masiva', [TriajeController::class, 'procesarAccionMasiva'])->name('accion-masiva');
    Route::post('/procesar-masivo', [TriajeController::class, 'procesarMasivo'])->name('procesar');
    Route::get('/analizar/{id}', [TriajeController::class, 'analizarSeguimiento'])->name('analizar');
});

// =============================================================================
// AGENDA ELECTRÓNICA MULTIFUNCIONAL
// =============================================================================

Route::middleware(['auth'])->prefix('agenda')->name('agenda.')->group(function () {
    // Vistas principales
    Route::get('/', [AgendaController::class, 'miDia'])->name('index');
    Route::get('/mi-dia', [AgendaController::class, 'miDia'])->name('mi-dia');
    Route::get('/mi-semana', [AgendaController::class, 'miSemana'])->name('mi-semana');
    
    // Gestión de tareas
    Route::post('/tareas', [AgendaController::class, 'crear'])->name('tareas.crear');
    Route::put('/tareas/{tarea}', [AgendaController::class, 'actualizar'])->name('tareas.actualizar');
    Route::post('/tareas/{tarea}/completar', [AgendaController::class, 'completarTarea'])->name('tareas.completar');
    Route::post('/tareas/{tarea}/posponer', [AgendaController::class, 'posponerTarea'])->name('tareas.posponer');
    
    // 🌟 DISTRIBUCIÓN AUTOMÁTICA (funcionalidad estrella)
    Route::post('/distribuir-seguimientos', [AgendaController::class, 'distribuirSeguimientosVencidos'])->name('distribuir');
});

// =============================================================================
// DOCUMENTACIÓN Y UTILIDADES
// =============================================================================

Route::get('/docs', function () {
    $filePath = storage_path('app/crm-bioscom-documentation.md');
    
    if (File::exists($filePath)) {
        $content = File::get($filePath);
        return response($content)->header('Content-Type', 'text/plain');
    }
    
    return 'Documentación no encontrada. Ejecuta: php artisan doc:generate';
})->name('docs');

// =============================================================================
// AUTENTICACIÓN TEMPORAL (Para desarrollo)
// =============================================================================

Route::get('/login', function () {
    $usuarios = \App\Models\User::all();
    
    $html = '
    <div style="max-width: 600px; margin: 50px auto; padding: 30px; font-family: Arial, sans-serif; background: #f3f6fa; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="color: #6284b8; text-align: center; margin-bottom: 30px;">🔐 CRM Bioscom - Login Temporal</h2>
        <p style="text-align: center; color: #00334e; margin-bottom: 30px;">Selecciona un usuario para iniciar sesión:</p>
        <div style="margin: 20px 0;">';
    
    foreach ($usuarios as $user) {
        $html .= '
        <a href="/login-as/' . $user->id . '" style="
            display: block; 
            margin: 15px 0; 
            padding: 15px 20px; 
            background: linear-gradient(135deg, #6284b8, #5f87b8); 
            color: white; 
            text-decoration: none; 
            border-radius: 8px; 
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        " onmouseover="this.style.transform=\'translateY(-2px)\'; this.style.boxShadow=\'0 4px 15px rgba(0,0,0,0.2)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 2px 5px rgba(0,0,0,0.1)\';">
            <strong>👤 ' . $user->name . '</strong><br>
            <small style="opacity: 0.9;">📧 ' . $user->email . '</small>
        </a>';
    }
    
    $html .= '
        </div>
        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <small style="color: #6c757d;">🛠️ Sistema de autenticación temporal para desarrollo</small>
        </div>
    </div>';
    
    return $html;
})->name('login');

Route::get('/login-as/{user}', function ($userId) {
    $user = \App\Models\User::findOrFail($userId);
    Auth::login($user);
    
    return redirect('/')->with('success', '✅ Autenticado como ' . $user->name);
})->name('login-as');

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login')->with('success', '👋 Sesión cerrada correctamente');
})->name('logout');

// =============================================================================
// RUTAS FUTURAS (Comentadas para referencia)
// =============================================================================

/*
// 📊 MÓDULO DE REPORTES (Fase 4)
Route::prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/', [ReportesController::class, 'index'])->name('index');
    Route::get('/ventas', [ReportesController::class, 'ventas'])->name('ventas');
    Route::get('/seguimiento', [ReportesController::class, 'seguimiento'])->name('seguimiento');
});

// 🔧 SERVICIO TÉCNICO (Fase 5)
Route::prefix('servicio-tecnico')->name('st.')->group(function () {
    Route::get('/', [ServicioTecnicoController::class, 'index'])->name('index');
    Route::resource('solicitudes', SolicitudSTController::class);
    Route::resource('mantenciones', MantencionController::class);
});

// 💰 COBRANZAS (Fase 5)
Route::prefix('cobranzas')->name('cobranzas.')->group(function () {
    Route::get('/', [CobranzasController::class, 'index'])->name('index');
    Route::post('/actualizar-masivo', [CobranzasController::class, 'updateMasivo'])->name('update-masivo');
});
*/