<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\SeguimientoController;
use App\Http\Controllers\TriajeController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\CobranzaController;
use App\Http\Controllers\CampaniaController;
use App\Http\Controllers\ServicioTecnicoController;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\FormularioController;
use App\Http\Controllers\ConfiguracionSupervisorController;

/*
|--------------------------------------------------------------------------
| API Routes - Solo APIs que devuelven JSON
|--------------------------------------------------------------------------
| Todas estas rutas automáticamente tienen el prefijo /api/
| Ejemplo: Route::get('/test') se convierte en /api/test
*/

// =============================================================================
// APIs DE BÚSQUEDA Y AUTOCOMPLETADO
// =============================================================================

Route::get('/buscar-clientes', [ClienteController::class, 'buscarClientes']);
Route::get('/buscar-productos', [ProductoController::class, 'buscarProductos']);
Route::get('/buscar-contactos', [ContactoController::class, 'buscar']);

// =============================================================================
// APIs DE CLIENTES 
// =============================================================================

Route::prefix('clientes')->group(function () {
    Route::get('/', [ClienteController::class, 'index']);
    Route::post('/', [ClienteController::class, 'store']);
    Route::get('/{cliente}', [ClienteController::class, 'show']);
    Route::put('/{cliente}', [ClienteController::class, 'update']);
    Route::delete('/{cliente}', [ClienteController::class, 'destroy']);
    
    // Verificaciones específicas
    Route::get('/verificar-rut', [ClienteController::class, 'verificarRut']);
    Route::get('/verificar-email', [ClienteController::class, 'verificarEmail']);
});

// =============================================================================
// APIs DE PRODUCTOS
// =============================================================================

Route::prefix('productos')->group(function () {
    Route::get('/', [ProductoController::class, 'index']);
    Route::post('/', [ProductoController::class, 'store']);
    Route::get('/{producto}', [ProductoController::class, 'show']);
    Route::put('/{producto}', [ProductoController::class, 'update']);
    Route::delete('/{producto}', [ProductoController::class, 'destroy']);
});

// =============================================================================
// APIs DE COTIZACIONES
// =============================================================================

Route::prefix('cotizaciones')->group(function () {
    Route::get('/', [CotizacionController::class, 'index']);
    Route::post('/', [CotizacionController::class, 'store']);
    Route::get('/{cotizacion}', [CotizacionController::class, 'show']);
    Route::put('/{cotizacion}', [CotizacionController::class, 'update']);
    Route::delete('/{cotizacion}', [CotizacionController::class, 'destroy']);
    
    // Actualización masiva
    Route::post('/update-masivo', [CotizacionController::class, 'updateMasivo']);
});

// =============================================================================
// APIs DE SEGUIMIENTO (CRÍTICO) 
// =============================================================================

Route::prefix('seguimiento')->group(function () {
    // API principal para la tabla tipo Excel
    Route::get('/data', [SeguimientoController::class, 'getSeguimientos']);
    
    // Actualización masiva
    Route::post('/update-masivo', [SeguimientoController::class, 'updateMasivo']);
    
    // Búsquedas específicas
    Route::get('/buscar-clientes', [SeguimientoController::class, 'buscarClientes']);
    Route::get('/vendedores', [SeguimientoController::class, 'getVendedores']);
     // 🆕 RUTAS CRUD:
    Route::post('/', [SeguimientoController::class, 'store']);
    Route::put('/{seguimiento}', [SeguimientoController::class, 'update']);
    Route::delete('/{seguimiento}', [SeguimientoController::class, 'destroy']);
});

// =============================================================================
// APIs DE CONTACTOS
// =============================================================================

Route::prefix('contactos')->group(function () {
    Route::get('/', [ContactoController::class, 'index']);
    Route::post('/', [ContactoController::class, 'store']);
    Route::get('/{contacto}', [ContactoController::class, 'show']);
    Route::put('/{contacto}', [ContactoController::class, 'update']);
    Route::delete('/{contacto}', [ContactoController::class, 'destroy']);
    Route::get('/cliente/{cliente}', [ContactoController::class, 'porCliente']);
});

// =============================================================================
// APIs DE TRIAJE INTELIGENTE
// =============================================================================

Route::prefix('triaje')->group(function () {
    Route::get('/seguimientos', [TriajeController::class, 'getSeguimientosClasificados']);
    Route::get('/vendedores', [TriajeController::class, 'getVendedoresDisponibles']);
    Route::post('/procesar-masivo', [TriajeController::class, 'procesarMasivo']);
    Route::get('/analizar/{id}', [TriajeController::class, 'analizarSeguimiento']);
});

// ============================================================================
// API ROUTES PARA MÓDULO DE TAREAS (AGENDA ELECTRÓNICA)
// ============================================================================

