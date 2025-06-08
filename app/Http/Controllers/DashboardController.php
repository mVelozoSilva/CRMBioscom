<?php

namespace App\Http\Controllers;

use App\Models\Cliente; // Importa el modelo Cliente para obtener datos
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Muestra la pantalla principal (Dashboard).
     */
    public function index()
    {
        // Obtener datos para el Dashboard:
        $totalClientes = Cliente::count(); // Obtiene el número total de clientes

        // Aquí agregarías lógica para:
        // $tareasPendientes = ...
        // $seguimientosPendientes = ...
        // $ventasCerradas = ...
        // $nuevosClientesMes = ... (ya tienes totalClientes, podrías filtrar por fecha)
        // $solicitudesServicioTecnico = ... (cuando crees ese módulo)

        // Pasar los datos a la vista del Dashboard
        return view('dashboard.index', compact('totalClientes'));
    }
}