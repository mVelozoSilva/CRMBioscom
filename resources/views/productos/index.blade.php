@extends('layouts.app')

@section('title', 'Catálogo de Productos - CRM Bioscom')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-boxes text-blue-600 mr-3"></i>
                    Catálogo de Productos
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Gestiona el inventario de productos con constructor visual para cotizaciones profesionales
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                <a href="{{ route('productos.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-plus mr-2"></i>
                    Nuevo Producto
                </a>
                <button type="button" id="btn-importar" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-upload mr-2"></i>
                    Importar
                </button>
                <button type="button" id="btn-exportar" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-download mr-2"></i>
                    Exportar
                </button>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-check-circle text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 truncate">Activos</p>
                            <p class="text-2xl font-semibold text-gray-900">
                                {{ $productos->where('estado', 'Activo')->count() }}
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
                                <i class="fas fa-pause-circle text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 truncate">Inactivos</p>
                            <p class="text-2xl font-semibold text-gray-900">
                                {{ $productos->where('estado', 'Inactivo')->count() }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-layer-group text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 truncate">Categorías</p>
                            <p class="text-2xl font-semibold text-gray-900">
                                {{ $categorias->count() }}
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
                                <i class="fas fa-dollar-sign text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 truncate">Valor Total</p>
                            <p class="text-2xl font-semibold text-gray-900">
                                ${{ number_format($productos->sum('precio_neto'), 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros Avanzados -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Filtros</h3>
            </div>
            <div class="p-6">
                <form method="GET" action="{{ route('productos.index') }}" id="form-filtros">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <!-- Búsqueda -->
                        <div class="lg:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                Buscar producto
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       name="search" 
                                       id="search"
                                       value="{{ request('search') }}"
                                       placeholder="Nombre, descripción o categoría..."
                                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search h-5 w-5 text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Categoría -->
                        <div>
                            <label for="categoria" class="block text-sm font-medium text-gray-700 mb-1">
                                Categoría
                            </label>
                            <select name="categoria" id="categoria" 
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Todas las categorías</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria }}" 
                                            {{ request('categoria') === $categoria ? 'selected' : '' }}>
                                        {{ $categoria }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">
                                Estado
                            </label>
                            <select name="estado" id="estado" 
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Todos los estados</option>
                                <option value="Activo" {{ request('estado') === 'Activo' ? 'selected' : '' }}>
                                    Activo
                                </option>
                                <option value="Inactivo" {{ request('estado') === 'Inactivo' ? 'selected' : '' }}>
                                    Inactivo
                                </option>
                            </select>
                        </div>

                        <!-- Rango de Precios -->
                        <div>
                            <label for="precio_min" class="block text-sm font-medium text-gray-700 mb-1">
                                Precio mínimo
                            </label>
                            <input type="number" 
                                   name="precio_min" 
                                   id="precio_min"
                                   value="{{ request('precio_min') }}"
                                   placeholder="0"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-4 mt-4">
                        <!-- Precio Máximo -->
                        <div>
                            <label for="precio_max" class="block text-sm font-medium text-gray-700 mb-1">
                                Precio máximo
                            </label>
                            <input type="number" 
                                   name="precio_max" 
                                   id="precio_max"
                                   value="{{ request('precio_max') }}"
                                   placeholder="Sin límite"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Ordenamiento -->
                        <div>
                            <label for="sort_by" class="block text-sm font-medium text-gray-700 mb-1">
                                Ordenar por
                            </label>
                            <select name="sort_by" id="sort_by" 
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="nombre" {{ request('sort_by') === 'nombre' ? 'selected' : '' }}>
                                    Nombre
                                </option>
                                <option value="precio_neto" {{ request('sort_by') === 'precio_neto' ? 'selected' : '' }}>
                                    Precio
                                </option>
                                <option value="categoria" {{ request('sort_by') === 'categoria' ? 'selected' : '' }}>
                                    Categoría
                                </option>
                                <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>
                                    Fecha de creación
                                </option>
                            </select>
                        </div>

                        <!-- Dirección -->
                        <div>
                            <label for="sort_direction" class="block text-sm font-medium text-gray-700 mb-1">
                                Dirección
                            </label>
                            <select name="sort_direction" id="sort_direction" 
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="asc" {{ request('sort_direction') === 'asc' ? 'selected' : '' }}>
                                    Ascendente
                                </option>
                                <option value="desc" {{ request('sort_direction') === 'desc' ? 'selected' : '' }}>
                                    Descendente
                                </option>
                            </select>
                        </div>

                        <!-- Botones -->
                        <div class="flex items-end space-x-2">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-filter mr-2"></i>
                                Filtrar
                            </button>
                            <a href="{{ route('productos.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-eraser mr-2"></i>
                                Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Vista de Productos -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">
                    Productos ({{ $productos->total() }})
                </h3>
                <div class="flex space-x-2">
                    <button type="button" id="btn-vista-lista" 
                            class="p-2 text-gray-400 hover:text-gray-600"
                            :class="{ 'text-blue-600': vistaActual === 'lista' }">
                        <i class="fas fa-list"></i>
                    </button>
                    <button type="button" id="btn-vista-tarjetas" 
                            class="p-2 text-gray-400 hover:text-gray-600"
                            :class="{ 'text-blue-600': vistaActual === 'tarjetas' }">
                        <i class="fas fa-th-large"></i>
                    </button>
                </div>
            </div>

            <!-- Vista Lista (por defecto) -->
            <div id="vista-lista" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Producto
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Categoría
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Precio
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Constructor
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($productos as $producto)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                           @php
                                                $imagenes = is_string($producto->imagenes) ? json_decode($producto->imagenes, true) : ($producto->imagenes ?? []);
                                                $imagenPrincipal = ($imagenes[0]['url'] ?? null) ?: '/images/producto-default.png';
                                            @endphp
                                            <img class="h-10 w-10 rounded-lg object-cover" 
                                                 src="{{ $imagenPrincipal }}" 
                                                 alt="{{ $producto->nombre }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $producto->nombre }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ Str::limit($producto->descripcion, 50) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $producto->categoria }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${{ number_format($producto->precio_neto, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $producto->estado === 'Activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $producto->estado }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($producto->bloques_contenido)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-magic mr-1"></i>
                                            Visual
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Básico
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('productos.show', $producto) }}" 
                                           class="text-blue-600 hover:text-blue-900"
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('productos.edit', $producto) }}" 
                                           class="text-indigo-600 hover:text-indigo-900"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($producto->bloques_contenido)
                                            <button onclick="previsualizarProducto({{ $producto->id }})"
                                                    class="text-purple-600 hover:text-purple-900"
                                                    title="Previsualizar constructor">
                                                <i class="fas fa-magic"></i>
                                            </button>
                                        @endif
                                        <button onclick="duplicarProducto({{ $producto->id }})"
                                                class="text-green-600 hover:text-green-900"
                                                title="Duplicar">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <form action="{{ route('productos.destroy', $producto) }}" 
                                              method="POST" 
                                              class="inline-block"
                                              onsubmit="return confirm('¿Estás seguro de que quieres desactivar este producto?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Desactivar">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-box-open text-4xl text-gray-400 mb-4"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay productos</h3>
                                        <p class="text-gray-500 mb-4">Comienza agregando tu primer producto al catálogo.</p>
                                        <a href="{{ route('productos.create') }}" 
                                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                            <i class="fas fa-plus mr-2"></i>
                                            Agregar Producto
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Vista Tarjetas (oculta por defecto) -->
            <div id="vista-tarjetas" class="hidden p-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($productos as $producto)
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                            @php
                                $imagenes = is_string($producto->imagenes) ? json_decode($producto->imagenes, true) : ($producto->imagenes ?? []);
                                $imagenPrincipal = ($imagenes[0]['url'] ?? null) ?: '/images/producto-default.png';
                            @endphp
                            <div class="aspect-w-16 aspect-h-10">
                                <img src="{{ $imagenPrincipal }}" 
                                     alt="{{ $producto->nombre }}"
                                     class="w-full h-48 object-cover rounded-t-lg">
                            </div>
                            <div class="p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-medium text-gray-900 mb-1">
                                            {{ $producto->nombre }}
                                        </h3>
                                        <p class="text-sm text-gray-600 mb-2">
                                            {{ Str::limit($producto->descripcion, 80) }}
                                        </p>
                                        <div class="flex items-center justify-between">
                                            <span class="text-lg font-bold text-blue-600">
                                                ${{ number_format($producto->precio_neto, 0, ',', '.') }}
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $producto->estado === 'Activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $producto->estado }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 flex justify-between items-center">
                                    <span class="text-xs text-gray-500">{{ $producto->categoria }}</span>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('productos.show', $producto) }}" 
                                           class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('productos.edit', $producto) }}" 
                                           class="text-indigo-600 hover:text-indigo-800">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Paginación -->
            @if($productos->hasPages())
                <div class="bg-white px-6 py-4 border-t border-gray-200">
                    {{ $productos->links() }}
                </div>
            @endif
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
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="titulo-preview">
                        Previsualización del Producto
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
    // Alternar vistas
    const btnVistaLista = document.getElementById('btn-vista-lista');
    const btnVistaTarjetas = document.getElementById('btn-vista-tarjetas');
    const vistaLista = document.getElementById('vista-lista');
    const vistaTarjetas = document.getElementById('vista-tarjetas');

    btnVistaLista.addEventListener('click', function() {
        vistaLista.classList.remove('hidden');
        vistaTarjetas.classList.add('hidden');
        btnVistaLista.classList.add('text-blue-600');
        btnVistaTarjetas.classList.remove('text-blue-600');
    });

    btnVistaTarjetas.addEventListener('click', function() {
        vistaLista.classList.add('hidden');
        vistaTarjetas.classList.remove('hidden');
        btnVistaTarjetas.classList.add('text-blue-600');
        btnVistaLista.classList.remove('text-blue-600');
    });
});