Route::prefix('agenda')->group(function () {
    // CRUD completo de tareas
    Route::apiResource('tareas', AgendaController::class, [
        'except' => ['index'] // usamos obtenerTareas en su lugar
    ]);
    
    // Obtener tareas con filtros avanzados y paginación
    Route::get('/tareas', [AgendaController::class, 'obtenerTareas'])->name('api.agenda.tareas.index');
    
    // Acciones específicas de tareas
    Route::post('/tareas/{tarea}/completar', [AgendaController::class, 'completarTarea'])->name('api.agenda.tareas.completar');
    Route::post('/tareas/{tarea}/posponer', [AgendaController::class, 'posponerTarea'])->name('api.agenda.tareas.posponer');
    
    // Distribución automática de seguimientos vencidos
    Route::post('/distribuir-seguimientos', [AgendaController::class, 'distribuirSeguimientosVencidos'])->name('api.agenda.distribuir-seguimientos');
    
    // Datos para formularios (usuarios, clientes, tipos, etc.)
    Route::get('/datos-formulario', [AgendaController::class, 'obtenerDatosFormulario'])->name('api.agenda.datos-formulario');
    
    // Estadísticas y métricas para dashboard
    Route::get('/estadisticas', [AgendaController::class, 'obtenerEstadisticas'])->name('api.agenda.estadisticas');
    // Ruta para obtener tareas de la semana
    Route::get('/tareas-semana', [AgendaController::class, 'obtenerTareasSemana']);
    // Análisis inteligente de carga de trabajo
    Route::get('/analizar-carga', [AgendaController::class, 'analizarCargaTrabajo']);
});

// ==========================================
// 💰 MÓDULO DE COBRANZAS - API ROUTES
// ==========================================

// Rutas especializadas para cobranzas (PRIMERO)
Route::prefix('cobranzas')->group(function () {
    
    // Dashboard y estadísticas
    Route::get('/dashboard-data', [\App\Http\Controllers\CobranzaController::class, 'dashboard'])
         ->name('cobranzas.dashboard');
    
    // Gestión de interacciones
    Route::post('/{cobranza}/interacciones', [\App\Http\Controllers\CobranzaController::class, 'registrarInteraccion'])
         ->name('cobranzas.registrar-interaccion');
    
    // Marcar como pagada
    Route::post('/{cobranza}/marcar-pagada', [\App\Http\Controllers\CobranzaController::class, 'marcarComoPagada'])
         ->name('cobranzas.marcar-pagada');
    
    // Actualización masiva
    Route::post('/update-masivo', [\App\Http\Controllers\CobranzaController::class, 'updateMasivo'])
         ->name('cobranzas.update-masivo');
    Route::put('/cobranzas/update-masivo', [CobranzaController::class, 'updateMasivo']);
    
});

// Rutas API para el CRUD principal de cobranzas (DESPUÉS)
Route::apiResource('cobranzas', \App\Http\Controllers\CobranzaController::class);

// Rutas adicionales para reportes y análisis (para implementación futura)
Route::prefix('cobranzas/reportes')->group(function () {
    
    // Reporte de efectividad de gestión
    Route::get('/efectividad', function() {
        return response()->json([
            'success' => true,
            'message' => 'Endpoint de reportes - A implementar en futuras sesiones'
        ]);
    })->name('cobranzas.reportes.efectividad');
    
    // Reporte de antigüedad de saldos
    Route::get('/antiguedad-saldos', function() {
        return response()->json([
            'success' => true,
            'message' => 'Endpoint de reportes - A implementar en futuras sesiones'
        ]);
    })->name('cobranzas.reportes.antiguedad');
    
    // Exportar datos a Excel
    Route::get('/exportar', function() {
        return response()->json([
            'success' => true,
            'message' => 'Endpoint de exportación - A implementar en futuras sesiones'
        ]);
    })->name('cobranzas.reportes.exportar');
    
});

Route::get('campanas', [CampaniaController::class, 'index']);
Route::get('campanas/crear', [CampaniaController::class, 'create']);    
Route::post('campanas', [CampaniaController::class, 'store']);

route::get('servicio-tecnico', [ServicioTecnicoController::class, 'index']);
Route::get('servicio-tecnico/crear', [ServicioTecnicoController::class, 'create']);
Route::post('servicio-tecnico', [ServicioTecnicoController::class, 'store   ']);    

route::get('archivos', [ArchivoController::class, 'index']);
Route::get('archivos/crear', [ArchivoController::class, 'create']); 
Route::post('archivos', [ArchivoController::class, 'store']);

route::get('notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
Route::post('notificaciones/leer', [NotificacionController::class, 'marcar  ComoLeidas'])->name('notificaciones.leer');     
route::post('notificaciones/limpiar', [NotificacionController::class, 'limpiarNotificaciones'])->name('notificaciones.limpiar');

route::get('formularios', [FormularioController::class, 'index']);
route::get('formularios/crear', [FormularioController::class, 'create']);       
route::post('formularios', [FormularioController::class, 'store']);

route::get('configuracion-supervisor', [ConfiguracionSupervisorController::class, 'index'])->name('configuracion.supervisor.index');
route::get('configuracion-supervisor/editar', [ConfiguracionSupervisorController::class, 'edit'])->name('configuracion.supervisor.edit');
route::put('configuracion-supervisor', [ConfiguracionSupervisorController::class, 'update'])->name('configuracion.supervisor.update');  

route::get('notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
route::post('notificaciones/leer', [NotificacionController::class, 'marcarComoLeidas'])->name('notificaciones.leer');
route::post('notificaciones/limpiar', [NotificacionController::class, 'limpiarNotificaciones'])->name('notificaciones.limpiar');    