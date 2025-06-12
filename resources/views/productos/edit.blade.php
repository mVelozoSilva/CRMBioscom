@extends('layouts.app')

@section('title', 'Editar Producto - CRM Bioscom')

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
                        <a href="{{ route('productos.show', $producto) }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">
                            {{ Str::limit($producto->nombre, 30) }}
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="text-sm font-medium text-gray-500">Editar</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-edit text-blue-600 mr-3"></i>
                    Editar Producto
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Modifica la información y configuración visual de <strong>{{ $producto->nombre }}</strong>
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                <a href="{{ route('productos.show', $producto) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-eye mr-2"></i>
                    Ver Producto
                </a>
                <a href="{{ route('productos.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </a>
            </div>
        </div>

        <!-- Información del Estado Actual -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-400 mt-0.5"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Información del Producto</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Estado actual: <strong>{{ $producto->estado }}</strong></li>
                            <li>Categoría: <strong>{{ $producto->categoria }}</strong></li>
                            <li>Precio actual: <strong>${{ number_format($producto->precio_neto, 0, ',', '.') }}</strong></li>
                            <li>Creado: <strong>{{ $producto->created_at->format('d/m/Y H:i') }}</strong></li>
                            <li>Última modificación: <strong>{{ $producto->updated_at->format('d/m/Y H:i') }}</strong></li>
                            @if($producto->bloques_contenido)
                                <li>Constructor visual: <span class="text-purple-700 font-medium">Configurado</span></li>
                            @else
                                <li>Constructor visual: <span class="text-gray-600">No configurado</span></li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario Principal -->
        <form action="{{ route('productos.update', $producto) }}" method="POST" enctype="multipart/form-data" id="form-producto">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Información Básica -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Datos Generales -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Información General</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Nombre del Producto -->
                            <div>
                                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre del Producto <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="nombre" 
                                       id="nombre"
                                       value="{{ old('nombre', $producto->nombre) }}"
                                       required
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nombre') border-red-300 @enderror">
                                @error('nombre')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Descripción -->
                            <div>
                                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                                    Descripción
                                </label>
                                <textarea name="descripcion" 
                                          id="descripcion"
                                          rows="4"
                                          class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('descripcion') border-red-300 @enderror">{{ old('descripcion', $producto->descripcion) }}</textarea>
                                @error('descripcion')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Fila de Categoría y Precio -->
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <!-- Categoría -->
                                <div>
                                    <label for="categoria" class="block text-sm font-medium text-gray-700 mb-2">
                                        Categoría <span class="text-red-500">*</span>
                                    </label>
                                    <select name="categoria" 
                                            id="categoria" 
                                            required
                                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('categoria') border-red-300 @enderror">
                                        <option value="">Seleccionar categoría</option>
                                        @foreach($categorias as $categoria)
                                            <option value="{{ $categoria }}" 
                                                    {{ old('categoria', $producto->categoria) === $categoria ? 'selected' : '' }}>
                                                {{ $categoria }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('categoria')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Precio Neto -->
                                <div>
                                    <label for="precio_neto" class="block text-sm font-medium text-gray-700 mb-2">
                                        Precio Neto (CLP) <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <input type="number" 
                                               name="precio_neto" 
                                               id="precio_neto"
                                               value="{{ old('precio_neto', $producto->precio_neto) }}"
                                               required
                                               min="0"
                                               step="1"
                                               class="block w-full pl-7 pr-3 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('precio_neto') border-red-300 @enderror">
                                    </div>
                                    @error('precio_neto')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Estado -->
                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                                    Estado <span class="text-red-500">*</span>
                                </label>
                                <select name="estado" 
                                        id="estado" 
                                        required
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('estado') border-red-300 @enderror">
                                    <option value="Activo" {{ old('estado', $producto->estado) === 'Activo' ? 'selected' : '' }}>
                                        Activo
                                    </option>
                                    <option value="Inactivo" {{ old('estado', $producto->estado) === 'Inactivo' ? 'selected' : '' }}>
                                        Inactivo
                                    </option>
                                </select>
                                @error('estado')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Archivos y Multimedia -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Archivos y Multimedia</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Imágenes Existentes -->
                            @if($producto->imagenes_array && count($producto->imagenes_array) > 0)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Imágenes Actuales
                                    </label>
                                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 mb-4">
                                        @foreach($producto->imagenes_array as $index => $imagen)
                                            <div class="relative group">
                                                <img src="{{ $imagen['url'] }}" 
                                                     alt="{{ $imagen['nombre_original'] ?? 'Imagen ' . ($index + 1) }}"
                                                     class="h-24 w-full object-cover rounded-lg">
                                                <button type="button" 
                                                        onclick="eliminarImagenExistente({{ $index }})"
                                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-700 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Nuevas Imágenes -->
                            <div>
                                <label for="imagenes" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ $producto->imagenes_array && count($producto->imagenes_array) > 0 ? 'Agregar Más Imágenes' : 'Imágenes del Producto' }}
                                </label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <i class="fas fa-images text-3xl text-gray-400"></i>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="imagenes" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                <span>Subir imágenes</span>
                                                <input id="imagenes" name="imagenes[]" type="file" class="sr-only" multiple accept="image/*">
                                            </label>
                                            <p class="pl-1">o arrastra y suelta</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF hasta 2MB c/u</p>
                                    </div>
                                </div>
                                <div id="preview-imagenes" class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 hidden">
                                    <!-- Previews dinámicos -->
                                </div>
                            </div>

                            <!-- Documentos Existentes -->
                            @if($producto->documentos && count(json_decode($producto->documentos, true) ?? []) > 0)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Documentos Actuales
                                    </label>
                                    <div class="space-y-2 mb-4">
                                        @foreach(json_decode($producto->documentos, true) ?? [] as $index => $documento)
                                            <div class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded-md">
                                                <div class="flex items-center">
                                                    <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                                    <span class="text-sm text-gray-900">{{ $documento['nombre_original'] ?? 'Documento ' . ($index + 1) }}</span>
                                                </div>
                                                <div class="flex space-x-2">
                                                    <a href="{{ $documento['url'] }}" 
                                                       target="_blank"
                                                       class="text-blue-600 hover:text-blue-800">
                                                        <i class="fas fa-download text-xs"></i>
                                                    </a>
                                                    <button type="button" 
                                                            onclick="eliminarDocumentoExistente({{ $index }})"
                                                            class="text-red-600 hover:text-red-800">
                                                        <i class="fas fa-times text-xs"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Nuevos Documentos -->
                            <div>
                                <label for="documentos" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ $producto->documentos ? 'Agregar Más Documentos' : 'Documentos Técnicos' }}
                                </label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <i class="fas fa-file-pdf text-3xl text-gray-400"></i>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="documentos" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                <span>Subir documentos</span>
                                                <input id="documentos" name="documentos[]" type="file" class="sr-only" multiple accept=".pdf,.doc,.docx">
                                            </label>
                                            <p class="pl-1">o arrastra y suelta</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PDF, DOC, DOCX hasta 5MB c/u</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Constructor Visual -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                <i class="fas fa-magic text-purple-600 mr-2"></i>
                                Constructor Visual para Cotizaciones
                                @if($producto->bloques_contenido)
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Configurado
                                    </span>
                                @endif
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Modifica cómo se mostrará este producto en las cotizaciones profesionales
                            </p>
                        </div>
                        <div class="p-6">
                            <producto-constructor-visual 
                                ref="constructorVisual"
                                :producto="{{ json_encode([
                                    'id' => $producto->id,
                                    'nombre' => $producto->nombre,
                                    'bloques_contenido' => $producto->bloques_contenido,
                                    'plantilla_base' => $producto->plantilla_base,
                                    'configuracion_visual' => $producto->configuracion_visual
                                ]) }}"
                                @actualizar="actualizarConstructorVisual">
                            </producto-constructor-visual>
                        </div>
                    </div>
                </div>

                <!-- Panel Lateral -->
                <div class="lg:col-span-1 space-y-6">
                    
                    <!-- Datos Estructurados -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Datos Estructurados</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Accesorios -->
                            <div>
                                <label for="accesorios-input" class="block text-sm font-medium text-gray-700 mb-2">
                                    Accesorios Incluidos
                                </label>
                                <div class="space-y-2">
                                    <div class="flex">
                                        <input type="text" 
                                               id="accesorios-input"
                                               placeholder="Ej: Cable de alimentación"
                                               class="flex-1 border-gray-300 rounded-l-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <button type="button" 
                                                onclick="agregarItem('accesorios')"
                                                class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 text-gray-500 hover:bg-gray-100">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <div id="lista-accesorios" class="space-y-1">
                                        <!-- Items cargados dinámicamente -->
                                    </div>
                                </div>
                                <input type="hidden" name="accesorios" id="accesorios-json">
                            </div>

                            <!-- Opcionales -->
                            <div>
                                <label for="opcionales-input" class="block text-sm font-medium text-gray-700 mb-2">
                                    Elementos Opcionales
                                </label>
                                <div class="space-y-2">
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" 
                                               id="opcionales-input"
                                               placeholder="Nombre opcional"
                                               class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <input type="number" 
                                               id="opcionales-precio"
                                               placeholder="Precio"
                                               class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    <button type="button" 
                                            onclick="agregarOpcional()"
                                            class="w-full inline-flex justify-center items-center px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500 hover:bg-gray-100">
                                        <i class="fas fa-plus mr-2"></i>
                                        Agregar Opcional
                                    </button>
                                    <div id="lista-opcionales" class="space-y-1">
                                        <!-- Items cargados dinámicamente -->
                                    </div>
                                </div>
                                <input type="hidden" name="opcionales" id="opcionales-json">
                            </div>

                            <!-- Especificaciones Técnicas -->
                            <div>
                                <label for="especificaciones-clave" class="block text-sm font-medium text-gray-700 mb-2">
                                    Especificaciones Técnicas
                                </label>
                                <div class="space-y-2">
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" 
                                               id="especificaciones-clave"
                                               placeholder="Especificación"
                                               class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <input type="text" 
                                               id="especificaciones-valor"
                                               placeholder="Valor"
                                               class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    <button type="button" 
                                            onclick="agregarEspecificacion()"
                                            class="w-full inline-flex justify-center items-center px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500 hover:bg-gray-100">
                                        <i class="fas fa-plus mr-2"></i>
                                        Agregar Especificación
                                    </button>
                                    <div id="lista-especificaciones" class="space-y-1">
                                        <!-- Items cargados dinámicamente -->
                                    </div>
                                </div>
                                <input type="hidden" name="especificaciones_tecnicas" id="especificaciones-json">
                            </div>
                        </div>
                    </div>

                    <!-- Garantías -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Información de Garantía</h3>
                        </div>
                        <div class="p-6">
                            <textarea name="garantias" 
                                      id="garantias"
                                      rows="4"
                                      placeholder="Detalles de garantía, tiempo de cobertura, condiciones..."
                                      class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('garantias', $producto->garantias) }}</textarea>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="p-6">
                            <div class="space-y-4">
                                <button type="submit" 
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-save mr-2"></i>
                                    Actualizar Producto
                                </button>
                                
                                <button type="button" 
                                        onclick="duplicarProducto()"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-copy mr-2"></i>
                                    Duplicar Producto
                                </button>

                                <a href="{{ route('productos.show', $producto) }}" 
                                   class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-times mr-2"></i>
                                    Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Campos ocultos para constructor visual -->
            <input type="hidden" name="bloques_contenido" id="bloques_contenido">
            <input type="hidden" name="plantilla_id" id="plantilla_id">
            <input type="hidden" name="configuracion_visual" id="configuracion_visual">
            
            <!-- Campos ocultos para archivos eliminados -->
            <input type="hidden" name="imagenes_eliminadas" id="imagenes_eliminadas">
            <input type="hidden" name="documentos_eliminados" id="documentos_eliminados">
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Arrays para almacenar datos estructurados
    let accesorios = @json($producto->accesorios_array ?? []);
    let opcionales = @json(json_decode($producto->opcionales, true) ?? []);
    let especificaciones = @json(json_decode($producto->especificaciones_tecnicas, true) ?? []);
    let imagenesEliminadas = [];
    let documentosEliminados = [];

    // Inicializar listas existentes
    actualizarListaAccesorios();
    actualizarListaOpcionales();
    actualizarListaEspecificaciones();

    // Manejo de imágenes nuevas
    const inputImagenes = document.getElementById('imagenes');
    const previewImagenes = document.getElementById('preview-imagenes');

    inputImagenes.addEventListener('change', function(e) {
        previewImagenes.innerHTML = '';
        const files = Array.from(e.target.files);
        
        if (files.length > 0) {
            previewImagenes.classList.remove('hidden');
            
            files.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'relative';
                        div.innerHTML = `
                            <img src="${e.target.result}" alt="Preview ${index + 1}" class="h-24 w-full object-cover rounded-lg">
                            <button type="button" onclick="eliminarImagenNueva(${index})" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                        previewImagenes.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                }
            });
        } else {
            previewImagenes.classList.add('hidden');
        }
    });

    // Funciones globales
    window.eliminarImagenExistente = function(index) {
        if (confirm('¿Estás seguro de que quieres eliminar esta imagen?')) {
            imagenesEliminadas.push(index);
            document.getElementById('imagenes_eliminadas').value = JSON.stringify(imagenesEliminadas);
            
            // Ocultar visualmente la imagen
            event.target.closest('.relative').style.display = 'none';
        }
    };

    window.eliminarDocumentoExistente = function(index) {
        if (confirm('¿Estás seguro de que quieres eliminar este documento?')) {
            documentosEliminados.push(index);
            document.getElementById('documentos_eliminados').value = JSON.stringify(documentosEliminados);
            
            // Ocultar visualmente el documento
            event.target.closest('.flex').style.display = 'none';
        }
    };

    window.agregarItem = function(tipo) {
        const input = document.getElementById(`${tipo}-input`);
        const valor = input.value.trim();
        
        if (valor) {
            if (tipo === 'accesorios') {
                accesorios.push(valor);
                actualizarListaAccesorios();
            }
            input.value = '';
        }
    };

    window.agregarOpcional = function() {
        const inputNombre = document.getElementById('opcionales-input');
        const inputPrecio = document.getElementById('opcionales-precio');
        const nombre = inputNombre.value.trim();
        const precio = parseFloat(inputPrecio.value) || 0;
        
        if (nombre) {
            opcionales.push({ nombre, precio });
            actualizarListaOpcionales();
            inputNombre.value = '';
            inputPrecio.value = '';
        }
    };

    window.agregarEspecificacion = function() {
        const inputClave = document.getElementById('especificaciones-clave');
        const inputValor = document.getElementById('especificaciones-valor');
        const clave = inputClave.value.trim();
        const valor = inputValor.value.trim();
        
        if (clave && valor) {
            especificaciones.push({ clave, valor });
            actualizarListaEspecificaciones();
            inputClave.value = '';
            inputValor.value = '';
        }
    };

    window.duplicarProducto = function() {
        if (confirm('¿Estás seguro de que quieres duplicar este producto? Se creará una copia con el nombre "Copia de [nombre actual]".')) {
            // Crear un formulario temporal para duplicar
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/api/productos/{{ $producto->id }}/duplicar`;
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }
    };

    function actualizarListaAccesorios() {
        const lista = document.getElementById('lista-accesorios');
        lista.innerHTML = accesorios.map((item, index) => `
            <div class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded-md">
                <span class="text-sm text-gray-900">${item}</span>
                <button type="button" onclick="eliminarAccesorio(${index})" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        `).join('');
        document.getElementById('accesorios-json').value = JSON.stringify(accesorios);
    }

    function actualizarListaOpcionales() {
        const lista = document.getElementById('lista-opcionales');
        lista.innerHTML = opcionales.map((item, index) => `
            <div class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded-md">
                <div>
                    <span class="text-sm text-gray-900">${item.nombre}</span>
                    <span class="text-xs text-gray-500 ml-2">$${Number(item.precio).toLocaleString()}</span>
                </div>
                <button type="button" onclick="eliminarOpcional(${index})" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        `).join('');
        document.getElementById('opcionales-json').value = JSON.stringify(opcionales);
    }

    function actualizarListaEspecificaciones() {
        const lista = document.getElementById('lista-especificaciones');
        lista.innerHTML = especificaciones.map((item, index) => `
            <div class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded-md">
                <div>
                    <span class="text-sm font-medium text-gray-900">${item.clave}:</span>
                    <span class="text-sm text-gray-700 ml-1">${item.valor}</span>
                </div>
                <button type="button" onclick="eliminarEspecificacion(${index})" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        `).join('');
        document.getElementById('especificaciones-json').value = JSON.stringify(especificaciones);
    }

    // Funciones para eliminar items
    window.eliminarAccesorio = function(index) {
        accesorios.splice(index, 1);
        actualizarListaAccesorios();
    };

    window.eliminarOpcional = function(index) {
        opcionales.splice(index, 1);
        actualizarListaOpcionales();
    };

    window.eliminarEspecificacion = function(index) {
        especificaciones.splice(index, 1);
        actualizarListaEspecificaciones();
    };

    // Función para actualizar datos del constructor visual
    window.actualizarConstructorVisual = function(data) {
        document.getElementById('bloques_contenido').value = data.bloques_contenido || '';
        document.getElementById('plantilla_id').value = data.plantilla_base || '';
        document.getElementById('configuracion_visual').value = JSON.stringify(data.configuracion_visual || {});
    };

    // Validación del formulario
    document.getElementById('form-producto').addEventListener('submit', function(e) {
        const nombre = document.getElementById('nombre').value.trim();
        const categoria = document.getElementById('categoria').value;
        const precio = document.getElementById('precio_neto').value;
        
        if (!nombre || !categoria || !precio) {
            e.preventDefault();
            alert('Por favor completa todos los campos obligatorios.');
            return false;
        }
        
        if (parseFloat(precio) <= 0) {
            e.preventDefault();
            alert('El precio debe ser mayor a 0.');
            return false;
        }
    });
});
</script>
@endsection

@section('styles')
<style>
/* Estilos similares a create.blade.php */
.file-drop-zone {
    transition: all 0.3s ease;
}

.file-drop-zone:hover {
    border-color: #3b82f6;
    background-color: #eff6ff;
}

.preview-image {
    transition: transform 0.2s ease;
}

.preview-image:hover {
    transform: scale(1.05);
}

.lista-item {
    animation: fadeInUp 0.3s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.constructor-container {
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    min-height: 400px;
}

.constructor-container.active {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Estilos para elementos eliminados */
.elemento-eliminado {
    opacity: 0.5;
    pointer-events: none;
    position: relative;
}

.elemento-eliminado::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 2px;
    background-color: #ef4444;
    transform: translateY(-50%);
}
</style>
@endsection