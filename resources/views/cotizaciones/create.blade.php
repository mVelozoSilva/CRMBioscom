@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-sm text-gray-600">
            <li>
                <a href="{{ route('dashboard') }}" class="hover:text-bioscom-primary transition-colors">
                    <i class="fas fa-home mr-1"></i>Dashboard
                </a>
            </li>
            <li class="text-gray-400">/</li>
            <li>
                <a href="{{ route('cotizaciones.index') }}" class="hover:text-bioscom-primary transition-colors">
                    Cotizaciones
                </a>
            </li>
            <li class="text-gray-400">/</li>
            <li class="text-gray-800 font-medium">Crear Nueva</li>
        </ol>
    </nav>

    <!-- Header de la página -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    <i class="fas fa-file-invoice text-bioscom-primary mr-3"></i>
                    Crear Nueva Cotización
                </h1>
                <p class="text-gray-600">
                    Complete el formulario para generar una nueva cotización profesional para sus clientes
                </p>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Estado</div>
                <div class="text-lg font-bold text-green-600">Nueva Cotización</div>
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Clientes Activos</p>
                    <p class="text-lg font-semibold text-gray-900">{{ \App\Models\Cliente::count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-box text-green-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Productos Disponibles</p>
                    <p class="text-lg font-semibold text-gray-900">{{ \App\Models\Producto::where('estado', 'Activo')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-invoice text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Cotizaciones Este Mes</p>
                    <p class="text-lg font-semibold text-gray-900">{{ \App\Models\Cotizacion::whereMonth('created_at', now()->month)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Pendiente</p>
                    <p class="text-lg font-semibold text-gray-900">${{ number_format(\App\Models\Cotizacion::where('estado', 'Pendiente')->sum('total_con_iva'), 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- APLICACIÓN VUE.JS -->
    <div id="app">
        <!-- Componente Vue de Cotización -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <cotizacion-form 
                @if(isset($cliente))
                :cliente-preseleccionado="{{ json_encode($cliente) }}"
                @endif
            ></cotizacion-form>
        </div>
    </div>

    <!-- Enlaces de navegación rápida -->
    <div class="mt-6 flex flex-wrap gap-3 justify-center">
        <a href="{{ route('cotizaciones.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bioscom-primary transition-colors">
            <i class="fas fa-list mr-2 text-gray-500"></i>
            Ver Todas las Cotizaciones
        </a>
        
        <a href="{{ route('clientes.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm bg-bioscom-primary text-sm font-medium text-white hover:bg-bioscom-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bioscom-primary transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Crear Cliente Nuevo
        </a>
        
        <a href="{{ route('productos.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm bg-green-600 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
            <i class="fas fa-box mr-2"></i>
            Agregar Producto
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
console.log('🚀 Scripts de create.blade.php cargándose...');
console.log('✅ Vue disponible:', typeof Vue !== 'undefined');
console.log('✅ Axios disponible:', typeof axios !== 'undefined');
</script>
@endpush