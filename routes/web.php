<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\SeguimientoController; // ← AGREGAR ESTA LÍNEA

/*
|--------------------------------------------------------------------------
| Web Routes - Rutas para vistas Blade
|--------------------------------------------------------------------------
*/

// --- Ruta principal (Dashboard) ---
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// --- Rutas para Clientes (CRUD completo) ---
Route::resource('clientes', ClienteController::class);

// --- Rutas para Productos (CRUD completo) ---
Route::resource('productos', ProductoController::class);

// --- Rutas para Cotizaciones (CRUD completo) ---
Route::resource('cotizaciones', CotizacionController::class);

// --- Rutas del módulo de seguimiento ---
Route::prefix('seguimiento')->name('seguimiento.')->group(function () {
    Route::get('/', [SeguimientoController::class, 'index'])->name('index');
    Route::post('/', [SeguimientoController::class, 'store'])->name('store');
    Route::put('/{seguimiento}', [SeguimientoController::class, 'update'])->name('update');
    Route::delete('/{seguimiento}', [SeguimientoController::class, 'destroy'])->name('destroy');
    Route::post('/update-masivo', [SeguimientoController::class, 'updateMasivo'])->name('update-masivo');
    Route::post('/importar', [SeguimientoController::class, 'importar'])->name('importar');
});

// --- Rutas adicionales para búsquedas (usadas por Vue.js) ---
// Buscar clientes (autocompletado en cotizaciones)
Route::get('/api/buscar-clientes', [ClienteController::class, 'buscarClientes'])->name('buscar.clientes');

// Buscar productos (autocompletado en cotizaciones)  
Route::get('/api/buscar-productos', [ProductoController::class, 'buscarProductos'])->name('buscar.productos');

// Obtener cliente específico con datos completos
Route::get('/api/cliente/{cliente}', [ClienteController::class, 'show'])->name('api.cliente.show');

// Obtener producto específico con datos completos
Route::get('/api/producto/{producto}', [ProductoController::class, 'show'])->name('api.producto.show');