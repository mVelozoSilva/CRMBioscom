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

// =============================================================================
// APIs DE AGENDA
// =============================================================================

Route::prefix('agenda')->group(function () {
    Route::get('/tareas', [AgendaController::class, 'obtenerTareas']);
    Route::post('/tareas', [AgendaController::class, 'crear']);
    Route::put('/tareas/{tarea}', [AgendaController::class, 'actualizar']);
    Route::post('/tareas/{tarea}/completar', [AgendaController::class, 'completarTarea']);
    Route::post('/tareas/{tarea}/posponer', [AgendaController::class, 'posponerTarea']);
    Route::post('/distribuir-seguimientos', [AgendaController::class, 'distribuirSeguimientosVencidos']);
});