@extends('layouts.app')

@section('title', 'Editar Cliente: ' . $cliente->nombre_institucion)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-edit mr-3 text-blue-600"></i>
                        Editar Cliente
                    </h1>
                    <p class="text-gray-600 mt-1">Modificar información de: <strong>{{ $cliente->nombre_institucion }}</strong></p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('clientes.show', $cliente->id) }}" class="btn-secondary">
                        <i class="fas fa-eye mr-2"></i>
                        Ver Cliente
                    </a>
                    <a href="{{ route('clientes.index') }}" class="btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver a Lista
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Información del Cliente -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <div class="h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-lg font-bold text-blue-700">
                        {{ strtoupper(substr($cliente->nombre_institucion, 0, 2)) }}
                    </span>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-blue-900">{{ $cliente->nombre_institucion }}</h3>
                    <div class="flex items-center space-x-4 text-sm text-blue-700">
                        @if($cliente->rut)
                            <span><i class="fas fa-id-card mr-1"></i>{{ $cliente->rut }}</span>
                        @endif
                        <span class="px-2 py-1 bg-blue-200 rounded-full text-xs">{{ $cliente->tipo_cliente }}</span>
                        <span><i class="fas fa-calendar mr-1"></i>Registrado: {{ $cliente->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario de Edición -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <form action="{{ route('clientes.update', $cliente->id) }}" method="POST" id="form-editar-cliente">
                @csrf
                @method('PUT')
                
                <!-- Tabs de Navegación -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                        <button type="button" 
                                onclick="mostrarTab('informacion')" 
                                class="tab-button active" 
                                id="tab-informacion">
                            <i class="fas fa-building mr-2"></i>
                            Información General
                        </button>
                        <button type="button" 
                                onclick="mostrarTab('contacto')" 
                                class="tab-button" 
                                id="tab-contacto">
                            <i class="fas fa-user mr-2"></i>
                            Contacto Principal
                        </button>
                        <button type="button" 
                                onclick="mostrarTab('configuracion')" 
                                class="tab-button" 
                                id="tab-configuracion">
                            <i class="fas fa-cogs mr-2"></i>
                            Configuración
                        </button>
                        <button type="button" 
                                onclick="mostrarTab('historial')" 
                                class="tab-button" 
                                id="tab-historial">
                            <i class="fas fa-history mr-2"></i>
                            Historial
                        </button>
                    </nav>
                </div>

                <div class="px-6 py-6">
                    
                    <!-- Tab: Información General -->
                    <div class="tab-content active" id="content-informacion">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Información de la Institución</h3>
                            <p class="text-sm text-gray-600">Datos básicos de la empresa o institución cliente</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre de Institución -->
                            <div class="md:col-span-2">
                                <label for="nombre_institucion" class="form-label required">
                                    Nombre de la Institución
                                </label>
                                <input
                                    type="text"
                                    name="nombre_institucion"
                                    id="nombre_institucion"
                                    value="{{ old('nombre_institucion', $cliente->nombre_institucion) }}"
                                    class="input-field @error('nombre_institucion') border-red-500 @enderror"
                                    required
                                    maxlength="255"
                                >
                                @error('nombre_institucion')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- RUT -->
                            <div>
                                <label for="rut" class="form-label">
                                    RUT de la Institución
                                </label>
                                <input
                                    type="text"
                                    name="rut"
                                    id="rut"
                                    value="{{ old('rut', $cliente->rut) }}"
                                    class="input-field @error('rut') border-red-500 @enderror"
                                    maxlength="12"
                                    onblur="validarRUT(this)"
                                >
                                @error('rut')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tipo de Cliente -->
                            <div>
                                <label for="tipo_cliente" class="form-label required">
                                    Tipo de Cliente
                                </label>
                                <select
                                    name="tipo_cliente"
                                    id="tipo_cliente"
                                    class="input-field @error('tipo_cliente') border-red-500 @enderror"
                                    required
                                    onchange="mostrarInfoTipoCliente()"
                                >
                                    <option value="">Seleccionar tipo...</option>
                                    <option value="Cliente Público" {{ old('tipo_cliente', $cliente->tipo_cliente) === 'Cliente Público' ? 'selected' : '' }}>
                                        Cliente Público
                                    </option>
                                    <option value="Cliente Privado" {{ old('tipo_cliente', $cliente->tipo_cliente) === 'Cliente Privado' ? 'selected' : '' }}>
                                        Cliente Privado
                                    </option>
                                    <option value="Revendedor" {{ old('tipo_cliente', $cliente->tipo_cliente) === 'Revendedor' ? 'selected' : '' }}>
                                        Revendedor
                                    </option>
                                </select>
                                @error('tipo_cliente')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                                
                                <!-- Información del Tipo -->
                                <div id="info-tipo-cliente" class="hidden mt-2 p-3 rounded-md text-sm">
                                    <!-- Se llenará con JavaScript -->
                                </div>
                            </div>

                            <!-- Dirección -->
                            <div class="md:col-span-2">
                                <label for="direccion" class="form-label">
                                    Dirección
                                </label>
                                <input
                                    type="text"
                                    name="direccion"
                                    id="direccion"
                                    value="{{ old('direccion', $cliente->direccion) }}"
                                    class="input-field @error('direccion') border-red-500 @enderror"
                                    maxlength="255"
                                >
                                @error('direccion')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Contacto Principal -->
                    <div class="tab-content hidden" id="content-contacto">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Contacto Principal</h3>
                            <p class="text-sm text-gray-600">Persona de contacto principal en la institución</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre del Contacto -->
                            <div>
                                <label for="nombre_contacto" class="form-label">
                                    Nombre del Contacto
                                </label>
                                <input
                                    type="text"
                                    name="nombre_contacto"
                                    id="nombre_contacto"
                                    value="{{ old('nombre_contacto', $cliente->nombre_contacto) }}"
                                    class="input-field @error('nombre_contacto') border-red-500 @enderror"
                                    maxlength="255"
                                >
                                @error('nombre_contacto')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="form-label">
                                    Email de Contacto
                                </label>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    value="{{ old('email', $cliente->email) }}"
                                    class="input-field @error('email') border-red-500 @enderror"
                                    maxlength="255"
                                >
                                @error('email')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Teléfono -->
                            <div>
                                <label for="telefono" class="form-label">
                                    Teléfono de Contacto
                                </label>
                                <input
                                    type="tel"
                                    name="telefono"
                                    id="telefono"
                                    value="{{ old('telefono', $cliente->telefono) }}"
                                    class="input-field @error('telefono') border-red-500 @enderror"
                                    maxlength="20"
                                >
                                @error('telefono')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Botones de Acción Rápida -->
                            <div class="flex space-x-2">
                                <button type="button" onclick="verContactosAdicionales()" class="btn-secondary flex-1">
                                    <i class="fas fa-users mr-2"></i>
                                    Ver Contactos Adicionales
                                </button>
                                <button type="button" onclick="agregarContacto()" class="btn-primary flex-1">
                                    <i class="fas fa-user-plus mr-2"></i>
                                    Agregar Contacto
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Configuración -->
                    <div class="tab-content hidden" id="content-configuracion">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Configuración y Asignaciones</h3>
                            <p class="text-sm text-gray-600">Vendedores asignados y configuraciones especiales</p>
                        </div>

                        <div class="space-y-6">
                            <!-- Vendedores Asignados -->
                            <div>
                                <label class="form-label">Vendedores Asignados</label>
                                <div class="space-y-2">
                                    @php
                                        $vendedoresAsignados = is_string($cliente->vendedores_a_cargo) ? 
                                            json_decode($cliente->vendedores_a_cargo, true) : 
                                            ($cliente->vendedores_a_cargo ?? []);
                                        if (!is_array($vendedoresAsignados)) {
                                            $vendedoresAsignados = [];
                                        }
                                    @endphp
                                    
                                    @foreach($vendedores ?? [] as $vendedor)
                                        <label class="flex items-center">
                                            <input
                                                type="checkbox"
                                                name="vendedores_a_cargo[]"
                                                value="{{ $vendedor->id }}"
                                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                {{ in_array($vendedor->id, array_merge($vendedoresAsignados, old('vendedores_a_cargo', []))) ? 'checked' : '' }}
                                            >
                                            <span class="ml-2 text-sm text-gray-700">{{ $vendedor->name }}</span>
                                            @if(in_array($vendedor->id, $vendedoresAsignados))
                                                <span class="ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">Actual</span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                                @error('vendedores_a_cargo')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Información Adicional -->
                            <div>
                                <label for="informacion_adicional" class="form-label">
                                    Información Adicional
                                </label>
                                <textarea
                                    name="informacion_adicional"
                                    id="informacion_adicional"
                                    rows="5"
                                    class="input-field @error('informacion_adicional') border-red-500 @enderror"
                                    placeholder="Notas, observaciones especiales, historial previo, etc."
                                >{{ old('informacion_adicional', $cliente->informacion_adicional) }}</textarea>
                                @error('informacion_adicional')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Estadísticas de Actividad -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-3">Estadísticas de Actividad</h4>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-blue-600">{{ $estadisticas['cotizaciones'] ?? 0 }}</div>
                                        <div class="text-sm text-gray-600">Cotizaciones</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-green-600">{{ $estadisticas['seguimientos'] ?? 0 }}</div>
                                        <div class="text-sm text-gray-600">Seguimientos</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-purple-600">{{ $estadisticas['contactos'] ?? 0 }}</div>
                                        <div class="text-sm text-gray-600">Contactos</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-orange-600">{{ $estadisticas['ultima_actividad'] ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-600">Última Actividad</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Historial -->
                    <div class="tab-content hidden" id="content-historial">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Historial de Cambios</h3>
                            <p class="text-sm text-gray-600">Registro de modificaciones y actividades del cliente</p>
                        </div>

                        <div class="space-y-6">
                            <!-- Timeline de Actividades -->
                            <div class="flow-root">
                                <ul role="list" class="-mb-8">
                                    <!-- Registro de Creación -->
                                    <li>
                                        <div class="relative pb-8">
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                        <i class="fas fa-plus text-white text-sm"></i>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">
                                                            Cliente <strong>creado</strong> en el sistema
                                                        </p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        {{ $cliente->created_at->format('d/m/Y H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    <!-- Última Modificación -->
                                    @if($cliente->updated_at && $cliente->updated_at != $cliente->created_at)
                                    <li>
                                        <div class="relative pb-8">
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                        <i class="fas fa-edit text-white text-sm"></i>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">
                                                            Información del cliente <strong>actualizada</strong>
                                                        </p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        {{ $cliente->updated_at->format('d/m/Y H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    @endif

                                    <!-- Actividades Recientes -->
                                    @if(isset($actividadesRecientes))
                                        @foreach($actividadesRecientes as $actividad)
                                        <li>
                                            <div class="relative pb-8">
                                                @if(!$loop->last)
                                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                @endif
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full {{ $actividad['color'] ?? 'bg-gray-500' }} flex items-center justify-center ring-8 ring-white">
                                                            <i class="{{ $actividad['icon'] ?? 'fas fa-circle' }} text-white text-sm"></i>
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                        <div>
                                                            <p class="text-sm text-gray-500">{{ $actividad['descripcion'] }}</p>
                                                            @if(isset($actividad['detalles']))
                                                                <p class="text-xs text-gray-400 mt-1">{{ $actividad['detalles'] }}</p>
                                                            @endif
                                                        </div>
                                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                            {{ $actividad['fecha'] }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        @endforeach
                                    @endif

                                    <!-- Placeholder si no hay actividades -->
                                    @if(!isset($actividadesRecientes) || count($actividadesRecientes) === 0)
                                    <li>
                                        <div class="relative">
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                                        <i class="fas fa-clock text-white text-sm"></i>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5">
                                                    <p class="text-sm text-gray-500">
                                                        No hay actividades recientes registradas
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    @endif
                                </ul>
                            </div>

                            <!-- Acciones Rápidas desde Historial -->
                            <div class="border-t border-gray-200 pt-6">
                                <h4 class="text-md font-medium text-gray-900 mb-4">Acciones Relacionadas</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <a href="/seguimiento?cliente={{ $cliente->id }}" class="btn-secondary text-center">
                                        <i class="fas fa-chart-line mr-2"></i>
                                        Ver Seguimientos
                                    </a>
                                    <a href="/cotizaciones?cliente={{ $cliente->id }}" class="btn-secondary text-center">
                                        <i class="fas fa-file-invoice mr-2"></i>
                                        Ver Cotizaciones
                                    </a>
                                    <button type="button" onclick="generarReporte()" class="btn-secondary">
                                        <i class="fas fa-download mr-2"></i>
                                        Generar Reporte
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Botones de Acción -->
                <div class="bg-gray-50 px-6 py-4 flex justify-between items-center">
                    <div class="flex space-x-2">
                        <button type="button" onclick="confirmarEliminacion()" class="btn-danger">
                            <i class="fas fa-trash mr-2"></i>
                            Eliminar Cliente
                        </button>
                        <button type="button" onclick="duplicarCliente()" class="btn-secondary">
                            <i class="fas fa-copy mr-2"></i>
                            Duplicar
                        </button>
                    </div>
                    <div class="flex space-x-3">
                        <button type="button" onclick="guardarBorrador()" class="btn-secondary">
                            <i class="fas fa-save mr-2"></i>
                            Guardar Borrador
                        </button>
                        <button type="submit" class="btn-primary" id="btn-actualizar">
                            <i class="fas fa-check mr-2"></i>
                            Actualizar Cliente
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div id="modal-eliminacion" class="modal-overlay hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="text-lg font-medium text-gray-900">
                Confirmar Eliminación
            </h3>
            <button onclick="cerrarModalEliminacion()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="flex items-center mb-4">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
            </div>
            <div class="text-center">
                <p class="text-sm text-gray-500 mb-2">
                    ¿Estás seguro de que deseas eliminar este cliente?
                </p>
                <p class="text-lg font-medium text-gray-900">
                    {{ $cliente->nombre_institucion }}
                </p>
                <p class="text-sm text-red-600 mt-2">
                    Esta acción no se puede deshacer y se eliminarán todos los datos relacionados.
                </p>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="cerrarModalEliminacion()" class="btn-secondary">
                Cancelar
            </button>
            <button onclick="eliminarCliente()" class="btn-danger">
                <i class="fas fa-trash mr-2"></i>
                Eliminar Cliente
            </button>
        </div>
    </div>
</div>

<!-- Modal de Contactos Adicionales -->
<div id="modal-contactos" class="modal-overlay hidden">
    <div class="modal-content max-w-4xl">
        <div class="modal-header">
            <h3 class="text-lg font-medium text-gray-900">
                Contactos Adicionales
            </h3>
            <button onclick="cerrarModalContactos()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="lista-contactos">
                <!-- Se cargará dinámicamente -->
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">Cargando contactos...</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="cerrarModalContactos()" class="btn-secondary">
                Cerrar
            </button>
            <button onclick="agregarContacto()" class="btn-primary">
                <i class="fas fa-user-plus mr-2"></i>
                Agregar Nuevo Contacto
            </button>
        </div>
    </div>
</div>

<!-- Toast de Notificaciones -->
<div id="toast" class="toast hidden">
    <div class="flex items-center">
        <i id="toast-icon" class="mr-2"></i>
        <span id="toast-message"></span>
    </div>
    <button onclick="cerrarToast()" class="ml-4 text-white">
        <i class="fas fa-times"></i>
    </button>
</div>

@endsection

@section('scripts')
<script>
// Variables globales
const clienteId = {{ $cliente->id }};
let tabActual = 'informacion';

// Información de tipos de cliente (reutilizada del create)
const infoTiposCliente = {
    'Cliente Público': {
        color: 'bg-purple-50 border-purple-200 text-purple-800',
        icono: 'fas fa-university',
        descripcion: 'Instituciones del sector público como hospitales públicos, consultorios, organismos estatales.',
        caracteristicas: ['Procesos de compra más largos', 'Requiere licitaciones', 'Facturación centralizada']
    },
    'Cliente Privado': {
        color: 'bg-green-50 border-green-200 text-green-800',
        icono: 'fas fa-building',
        descripcion: 'Clínicas privadas, centros médicos particulares, consultas privadas.',
        caracteristicas: ['Decisiones más rápidas', 'Presupuestos flexibles', 'Relación directa']
    },
    'Revendedor': {
        color: 'bg-orange-50 border-orange-200 text-orange-800',
        icono: 'fas fa-handshake',
        descripcion: 'Distribuidores, representantes comerciales que revenden nuestros productos.',
        caracteristicas: ['Descuentos especiales', 'Volúmenes altos', 'Soporte técnico específico']
    }
};

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar información del tipo de cliente actual
    mostrarInfoTipoCliente();
    
    // Configurar formateo automático
    document.getElementById('rut').addEventListener('input', function(e) {
        formatearRUT(e.target);
    });
    
    document.getElementById('telefono').addEventListener('input', function(e) {
        formatearTelefono(e.target);
    });
    
    // Validación en tiempo real
    configurarValidacionTiempoReal();
});

// Funciones de navegación por tabs
function mostrarTab(nombreTab) {
    // Ocultar todos los contenidos
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
        content.classList.remove('active');
    });
    
    // Desactivar todos los botones de tab
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });
    
    // Mostrar contenido actual
    const contenido = document.getElementById(`content-${nombreTab}`);
    if (contenido) {
        contenido.classList.remove('hidden');
        contenido.classList.add('active');
    }
    
    // Activar botón actual
    const boton = document.getElementById(`tab-${nombreTab}`);
    if (boton) {
        boton.classList.add('active');
    }
    
    tabActual = nombreTab;
}

// Funciones de formateo (reutilizadas del create)
function formatearRUT(input) {
    let value = input.value.replace(/[^0-9kK]/g, '');
    
    if (value.length > 1) {
        let rut = value.slice(0, -1);
        let dv = value.slice(-1);
        rut = rut.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        input.value = rut + '-' + dv;
    } else {
        input.value = value;
    }
}

function validarRUT(input) {
    const rut = input.value.trim();
    
    if (rut && !validarFormatoRUT(rut)) {
        mostrarToast('El formato del RUT no es válido', 'warning');
        input.classList.add('border-yellow-500');
        return false;
    } else {
        input.classList.remove('border-yellow-500');
        return true;
    }
}

function validarFormatoRUT(rut) {
    const rutRegex = /^\d{1,2}\.\d{3}\.\d{3}-[\dkK]$/;
    return rutRegex.test(rut);
}

function formatearTelefono(input) {
    let value = input.value.replace(/[^0-9\+]/g, '');
    
    if (value.startsWith('569') && value.length === 11) {
        value = '+56 9 ' + value.slice(3, 7) + ' ' + value.slice(7);
    } else if (value.startsWith('9') && value.length === 9) {
        value = '+56 9 ' + value.slice(1, 5) + ' ' + value.slice(5);
    }
    
    input.value = value;
}

function mostrarInfoTipoCliente() {
    const tipoCliente = document.getElementById('tipo_cliente').value;
    const infoDiv = document.getElementById('info-tipo-cliente');
    
    if (tipoCliente && infoTiposCliente[tipoCliente]) {
        const info = infoTiposCliente[tipoCliente];
        
        infoDiv.className = `mt-2 p-3 rounded-md text-sm border ${info.color}`;
        infoDiv.innerHTML = `
            <div class="flex items-start">
                <i class="${info.icono} mr-2 mt-0.5"></i>
                <div>
                    <p class="font-medium mb-2">${info.descripcion}</p>
                    <ul class="text-xs space-y-1">
                        ${info.caracteristicas.map(c => `<li>• ${c}</li>`).join('')}
                    </ul>
                </div>
            </div>
        `;
        infoDiv.classList.remove('hidden');
    } else {
        infoDiv.classList.add('hidden');
    }
}

function configurarValidacionTiempoReal() {
    // Validar campos requeridos
    document.getElementById('nombre_institucion').addEventListener('blur', function() {
        if (!this.value.trim()) {
            this.classList.add('border-red-500');
        } else {
            this.classList.remove('border-red-500');
        }
    });
    
    // Validar email
    document.getElementById('email').addEventListener('blur', function() {
        const email = this.value.trim();
        if (email && !validarEmail(email)) {
            this.classList.add('border-red-500');
        } else {
            this.classList.remove('border-red-500');
        }
    });
}

function validarEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Funciones de modal
function confirmarEliminacion() {
    document.getElementById('modal-eliminacion').classList.remove('hidden');
}

function cerrarModalEliminacion() {
    document.getElementById('modal-eliminacion').classList.add('hidden');
}

function eliminarCliente() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/clientes/${clienteId}`;
    
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'DELETE';
    form.appendChild(methodField);
    
    const tokenField = document.createElement('input');
    tokenField.type = 'hidden';
    tokenField.name = '_token';
    tokenField.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(tokenField);
    
    document.body.appendChild(form);
    form.submit();
}

async function verContactosAdicionales() {
    document.getElementById('modal-contactos').classList.remove('hidden');
    
    try {
        const response = await fetch(`/crm-bioscom/public/api/contactos/cliente/${clienteId}`);
        const data = await response.json();
        
        const listaContactos = document.getElementById('lista-contactos');
        
        if (data.success && data.data.length > 0) {
            listaContactos.innerHTML = `
                <div class="space-y-3">
                    ${data.data.map(contacto => `
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                            <div class="flex items-center">
                                <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-blue-700">
                                        ${contacto.nombre.substring(0, 2).toUpperCase()}
                                    </span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">${contacto.nombre}</p>
                                    <p class="text-sm text-gray-500">${contacto.cargo || 'Sin cargo'}</p>
                                    ${contacto.email ? `<p class="text-xs text-blue-600">${contacto.email}</p>` : ''}
                                    ${contacto.telefono ? `<p class="text-xs text-green-600">${contacto.telefono}</p>` : ''}
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="editarContacto(${contacto.id})" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="eliminarContacto(${contacto.id})" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        } else {
            listaContactos.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay contactos adicionales</h3>
                    <p class="text-gray-500 mb-4">Este cliente no tiene contactos adicionales registrados.</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error al cargar contactos:', error);
        mostrarToast('Error al cargar contactos', 'error');
    }
}

function cerrarModalContactos() {
    document.getElementById('modal-contactos').classList.add('hidden');
}

function agregarContacto() {
    // Redirigir a formulario de creación de contacto
    window.location.href = `/contactos/create?cliente=${clienteId}`;
}

function editarContacto(contactoId) {
    window.location.href = `/contactos/${contactoId}/edit`;
}

async function eliminarContacto(contactoId) {
    if (confirm('¿Estás seguro de que deseas eliminar este contacto?')) {
        try {
            const response = await fetch(`/crm-bioscom/public/api/contactos/${contactoId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                mostrarToast('Contacto eliminado exitosamente', 'success');
                verContactosAdicionales(); // Recargar lista
            } else {
                mostrarToast('Error al eliminar contacto', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarToast('Error al eliminar contacto', 'error');
        }
    }
}

// Otras funciones
function duplicarCliente() {
    mostrarToast('Función de duplicar en desarrollo', 'info');
}

function guardarBorrador() {
    mostrarToast('Borrador guardado localmente', 'info');
}

function generarReporte() {
    window.open(`/clientes/${clienteId}/reporte`, '_blank');
}

// Envío del formulario
document.getElementById('form-editar-cliente').addEventListener('submit', function(e) {
    const btnActualizar = document.getElementById('btn-actualizar');
    btnActualizar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Actualizando...';
    btnActualizar.disabled = true;
});

// Sistema de notificaciones Toast
function mostrarToast(mensaje, tipo = 'info') {
    const toast = document.getElementById('toast');
    const icon = document.getElementById('toast-icon');
    const messageElement = document.getElementById('toast-message');
    
    toast.className = 'toast fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 flex items-center text-white transition-all duration-300';
    
    switch(tipo) {
        case 'success':
            toast.classList.add('bg-green-500');
            icon.className = 'fas fa-check-circle mr-2';
            break;
        case 'error':
            toast.classList.add('bg-red-500');
            icon.className = 'fas fa-exclamation-circle mr-2';
            break;
        case 'warning':
            toast.classList.add('bg-yellow-500');
            icon.className = 'fas fa-exclamation-triangle mr-2';
            break;
        default:
            toast.classList.add('bg-blue-500');
            icon.className = 'fas fa-info-circle mr-2';
    }
    
    messageElement.textContent = mensaje;
    toast.classList.remove('hidden');
    
    setTimeout(() => {
        cerrarToast();
    }, 5000);
}

function cerrarToast() {
    const toast = document.getElementById('toast');
    toast.classList.add('hidden');
}
</script>
@endsection