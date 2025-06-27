<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\SeguimientoController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\TriajeController;
use App\Http\Controllers\CobranzaController;
use App\Http\Controllers\ServicioTecnicoController;
use App\Http\Controllers\CampaniaController;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\FormularioController;
use App\Http\Controllers\ConfiguracionSupervisorController;

/*
|--------------------------------------------------------------------------
| Web Routes - Solo vistas Blade y acciones web
|--------------------------------------------------------------------------
| Estas rutas devuelven páginas HTML (vistas Blade)
*/

// =============================================================================
// DASHBOARD PRINCIPAL
// =============================================================================

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// =============================================================================
// MÓDULOS CRUD PRINCIPALES (Vistas Blade)
// =============================================================================

// --- Clientes ---
Route::resource('clientes', ClienteController::class);

// --- Productos ---
Route::resource('productos', ProductoController::class);

// --- Cotizaciones ---
Route::resource('cotizaciones', CotizacionController::class);

// --- Contactos ---
Route::resource('contactos', ContactoController::class);

// =============================================================================
// MÓDULO DE SEGUIMIENTO (CRÍTICO) - Solo vistas y acciones web
// =============================================================================

Route::prefix('seguimiento')->name('seguimiento.')->group(function () {
    // Vista principal (devuelve Blade)
    Route::get('/', [SeguimientoController::class, 'index'])->name('index');
    
    // Acciones CRUD individuales (para formularios web)
    Route::post('/', [SeguimientoController::class, 'store'])->name('store');
    Route::put('/{seguimiento}', [SeguimientoController::class, 'update'])->name('update');
    Route::delete('/{seguimiento}', [SeguimientoController::class, 'destroy'])->name('destroy');
    
    // Acciones masivas (para formularios web)
    Route::post('/update-masivo', [SeguimientoController::class, 'updateMasivo'])->name('update-masivo');
    Route::post('/importar', [SeguimientoController::class, 'importar'])->name('importar');
});

// =============================================================================
// SISTEMA DE TRIAJE INTELIGENTE (Vistas Blade)
// =============================================================================

Route::prefix('triaje')->name('triaje.')->group(function () {
    Route::get('/', [TriajeController::class, 'index'])->name('index');
    Route::post('/clasificar', [TriajeController::class, 'clasificarSeguimientos'])->name('clasificar');
    Route::post('/accion-masiva', [TriajeController::class, 'procesarAccionMasiva'])->name('accion-masiva');
    Route::post('/procesar-masivo', [TriajeController::class, 'procesarMasivo'])->name('procesar');
    Route::get('/analizar/{id}', [TriajeController::class, 'analizarSeguimiento'])->name('analizar');
});

// =============================================================================
// AGENDA ELECTRÓNICA MULTIFUNCIONAL (Vistas Blade)
// =============================================================================

