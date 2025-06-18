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
            <li>
                <a href="{{ route('cotizaciones.show', $cotizacion) }}" class="hover:text-bioscom-primary transition-colors">
                    {{ $cotizacion->codigo }}
                </a>
            </li>
            <li class="text-gray-400">/</li>
            <li class="text-gray-800 font-medium">Editar</li>
        </ol>
    </nav>

    <!-- Header de la página -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    <i class="fas fa-edit text-bioscom-primary mr-3"></i>
                    Editar Cotización
                </h1>
                <p class="text-gray-600">
                    Modificando: <span class="font-medium">{{ $cotizacion->nombre_cotizacion }}</span> 
                    ({{ $cotizacion->codigo }})
                </p>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Estado Actual</div>
                <div class="text-lg font-bold px-3 py-1 rounded-lg
                    {{ $cotizacion->estado === 'Ganada' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $cotizacion->estado === 'Perdida' ? 'bg-red-100 text-red-800' : '' }}
                    {{ $cotizacion->estado === 'Pendiente' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $cotizacion->estado === 'Enviada' ? 'bg-blue-100 text-blue-800' : '' }}
                ">
                    {{ $cotizacion->estado }}
                </div>
            </div>
        </div>
    </div>

    <!-- Información rápida de la cotización -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Creada</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $cotizacion->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-building text-green-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Cliente</p>
                    <p class="text-sm font-semibold text-gray-900">
                        {{ $cotizacion->cliente->nombre_institucion ?? $cotizacion->nombre_institucion }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Vence</p>
                    <p class="text-sm font-semibold text-gray-900">
                        @if($cotizacion->validez_oferta)
                            {{ is_string($cotizacion->validez_oferta) ? \Carbon\Carbon::parse($cotizacion->validez_oferta)->format('d/m/Y') : $cotizacion->validez_oferta->format('d/m/Y') }}
                        @else
                            No definida
                        @endif
                    </p>
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
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total</p>
                    <p class="text-sm font-semibold text-gray-900">${{ number_format($cotizacion->total_con_iva, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario Vue de Cotización (MODO EDICIÓN) -->
    <div id="app">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <cotizacion-form 
                :initial-cotizacion="{{ json_encode($cotizacionData) }}"
                
            ></cotizacion-form>
        </div>
    </div>

    <!-- Enlaces de navegación rápida -->
    <div class="mt-6 flex flex-wrap gap-3 justify-center">
        <a href="{{ route('cotizaciones.show', $cotizacion) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bioscom-primary transition-colors">
            <i class="fas fa-eye mr-2 text-gray-500"></i>
            Ver Cotización
        </a>
        
        <a href="{{ route('cotizaciones.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bioscom-primary transition-colors">
            <i class="fas fa-list mr-2 text-gray-500"></i>
            Todas las Cotizaciones
        </a>
        
        <button class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm bg-green-600 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
            <i class="fas fa-copy mr-2"></i>
            Duplicar Cotización
        </button>
    </div>
</div>
@endsection