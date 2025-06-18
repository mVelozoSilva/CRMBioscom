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
            <li class="text-gray-800 font-medium">{{ $cotizacion->codigo }}</li>
        </ol>
    </nav>

    <!-- Header de la cotización -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    <i class="fas fa-file-invoice text-bioscom-primary mr-3"></i>
                    {{ $cotizacion->nombre_cotizacion }}
                </h1>
                <p class="text-gray-600">Código: <span class="font-medium">{{ $cotizacion->codigo }}</span></p>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Estado</div>
                <div class="text-2xl font-bold px-3 py-1 rounded-lg
                    {{ ($cotizacion->estado ?? '') === 'Ganada' ? 'bg-green-100 text-green-800' : '' }}
                    {{ ($cotizacion->estado ?? '') === 'Perdida' ? 'bg-red-100 text-red-800' : '' }}
                    {{ ($cotizacion->estado ?? '') === 'Pendiente' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ ($cotizacion->estado ?? '') === 'Enviada' ? 'bg-blue-100 text-blue-800' : '' }}
                ">
                    {{ $cotizacion->estado }}
                </div>
            </div>
        </div>
    </div>

    <!-- Información del cliente y detalles -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Información del Cliente -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-building text-green-500 mr-2"></i>
                Información del Cliente
            </h2>
            
            <div class="space-y-3">
                <div>
                    <span class="text-sm font-medium text-gray-500">Institución:</span>
                    <p class="text-gray-900">
                        @if($cotizacion->cliente)
                            {{ $cotizacion->cliente->nombre_institucion }}
                        @else
                            {{ $cotizacion->nombre_institucion }}
                        @endif
                    </p>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-500">RUT:</span>
                    <p class="text-gray-900">{{ $cotizacion->cliente->rut ?? 'No disponible' }}</p>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-500">Contacto:</span>
                    <p class="text-gray-900">{{ $cotizacion->nombre_contacto }}</p>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-500">Email:</span>
                    <p class="text-gray-900">{{ $cotizacion->cliente->email ?? 'No disponible' }}</p>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-500">Teléfono:</span>
                    <p class="text-gray-900">{{ $cotizacion->cliente->telefono ?? 'No disponible' }}</p>
                </div>
            </div>
        </div>

        <!-- Detalles de la Cotización -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                Detalles de la Cotización
            </h2>
            
            <div class="space-y-3">
                <div>
                    <span class="text-sm font-medium text-gray-500">Válida hasta:</span>
                    <p class="text-gray-900">
                        @if($cotizacion->validez_oferta)
                            @if(is_string($cotizacion->validez_oferta))
                                {{ \Carbon\Carbon::parse($cotizacion->validez_oferta)->format('d/m/Y') }}
                            @else
                                {{ $cotizacion->validez_oferta->format('d/m/Y') }}
                            @endif
                        @else
                            No especificada
                        @endif
                    </p>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-500">Vendedor:</span>
                    <p class="text-gray-900">{{ $cotizacion->vendedor->name ?? 'Sin asignar' }}</p>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-500">Info. Contacto Vendedor:</span>
                    <p class="text-gray-900">{{ $cotizacion->info_contacto_vendedor ?? 'No especificada' }}</p>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-500">Fecha de Creación:</span>
                    <p class="text-gray-900">
                        @if($cotizacion->created_at)
                            {{ $cotizacion->created_at->format('d/m/Y H:i') }}
                        @else
                            No disponible
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Productos Cotizados -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-box text-yellow-500 mr-2"></i>
            Productos Cotizados
        </h2>
        
        @php
            $productos = null;
            if (!empty($cotizacion->productos_cotizados)) {
                if (is_string($cotizacion->productos_cotizados)) {
                    $productos = json_decode($cotizacion->productos_cotizados, true);
                } else {
                    $productos = $cotizacion->productos_cotizados;
                }
            }
        @endphp
        
        @if($productos && is_array($productos) && count($productos) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unit.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descuento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($productos as $index => $producto)
                            <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $producto['nombre'] ?? $producto['nombre_producto'] ?? 'Sin nombre' }}
                                    </div>
                                    @if(isset($producto['descripcion']) && $producto['descripcion'])
                                        <div class="text-xs text-gray-500">{{ $producto['descripcion'] }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $producto['cantidad'] ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${{ number_format($producto['precio_unitario'] ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ ($producto['descuento'] ?? 0) }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ${{ number_format($producto['subtotal'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-box-open text-4xl mb-4"></i>
                <p>No hay productos en esta cotización</p>
            </div>
        @endif
    </div>

    <!-- Totales -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-calculator text-purple-500 mr-2"></i>
            Resumen de Totales
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Subtotal Neto</div>
                <div class="text-xl font-semibold text-gray-900">
                    ${{ number_format($cotizacion->total_neto ?? 0, 0, ',', '.') }}
                </div>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">IVA (19%)</div>
                <div class="text-xl font-semibold text-gray-900">
                    ${{ number_format($cotizacion->iva ?? 0, 0, ',', '.') }}
                </div>
            </div>
            
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <div class="text-sm text-blue-600">Total con IVA</div>
                <div class="text-2xl font-bold text-blue-800">
                    ${{ number_format($cotizacion->total_con_iva ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Condiciones Comerciales -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-handshake text-red-500 mr-2"></i>
            Condiciones Comerciales
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <span class="text-sm font-medium text-gray-500">Forma de Pago:</span>
                <p class="text-gray-900">{{ $cotizacion->forma_pago ?? 'No especificada' }}</p>
            </div>
            
            <div>
                <span class="text-sm font-medium text-gray-500">Plazo de Entrega:</span>
                <p class="text-gray-900">{{ $cotizacion->plazo_entrega ?? 'No especificado' }}</p>
            </div>
            
            <div class="md:col-span-2">
                <span class="text-sm font-medium text-gray-500">Garantía Técnica:</span>
                <p class="text-gray-900">{{ $cotizacion->garantia_tecnica ?? 'No especificada' }}</p>
            </div>
            
            @if($cotizacion->informacion_adicional)
            <div class="md:col-span-2">
                <span class="text-sm font-medium text-gray-500">Información Adicional:</span>
                <p class="text-gray-900">{{ $cotizacion->informacion_adicional }}</p>
            </div>
            @endif
            
            @if($cotizacion->descripcion_opcionales)
            <div class="md:col-span-2">
                <span class="text-sm font-medium text-gray-500">Productos y Servicios Opcionales:</span>
                <p class="text-gray-900">{{ $cotizacion->descripcion_opcionales }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="flex gap-3 justify-center">
        <a href="{{ route('cotizaciones.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bioscom-primary transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver a Cotizaciones
        </a>
        
        <a href="{{ route('cotizaciones.edit', $cotizacion->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm bg-bioscom-primary text-sm font-medium text-white hover:bg-bioscom-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bioscom-primary transition-colors">
            <i class="fas fa-edit mr-2"></i>
            Editar Cotización
        </a>
        
        <button class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm bg-green-600 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
            <i class="fas fa-download mr-2"></i>
            Descargar PDF
        </button>
    </div>
</div>
@endsection