Route::middleware(['auth'])->prefix('agenda')->name('agenda.')->group(function () {
    // Vistas principales
    Route::get('/', [AgendaController::class, 'miDia'])->name('index');
    Route::get('/mi-dia', [AgendaController::class, 'miDia'])->name('mi-dia');
    Route::get('/mi-semana', [AgendaController::class, 'miSemana'])->name('mi-semana');
    
    // Gestión de tareas (formularios web)
    Route::post('/tareas', [AgendaController::class, 'crear'])->name('tareas.crear');
    Route::put('/tareas/{tarea}', [AgendaController::class, 'actualizar'])->name('tareas.actualizar');
    Route::post('/tareas/{tarea}/completar', [AgendaController::class, 'completarTarea'])->name('tareas.completar');
    Route::post('/tareas/{tarea}/posponer', [AgendaController::class, 'posponerTarea'])->name('tareas.posponer');
    Route::get('/agenda/mi-semana', [AgendaController::class, 'miSemana'])->name('agenda.mi-semana');
    
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

// ============================================================================
// RUTAS PARA MÓDULO DE TAREAS (AGENDA ELECTRÓNICA)
// ============================================================================

Route::prefix('agenda')->name('agenda.')->group(function () {
    // Vista principal de la agenda
    Route::get('/', [AgendaController::class, 'index'])->name('index');
    
    // Vista "Mi Día" - Tareas del día actual
    Route::get('/mi-dia', [AgendaController::class, 'miDia'])->name('mi-dia');
    
    // Vista "Mi Semana" - Planificación semanal
    Route::get('/mi-semana', [AgendaController::class, 'miSemana'])->name('mi-semana');
    
    // Acciones rápidas desde las vistas
    Route::post('/tareas', [AgendaController::class, 'store'])->name('tareas.store');
    Route::put('/tareas/{tarea}', [AgendaController::class, 'update'])->name('tareas.update');
    Route::post('/tareas/{tarea}/completar', [AgendaController::class, 'completarTarea'])->name('tareas.completar');
    Route::post('/tareas/{tarea}/posponer', [AgendaController::class, 'posponerTarea'])->name('tareas.posponer');
    
    // Distribución automática de seguimientos (para Jefes de Ventas)
    Route::post('/distribuir-seguimientos', [AgendaController::class, 'distribuirSeguimientosVencidos'])->name('distribuir-seguimientos');
});

// ==========================================
// 💰 MÓDULO DE COBRANZAS
// ==========================================


// Ruta principal del módulo de cobranzas
Route::get('/cobranzas', function () {
    return view('cobranzas.index');
})->name('cobranzas.index');

// Rutas adicionales para vistas específicas (si se necesitan en el futuro)
Route::prefix('cobranzas')->name('cobranzas.')->group(function () {
    // Vista de dashboard de cobranzas
    Route::get('/dashboard', function () {
        return view('cobranzas.dashboard');
    })->name('dashboard');
    
    // Vista de reportes de cobranzas
    Route::get('/reportes', function () {
        return view('cobranzas.reportes');
    })->name('reportes');
    
    // Vista de configuración de cobranzas (para roles administrativos)
    Route::get('/configuracion', function () {
        return view('cobranzas.configuracion');
    })->name('configuracion');
});


//Rutas Accesibilidad //
Route::get('/accesibilidad', function () {
    return view('accesibilidad.index');
});


Route::get('/servicio-tecnico', [ServicioTecnicoController::class, 'index']);
Route::get('/servicio-tecnico/crear', [ServicioTecnicoController::class, 'create']);
Route::post('/servicio-tecnico', [ServicioTecnicoController::class, 'store']);

Route::get('/campanias', [CampaniaController::class, 'index']);
Route::get('/campanias/crear', [CampaniaController::class, 'create']);
Route::post('/campanias', [CampaniaController::class, 'store']);

Route::get('/archivos', [ArchivoController::class, 'index']);
Route::get('/archivos/crear', [ArchivoController::class, 'create']);
Route::post('/archivos', [ArchivoController::class, 'store']);

Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
Route::post('/notificaciones/leer', [NotificacionController::class, 'marcarComoLeidas'])->name('notificaciones.leer');
Route::post('/notificaciones/limpiar', [NotificacionController::class, 'limpiarNotificaciones'])->name('notificaciones.limpiar');

Route::get('/formularios', [FormularioController::class, 'index']);
Route::get('/formularios/crear', [FormularioController::class, 'create']);
Route::post('/formularios', [FormularioController::class, 'store']);

Route::get('/configuracion', [ConfiguracionSupervisorController::class, 'index']);
Route::get('/configuracion/editar', [ConfiguracionSupervisorController::class, 'edit']);
Route::post('/configuracion', [ConfiguracionSupervisorController::class, 'update']);
Route::get('/configuracion/crear', [ConfiguracionSupervisorController::class, 'create'])->name('configuracion.create');

Route::get('/notificaciones', [NotificacionController::class, 'index']);
Route::get('/notificaciones/crear', [NotificacionController::class, 'create']);
Route::post('/notificaciones', [NotificacionController::class, 'store']);

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
*/