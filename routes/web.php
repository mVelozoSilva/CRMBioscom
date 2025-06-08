<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController; // Importa DashboardController
use App\Http\Controllers\ClienteController;   // Importa ClienteController
use App\Http\Controllers\ProductoController; // Importa ProductoController
use App\Http\Controllers\CotizacionController; // Importa CotizacionController

// --- Rutas del Dashboard ---
// Ruta principal conectada a DashboardController
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// --- Rutas de Clientes (CRUD) ---
// Esta línea crea todas las rutas CRUD para el recurso 'clientes'
Route::resource('clientes', ClienteController::class);

// --- Rutas de Productos (CRUD) ---
// Esta línea crea todas las rutas CRUD para el recurso 'productos'
Route::resource('productos', ProductoController::class);

// --- Rutas de Cotizaciones (CRUD) ---
Route::resource('cotizaciones', CotizacionController::class); // Esta línea crea todas las rutas CRUD para el recurso 'cotizaciones'

Route::put('/cotizaciones/{cotizacion}', function ($cotizacionId) {
    // Log::info('Petición PUT a cotizaciones/{id} capturada en routes/web.php. ID:', [$cotizacionId]); // Puedes usar Log::info si prefieres
    dd('Petición PUT a cotizaciones/{id} capturada en routes/web.php. ID: ' . $cotizacionId);
})->name('cotizaciones.update_debug'); // Le damos un nombre temporal por si acaso
