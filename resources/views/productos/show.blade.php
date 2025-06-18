@extends('layouts.app')

@section('title', $producto->nombre . ' - CRM Bioscom')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('productos.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <i class="fas fa-boxes mr-2"></i>
                        Productos
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="text-sm font-medium text-gray-500">{{ $producto->nombre }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header con Acciones -->
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    {{ $producto->nombre }}
                    <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        {{ $producto->estado === 'Activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $producto->estado }}
                    </span>
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    {{ $producto->categoria }} • Creado {{ $producto->created_at->diffForHumans() }}
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                @if($producto->bloques_contenido)
                    <button onclick="previsualizarProducto()" 
                            class="inline-flex items-center px-4 py-2 border border-purple-300 rounded-md shadow-sm text-sm font-medium text-purple-700 bg-white hover:bg-purple-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        <i class="fas fa-magic mr-2"></i>
                        Previsualizar
                    </button>
                @endif
                <a href="{{ route('productos.edit', $producto) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-edit mr-2"></i>
                    Editar
                </a>
                <button onclick="duplicarProducto()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-copy mr-2"></i>
                    Duplicar
                </button>
                <a href="{{ route('productos.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </a>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-file-invoice text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 truncate">Cotizaciones</p>
                            <p class="text-2xl font-semibold text-gray-900">
                                {{ $estadisticas['total_cotizaciones'] ?? 0 }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 truncate">Ventas Mes</p>
                            <p class="text-2xl font-semibold text-gray-900">
                                ${{ number_format($estadisticas['ventas_ultimo_mes'] ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-chart-line text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 truncate">Precio Promedio</p>
                            <p class="text-2xl font-semibold text-gray-900">
                                ${{ number_format($estadisticas['precio_promedio_venta'] ?? $producto->precio_neto, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-star text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 truncate">Constructor</p>
                            <p class="text-lg font-semibold text-gray-900">
                                @if($producto->bloques_contenido)
                                    <span class="text-purple-600">Visual</span>
                                @else
                                    <span class="text-gray-400">Básico</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Contenido Principal -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Información General -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Información General</h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $producto->nombre }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Categoría</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $producto->categoria }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Precio Neto</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900">
                                    ${{ number_format($producto->precio_neto, 0, ',', '.') }} CLP
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Estado</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $producto->estado === 'Activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $producto->estado }}
                                    </span>
                                </dd>
                            </div>
                            @if($producto->descripcion)
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $producto->descripcion }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Galería de Imágenes -->
                @php
                    $imagenes = json_decode($producto->imagenes, true) ?? [];
                @endphp
                @if(count($imagenes) > 0)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Galería de Imágenes</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                                @foreach($imagenes as $index => $imagen)
                                    <div class="relative group">
                                        <img src="{{ $imagen['url'] }}" 
                                             alt="{{ $imagen['nombre_original'] ?? 'Imagen ' . ($index + 1) }}"
                                             class="h-24 w-full object-cover rounded-lg cursor-pointer hover:opacity-75 transition-opacity"
                                             onclick="abrirGaleria({{ $index }})">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-opacity rounded-lg flex items-center justify-center">
                                            <i class="fas fa-search-plus text-white opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Constructor Visual -->
                @if($producto->bloques_contenido)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                <i class="fas fa-magic text-purple-600 mr-2"></i>
                                Contenido Visual para Cotizaciones
                            </h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                Plantilla: {{ ucfirst($producto->plantilla_base ?? 'personalizada') }}
                            </span>
                        </div>
                        <div class="p-6">
                            @if($contenidoVisual)
                                <div id="contenido-visual-preview" class="border border-gray-200 rounded-lg p-4">
                                    {!! $contenidoVisual !!}
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <i class="fas fa-magic text-4xl text-purple-400 mb-4"></i>
                                    <h4 class="text-lg font-medium text-gray-900 mb-2">Constructor Visual Configurado</h4>
                                    <p class="text-gray-600 mb-4">Este producto tiene contenido visual personalizado para cotizaciones.</p>
                                    <button onclick="previsualizarProducto()" 
                                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700">
                                        <i class="fas fa-eye mr-2"></i>
                                        Ver Previsualización
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Especificaciones Técnicas -->
                @php
                    $especificaciones = json_decode($producto->especificaciones_tecnicas, true) ?? [];
                @endphp
                @if(count($especificaciones) > 0)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Especificaciones Técnicas</h3>
                        </div>
                        <div class="p-6">
                            <div class="overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($especificaciones as $spec)
                                            <tr>
                                                <td class="px-0 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $spec['clave'] }}
                                                </td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $spec['valor'] }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Cotizaciones Recientes -->
                @if($cotizaciones && $cotizaciones->count() > 0)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Cotizaciones Recientes</h3>
                        </div>
                        <div class="overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cotización
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cliente
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Estado
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Fecha
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($cotizaciones as $cotizacion)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <a href="{{ route('cotizaciones.show', $cotizacion) }}" 
                                                       class="text-blue-600 hover:text-blue-800">
                                                        {{ $cotizacion->nombre_cotizacion }}
                                                    </a>
                                                </div>
                                                <div class="text-sm text-gray-500">{{ $cotizacion->codigo }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $cotizacion->nombre_institucion }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ${{ number_format($cotizacion->total_con_iva, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @switch($cotizacion->estado)
                                                        @case('Ganada')
                                                            bg-green-100 text-green-800
                                                            @break
                                                        @case('Perdida')
                                                            bg-red-100 text-red-800
                                                            @break
                                                        @case('En Proceso')
                                                            bg-blue-100 text-blue-800
                                                            @break
                                                        @default
                                                            bg-yellow-100 text-yellow-800
                                                    @endswitch">
                                                    {{ $cotizacion->estado }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $cotizacion->created_at->format('d/m/Y') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($estadisticas['total_cotizaciones'] > 10)
                            <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                                <a href="{{ route('cotizaciones.index') }}?producto={{ $producto->id }}" 
                                   class="text-sm text-blue-600 hover:text-blue-800">
                                    Ver todas las cotizaciones ({{ $estadisticas['total_cotizaciones'] }})
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Panel Lateral -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Accesorios -->
                @php
                    $accesorios = json_decode($producto->accesorios, true) ?? [];
                @endphp
                @if(count($accesorios) > 0)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Accesorios Incluidos</h3>
                        </div>
                        <div class="p-6">
                            <ul class="space-y-2">
                                @foreach($accesorios as $accesorio)
                                    <li class="flex items-center text-sm text-gray-900">
                                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                        {{ $accesorio }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Opcionales -->
                @php
                    $opcionales = json_decode($producto->opcionales, true) ?? [];
                @endphp
                @if(count($opcionales) > 0)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Elementos Opcionales</h3>
                        </div>
                        <div class="p-6">
                            <ul class="space-y-3">
                                @foreach($opcionales as $opcional)
                                    <li class="flex items-center justify-between">
                                        <span class="text-sm text-gray-900">{{ $opcional['nombre'] }}</span>
                                        <span class="text-sm font-medium text-gray-600">
                                            ${{ number_format($opcional['precio'], 0, ',', '.') }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Información de Garantía -->
                @if($producto->garantias)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Garantía</h3>
                        </div>
                        <div class="p-6">
                            <p class="text-sm text-gray-900">{{ $producto->garantias }}</p>
                        </div>
                    </div>
                @endif

                <!-- Documentos -->
                @php
                    $documentos = json_decode($producto->documentos, true) ?? [];
                @endphp
                @if(count($documentos) > 0)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Documentos</h3>
                        </div>
                        <div class="p-6">
                            <ul class="space-y-3">
                                @foreach($documentos as $documento)
                                    <li>
                                        <a href="{{ $documento['url'] }}" 
                                           target="_blank"
                                           class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                            <i class="fas fa-file-pdf text-red-500 mr-3"></i>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    {{ $documento['nombre_original'] }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ number_format($documento['tamaño'] / 1024, 0) }} KB
                                                </p>
                                            </div>
                                            <i class="fas fa-download text-gray-400"></i>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Metadatos -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Información del Sistema</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">ID</dt>
                            <dd class="mt-1 text-sm text-gray-900">#{{ $producto->id }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Creado</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $producto->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Última modificación</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $producto->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Usado en cotizaciones</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $estadisticas['total_cotizaciones'] ?? 0 }} veces</dd>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Acciones Rápidas</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <button onclick="agregarACotizacion()" 
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-plus mr-2"></i>
                            Agregar a Cotización
                        </button>
                        
                        <button onclick="crearSeguimiento()" 
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-tasks mr-2"></i>
                            Crear Seguimiento
                        </button>
                        
                        <button onclick="exportarDatos()" 
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-download mr-2"></i>
                            Exportar Datos
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Galería -->
<div id="modal-galeria" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
        <div class="fixed inset-0 bg-black bg-opacity-75 transition-opacity" onclick="cerrarGaleria()"></div>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all my-8 align-middle max-w-4xl w-full">
            <div class="bg-white px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="titulo-galeria">
                        Galería de Imágenes
                    </h3>
                    <div class="flex items-center space-x-4">
                        <button onclick="imagenAnterior()" class="p-2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span id="contador-imagenes" class="text-sm text-gray-500">1 / 1</span>
                        <button onclick="imagenSiguiente()" class="p-2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <button onclick="cerrarGaleria()" class="p-2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <img id="imagen-galeria" src="" alt="" class="w-full h-auto max-h-96 object-contain mx-auto">
            </div>
        </div>
    </div>
</div>

<!-- Modal de Previsualización -->
<div id="modal-preview" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="cerrarPreview()"></div>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all my-8 align-middle max-w-4xl w-full">
            <div class="bg-white px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Previsualización - {{ $producto->nombre }}
                    </h3>
                    <button onclick="cerrarPreview()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div id="contenido-preview" class="p-6 max-h-96 overflow-y-auto">
                <!-- Contenido dinámico -->
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales para galería
    const imagenes = @json($imagenes ?? []);
    let imagenActual = 0;

    // Función para abrir galería
    window.abrirGaleria = function(index) {
        imagenActual = index;
        mostrarImagenActual();
        document.getElementById('modal-galeria').classList.remove('hidden');
    };

    // Función para cerrar galería
    window.cerrarGaleria = function() {
        document.getElementById('modal-galeria').classList.add('hidden');
    };

    // Función para mostrar imagen actual
    function mostrarImagenActual() {
        if (imagenes.length === 0) return;
        
        const imagen = imagenes[imagenActual];
        document.getElementById('imagen-galeria').src = imagen.url;
        document.getElementById('imagen-galeria').alt = imagen.nombre_original || `Imagen ${imagenActual + 1}`;
        document.getElementById('contador-imagenes').textContent = `${imagenActual + 1} / ${imagenes.length}`;
    }

    // Función para imagen anterior
    window.imagenAnterior = function() {
        imagenActual = (imagenActual - 1 + imagenes.length) % imagenes.length;
        mostrarImagenActual();
    };

    // Función para imagen siguiente
    window.imagenSiguiente = function() {
        imagenActual = (imagenActual + 1) % imagenes.length;
        mostrarImagenActual();
    };

    // Navegación con teclado
    document.addEventListener('keydown', function(e) {
        const modalGaleria = document.getElementById('modal-galeria');
        if (!modalGaleria.classList.contains('hidden')) {
            switch(e.key) {
                case 'ArrowLeft':
                    imagenAnterior();
                    break;
                case 'ArrowRight':
                    imagenSiguiente();
                    break;
                case 'Escape':
                    cerrarGaleria();
                    break;
            }
        }
    });
});

// Función para previsualizar producto
async function previsualizarProducto() {
    try {
        const response = await fetch('/crm-bioscom/public/api/productos/previsualizar-constructor', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                bloques_contenido: @json($producto->bloques_contenido),
                plantilla_base: @json($producto->plantilla_base),
                configuracion_visual: @json($producto->configuracion_visual)
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('contenido-preview').innerHTML = data.html;
            document.getElementById('modal-preview').classList.remove('hidden');
        } else {
            alert('Error al generar la previsualización.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar la previsualización.');
    }
}

// Función para duplicar producto
async function duplicarProducto() {
    if (confirm('¿Estás seguro de que quieres duplicar este producto?')) {
        try {
            const response = await fetch(`/crm-bioscom/public/api/productos/{{ $producto->id }}/duplicar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.location.href = `/productos/${data.producto_id}/edit`;
            } else {
                alert('Error al duplicar el producto.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al duplicar el producto.');
        }
    }
}

// Función para agregar a cotización
function agregarACotizacion() {
    // Redirigir a crear cotización con producto preseleccionado
    window.location.href = `/cotizaciones/create?producto={{ $producto->id }}`;
}

// Función para crear seguimiento
function crearSeguimiento() {
    // Implementar modal o redirección para crear seguimiento
    alert('Funcionalidad de seguimiento en desarrollo.');
}

// Función para exportar datos
function exportarDatos() {
    // Implementar exportación de datos del producto
    window.location.href = `/crm-bioscom/public/api/productos/{{ $producto->id }}/exportar`;
}

// Función para cerrar preview
function cerrarPreview() {
    document.getElementById('modal-preview').classList.add('hidden');
}
</script>
@endsection

@section('styles')
<style>
/* Estilos personalizados para la vista detallada */
.modal-overlay {
    backdrop-filter: blur(4px);
}

.galeria-navegacion {
    transition: opacity 0.3s ease;
}

.imagen-galeria {
    transition: opacity 0.3s ease;
    max-height: 70vh;
}

/* Animaciones suaves */
.fade-in {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Estilos para las tablas */
.tabla-cotizaciones tr:hover {
    background-color: #f9fafb;
    transition: background-color 0.2s ease;
}

/* Estilos para documentos */
.documento-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .imagen-galeria {
        max-height: 50vh;
    }
    
    .modal-content {
        margin: 1rem;
    }
}
</style>
@endsection