// Función para previsualizar producto
async function previsualizarProducto(id) {
    try {
        const response = await fetch(`/crm-bioscom/public/api/productos/${id}/detalles-completos`);
        const data = await response.json();
        
        if (data.success && data.producto.bloques_contenido) {
            const previewResponse = await fetch('/crm-bioscom/public/api/productos/previsualizar-constructor', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    bloques_contenido: data.producto.bloques_contenido,
                    plantilla_base: data.producto.plantilla_base,
                    configuracion_visual: data.producto.configuracion_visual
                })
            });
            
            const previewData = await previewResponse.json();
            
            if (previewData.success) {
                document.getElementById('titulo-preview').textContent = `Previsualización - ${data.producto.nombre}`;
                document.getElementById('contenido-preview').innerHTML = previewData.html;
                document.getElementById('modal-preview').classList.remove('hidden');
            }
        } else {
            alert('Este producto no tiene contenido visual configurado.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar la previsualización.');
    }
}

// Función para duplicar producto
async function duplicarProducto(id) {
    if (confirm('¿Estás seguro de que quieres duplicar este producto?')) {
        try {
            const response = await fetch(`/crm-bioscom/public/api/productos/${id}/duplicar`, {
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

// Función para cerrar preview
function cerrarPreview() {
    document.getElementById('modal-preview').classList.add('hidden');
}
</script>
@endsection