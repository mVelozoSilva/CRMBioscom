@extends('layouts.app')

@section('title', 'Mi Semana - Agenda')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl font-bold text-gray-900 leading-tight">
                        <i class="fas fa-calendar-week text-blue-600 mr-3"></i>
                        Mi Semana
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Planifica y organiza tus tareas semanales
                    </p>
                </div>
                <div class="mt-4 md:mt-0 md:ml-4">
                    <div class="flex items-center space-x-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-clock mr-1"></i>
                            {{ \Carbon\Carbon::now('America/Santiago')->format('H:i') }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <i class="fas fa-calendar mr-1"></i>
                            Semana {{ \Carbon\Carbon::now('America/Santiago')->weekOfYear }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Section -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-bolt text-yellow-500 mr-2"></i>
                    Acciones Rápidas
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Nueva Tarea -->
                    <button onclick="abrirModalNuevaTarea()" class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-plus text-blue-600"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-medium text-gray-900">Nueva Tarea</p>
                            <p class="text-xs text-gray-500">Crear rápidamente</p>
                        </div>
                    </button>

                    <!-- Mi Día -->
                    <a href="/crm-bioscom/public/agenda/mi-dia" class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-calendar-day text-green-600"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-medium text-gray-900">Mi Día</p>
                            <p class="text-xs text-gray-500">Vista diaria</p>
                        </div>
                    </a>

                    <!-- Seguimientos -->
                    <a href="/crm-bioscom/public/seguimiento" class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-chart-line text-purple-600"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-medium text-gray-900">Seguimientos</p>
                            <p class="text-xs text-gray-500">Gestiones comerciales</p>
                        </div>
                    </a>

                    <!-- Cotizaciones -->
                    <a href="/crm-bioscom/public/cotizaciones" class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-file-invoice text-orange-600"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-medium text-gray-900">Cotizaciones</p>
                            <p class="text-xs text-gray-500">Propuestas comerciales</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content: Vista Semanal -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <!-- Component Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                        Planificación Semanal
                    </h2>
                    <div class="flex space-x-3">
                        <button onclick="abrirModalNuevaTarea()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Nueva Tarea
                        </button>
                        <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Actualizar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Vue Component Mount Point -->
            <div id="tarea-week-view-container" class="p-6">
                <tarea-week-view></tarea-week-view>
            </div>
        </div>

        <!-- Modal de Nueva Tarea (reutilizado) -->
        <tarea-form 
            :visible="false" 
            ref="tareaForm"
        ></tarea-form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Función simple para abrir modal de nueva tarea (reutilizada de mi-dia)
function abrirModalNuevaTarea() {
    console.log('🔥 Abriendo modal de nueva tarea desde Mi Semana');
    // Acceder al componente TareaForm y mostrarlo
    const app = window.vueApp;
    if (app && app.$refs && app.$refs.tareaForm) {
        app.$refs.tareaForm.mostrarModal = true;
    }
}

window.abrirModalNuevaTarea = abrirModalNuevaTarea;
</script>
@endpush