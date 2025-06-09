<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\SeguimientoController; // ← AGREGAR ESTA LÍNEA

/*
|--------------------------------------------------------------------------
| API Routes - Rutas para Vue.js y AJAX
|--------------------------------------------------------------------------
*/

// Ruta para obtener información del usuario autenticado (si usas autenticación)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// --- Rutas API para Clientes ---
Route::prefix('clientes')->name('api.clientes.')->group(function () {
    Route::get('/', [ClienteController::class, 'index'])->name('index'); // Listar clientes
    Route::post('/', [ClienteController::class, 'store'])->name('store'); // Crear cliente
    Route::get('/{cliente}', [ClienteController::class, 'show'])->name('show'); // Ver cliente
    Route::put('/{cliente}', [ClienteController::class, 'update'])->name('update'); // Actualizar cliente
    Route::delete('/{cliente}', [ClienteController::class, 'destroy'])->name('destroy'); // Eliminar cliente
});

// --- Rutas API para Productos ---
Route::prefix('productos')->name('api.productos.')->group(function () {
    Route::get('/', [ProductoController::class, 'index'])->name('index'); // Listar productos
    Route::post('/', [ProductoController::class, 'store'])->name('store'); // Crear producto
    Route::get('/{producto}', [ProductoController::class, 'show'])->name('show'); // Ver producto
    Route::put('/{producto}', [ProductoController::class, 'update'])->name('update'); // Actualizar producto
    Route::delete('/{producto}', [ProductoController::class, 'destroy'])->name('destroy'); // Eliminar producto
});

// --- Rutas API para Cotizaciones ---
Route::prefix('cotizaciones')->name('api.cotizaciones.')->group(function () {
    Route::get('/', [CotizacionController::class, 'index'])->name('index'); // Listar cotizaciones
    Route::post('/', [CotizacionController::class, 'store'])->name('store'); // Crear cotización
    Route::get('/{cotizacion}', [CotizacionController::class, 'show'])->name('show'); // Ver cotización
    Route::put('/{cotizacion}', [CotizacionController::class, 'update'])->name('update'); // Actualizar cotización
    Route::delete('/{cotizacion}', [CotizacionController::class, 'destroy'])->name('destroy'); // Eliminar cotización
});

// --- Rutas API para Contactos ---
Route::prefix('contactos')->name('api.contactos.')->group(function () {
    Route::get('/', [ContactoController::class, 'index'])->name('index'); // Listar contactos
    Route::post('/', [ContactoController::class, 'store'])->name('store'); // Crear contacto
    Route::get('/{contacto}', [ContactoController::class, 'show'])->name('show'); // Ver contacto
    Route::put('/{contacto}', [ContactoController::class, 'update'])->name('update'); // Actualizar contacto
    Route::delete('/{contacto}', [ContactoController::class, 'destroy'])->name('destroy'); // Eliminar contacto
    Route::get('/cliente/{cliente}', [ContactoController::class, 'porCliente'])->name('porCliente'); // Contactos de un cliente
});

// --- APIs para el módulo de seguimiento ---
Route::prefix('seguimiento')->group(function () {
    Route::get('/data', [SeguimientoController::class, 'getSeguimientos']);
    Route::post('/update-masivo', [SeguimientoController::class, 'updateMasivo']);
    Route::get('/buscar-clientes', [SeguimientoController::class, 'buscarClientes']);
    Route::get('/vendedores', [SeguimientoController::class, 'getVendedores']);
});

// --- Rutas específicas para autocompletado ---
// Buscar clientes para autocompletado en cotizaciones
Route::get('/buscar-clientes', [ClienteController::class, 'buscarClientes'])->name('api.buscar-clientes');

// Buscar productos para autocompletado en cotizaciones  
Route::get('/buscar-productos', [ProductoController::class, 'buscarProductos'])->name('api.buscar-productos');

// Buscar contactos para autocompletado
Route::get('/buscar-contactos', [ContactoController::class, 'buscar'])->name('api.buscar-contactos');