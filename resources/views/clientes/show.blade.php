</div>

    <!-- Contenido Principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Métricas de Resumen -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="stat-card bg-blue-50 border-blue-200">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-blue-800">Cotizaciones</p>
                        <p class="text-2xl font-bold text-blue-900">{{ $estadisticas['cotizaciones'] ?? 0 }}</p>
                        <p class="text-xs text-blue-600">{{ $estadisticas['cotizaciones_activas'] ?? 0 }} activas</p>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-green-50 border-green-200">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="fas fa-chart-line text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-green-800">Seguimientos</p>
                        <p class="text-2xl font-bold text-green-900">{{ $estadisticas['seguimientos'] ?? 0 }}</p>
                        <p class="text-xs text-green-600">{{ $estadisticas['seguimientos_pendientes'] ?? 0 }} pendientes</p>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-purple-50 border-purple-200">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="fas fa-users text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-purple-800">Contactos</p>
                        <p class="text-2xl font-bold text-purple-900">{{ $estadisticas['contactos'] ?? 1 }}</p>
                        <p class="text-xs text-purple-600">{{ $estadisticas['contactos_completos'] ?? 0 }} completos</p>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-orange-50 border-orange-200">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-100 rounded-lg">
                        <i class="fas fa-clock text-orange-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-orange-800">Última Actividad</p>
                        <p class="text-lg font-bold text-orange-900">
                            {{ $estadisticas['ultima_actividad'] ?? 'N/A' }}
                        </p>
                        <p class="text-xs text-orange-600">{{ $estadisticas['dias_inactividad'] ?? 0 }} días</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Layout de 2 Columnas -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Columna Principal -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Información del Cliente -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                            Información del Cliente
                        </h3>
                    </div>
                    <div class="px-6 py-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nombre de la Institución</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $cliente->nombre_institucion }}</dd>
                            </div>
                            
                            @if($cliente->rut)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">RUT</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $cliente->rut }}</dd>
                            </div>
                            @endif
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tipo de Cliente</dt>
                                <dd class="mt-1">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                        {{ $cliente->tipo_cliente === 'Cliente Público' ? 'bg-purple-100 text-purple-800' : 
                                           ($cliente->tipo_cliente === 'Cliente Privado' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800') }}">
                                        {{ $cliente->tipo_cliente }}
                                    </span>
                                </dd>
                            </div>
                            
                            @if($cliente->direccion)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Dirección</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>
                                    {{ $cliente->direccion }}
                                </dd>
                            </div>
                            @endif
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Fecha de Registro</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $cliente->created_at->format('d/m/Y H:i') }}
                                    <span class="text-gray-500">({{ $cliente->created_at->diffForHumans() }})</span>
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Última Actualización</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $cliente->updated_at->format('d/m/Y H:i') }}
                                    <span class="text-gray-500">({{ $cliente->updated_at->diffForHumans() }})</span>
                                </dd>
                            </div>
                        </dl>
                        
                        @if($cliente->informacion_adicional)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <dt class="text-sm font-medium text-gray-500 mb-2">Información Adicional</dt>
                            <dd class="text-sm text-gray-900 bg-gray-50 rounded-lg p-4">
                                {{ $cliente->informacion_adicional }}
                            </dd>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Contacto Principal -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                <i class="fas fa-user mr-2 text-blue-600"></i>
                                Contacto Principal
                            </h3>
                            <button onclick="editarContactoPrincipal()" class="btn-secondary text-sm">
                                <i class="fas fa-edit mr-1"></i>
                                Editar
                            </button>
                        </div>
                    </div>
                    <div class="px-6 py-6">
                        @if($cliente->nombre_contacto || $cliente->email || $cliente->telefono)
                            <div class="flex items-center">
                                <div class="h-12 w-12 bg-gray-100 rounded-full flex items-center justify-center">
                                    @if($cliente->nombre_contacto)
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ strtoupper(substr($cliente->nombre_contacto, 0, 2)) }}
                                        </span>
                                    @else
                                        <i class="fas fa-user text-gray-400"></i>
                                    @endif
                                </div>
                                <div class="ml-4 flex-1">
                                    <h4 class="text-lg font-medium text-gray-900">
                                        {{ $cliente->nombre_contacto ?: 'Sin nombre de contacto' }}
                                    </h4>
                                    <div class="mt-1 space-y-1">
                                        @if($cliente->email)
                                            <div class="flex items-center text-sm text-gray-600">
                                                <i class="fas fa-envelope mr-2 text-gray-400"></i>
                                                <a href="mailto:{{ $cliente->email }}" class="text-blue-600 hover:text-blue-900">
                                                    {{ $cliente->email }}
                                                </a>
                                            </div>
                                        @endif
                                        @if($cliente->telefono)
                                            <div class="flex items-center text-sm text-gray-600">
                                                <i class="fas fa-phone mr-2 text-gray-400"></i>
                                                <a href="tel:{{ $cliente->telefono }}" class="text-blue-600 hover:text-blue-900">
                                                    {{ $cliente->telefono }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    @if($cliente->email)
                                        <a href="mailto:{{ $cliente->email }}" class="btn-secondary text-sm">
                                            <i class="fas fa-envelope mr-1"></i>
                                            Email
                                        </a>
                                    @endif
                                    @if($cliente->telefono)
                                        <a href="tel:{{ $cliente->telefono }}" class="btn-secondary text-sm">
                                            <i class="fas fa-phone mr-1"></i>
                                            Llamar
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <i class="fas fa-user-slash text-4xl text-gray-300 mb-4"></i>
                                <h4 class="text-lg font-medium text-gray-900 mb-2">Sin información de contacto</h4>
                                <p class="text-gray-500 mb-4">No hay información del contacto principal registrada.</p>
                                <button onclick="editarContactoPrincipal()" class="btn-primary">
                                    <i class="fas fa-user-plus mr-2"></i>
                                    Agregar Contacto Principal
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Vendedores Asignados -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                <i class="fas fa-user-tie mr-2 text-blue-600"></i>
                                Vendedores Asignados
                            </h3>
                            <button onclick="gestionarVendedores()" class="btn-secondary text-sm">
                                <i class="fas fa-cog mr-1"></i>
                                Gestionar
                            </button>
                        </div>
                    </div>
                    <div class="px-6 py-6">
                        @if($cliente->vendedores_a_cargo)
                            @php
                                $vendedoresIds = is_string($cliente->vendedores_a_cargo) ? 
                                    json_decode($cliente->vendedores_a_cargo, true) : 
                                    $cliente->vendedores_a_cargo;
                                if (!is_array($vendedoresIds)) {
                                    $vendedoresIds = [];
                                }
                            @endphp
                            
                            @if(count($vendedoresIds) > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($vendedoresIds as $vendedorId)
                                        @php
                                            $vendedor = ($vendedores ?? collect())->firstWhere('id', $vendedorId);
                                        @endphp
                                        @if($vendedor)
                                            <div class="flex items-center p-3 border border-gray-200 rounded-lg">
                                                <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <span class="text-sm font-medium text-blue-700">
                                                        {{ strtoupper(substr($vendedor->name, 0, 2)) }}
                                                    </span>
                                                </div>
                                                <div class="ml-3 flex-1">
                                                    <p class="text-sm font-medium text-gray-900">{{ $vendedor->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $vendedor->email ?? 'Sin email' }}</p>
                                                </div>
                                                <div class="flex space-x-1">
                                                    <button onclick="contactarVendedor('{{ $vendedor->email }}')" 
                                                            class="text-blue-600 hover:text-blue-900"
                                                            title="Contactar">
                                                        <i class="fas fa-envelope text-sm"></i>
                                                    </button>
                                                    <button onclick="verSeguimientosVendedor({{ $vendedor->id }})" 
                                                            class="text-green-600 hover:text-green-900"
                                                            title="Ver seguimientos">
                                                        <i class="fas fa-chart-line text-sm"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-6">
                                    <i class="fas fa-user-slash text-3xl text-gray-300 mb-3"></i>
                                    <p class="text-gray-500">No hay vendedores asignados</p>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-6">
                                <i class="fas fa-user-slash text-3xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500 mb-3">No hay vendedores asignados a este cliente</p>
                                <button onclick="gestionarVendedores()" class="btn-primary">
                                    <i class="fas fa-user-plus mr-2"></i>
                                    Asignar Vendedor
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Columna Lateral -->
            <div class="space-y-8">
                
                <!-- Acciones Rápidas -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <i class="fas fa-bolt mr-2 text-blue-600"></i>
                            Acciones Rápidas
                        </h3>
                    </div>
                    <div class="px-6 py-6 space-y-3">
                        <a href="/cotizaciones/create?cliente={{ $cliente->id }}" class="btn-primary w-full">
                            <i class="fas fa-file-invoice mr-2"></i>
                            Nueva Cotización
                        </a>
                        <a href="/seguimiento/create?cliente={{ $cliente->id }}" class="btn-secondary w-full">
                            <i class="fas fa-chart-line mr-2"></i>
                            Nuevo Seguimiento
                        </a>
                        <button onclick="programarTarea()" class="btn-secondary w-full">
                            <i class="fas fa-calendar-plus mr-2"></i>
                            Programar Tarea
                        </button>
                        <button onclick="generarReporte()" class="btn-secondary w-full">
                            <i class="fas fa-file-pdf mr-2"></i>
                            Generar Reporte
                        </button>
                    </div>
                </div>

                <!-- Contactos Adicionales -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                <i class="fas fa-users mr-2 text-blue-600"></i>
                                Contactos Adicionales
                            </h3>
                            <button onclick="agregarContacto()" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="px-6 py-6" id="lista-contactos-adicionales">
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin text-gray-400"></i>
                            <p class="text-sm text-gray-500 mt-2">Cargando contactos...</p>
                        </div>
                    </div>
                </div>

                <!-- Actividad Reciente -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <i class="fas fa-history mr-2 text-blue-600"></i>
                            Actividad Reciente
                        </h3>
                    </div>
                    <div class="px-6 py-6">
                        <div class="flow-root">
                            <ul role="list" class="-mb-8">
                                @if(isset($actividadReciente) && count($actividadReciente) > 0)
                                    @foreach($actividadReciente as $actividad)
                                        <li>
                                            <div class="relative pb-8">
                                                @if(!$loop->last)
                                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                @endif
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full {{ $actividad['color'] ?? 'bg-gray-500' }} flex items-center justify-center ring-8 ring-white">
                                                            <i class="{{ $actividad['icon'] ?? 'fas fa-circle' }} text-white text-xs"></i>
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-1.5">
                                                        <p class="text-sm text-gray-500">
                                                            {{ $actividad['descripcion'] }}
                                                        </p>
                                                        <p class="text-xs text-gray-400 mt-1">
                                                            {{ $actividad['fecha'] }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                @else
                                    <li class="text-center py-6">
                                        <i class="fas fa-clock text-3xl text-gray-300 mb-3"></i>
                                        <p class="text-gray-500">No hay actividad reciente</p>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Tabs de Información Detallada -->
        <div class="mt-8 bg-white rounded-lg shadow-sm overflow-hidden">
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                    <button onclick="mostrarTab('cotizaciones')" class="tab-button active" id="tab-cotizaciones">
                        <i class="fas fa-file-invoice mr-2"></i>
                        Cotizaciones
                        <span class="ml-2 py-0.5 px-2 text-xs bg-blue-100 text-blue-600 rounded-full">
                            {{ $estadisticas['cotizaciones'] ?? 0 }}
                        </span>
                    </button>
                    <button onclick="mostrarTab('seguimientos')" class="tab-button" id="tab-seguimientos">
                        <i class="fas fa-chart-line mr-2"></i>
                        Seguimientos
                        <span class="ml-2 py-0.5 px-2 text-xs bg-green-100 text-green-600 rounded-full">
                            {{ $estadisticas['seguimientos'] ?? 0 }}
                        </span>
                    </button>
                    <button onclick="mostrarTab('documentos')" class="tab-button" id="tab-documentos">
                        <i class="fas fa-folder mr-2"></i>
                        Documentos
                        <span class="ml-2 py-0.5 px-2 text-xs bg-purple-100 text-purple-600 rounded-full">
                            {{ $estadisticas['documentos'] ?? 0 }}
                        </span>
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="px-6 py-6">
                
                <!-- Tab Cotizaciones -->
                <div class="tab-content active" id="content-cotizaciones">
                    <div class="space-y-4" id="lista-cotizaciones">
                        <div class="text-center py-8">
                            <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                            <p class="text-gray-500">Cargando cotizaciones...</p>
                        </div>
                    </div>
                </div>

                <!-- Tab Seguimientos -->
                <div class="tab-content hidden" id="content-seguimientos">
                    <div class="space-y-4" id="lista-seguimientos">
                        <div class="text-center py-8">
                            <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                            <p class="text-gray-500">Cargando seguimientos...</p>
                        </div>
                    </div>
                </div>

                <!-- Tab Documentos -->
                <div class="tab-content hidden" id="content-documentos">
                    <div class="space-y-4" id="lista-documentos">
                        <div class="text-center py-8">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Gestión de Documentos</h3>
                            <p class="text-gray-500 mb-4">Sube y gestiona documentos relacionados con este cliente</p>
                            <button onclick="subirDocumento()" class="btn-primary">
                                <i class="fas fa-upload mr-2"></i>
                                Subir Documento
                            </button>
                        </div>
                    </div>
                </div>

            </div>
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
let tabActual = 'cotizaciones';

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    cargarContactosAdicionales();
    cargarCotizaciones();
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
    
    // Cargar contenido según el tab
    switch(nombreTab) {
        case 'cotizaciones':
            cargarCotizaciones();
            break;
        case 'seguimientos':
            cargarSeguimientos();
            break;
        case 'documentos':
            cargarDocumentos();
            break;
    }
}

// Funciones de carga de datos
async function cargarContactosAdicionales() {
    try {
        const response = await fetch(`/api/contactos/cliente/${clienteId}`);
        const data = await response.json();
        
        const contenedor = document.getElementById('lista-contactos-adicionales');
        
        if (data.success && data.data.length > 0) {
            contenedor.innerHTML = `
                <div class="space-y-3">
                    ${data.data.map(contacto => `
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="h-8 w-8 bg-gray-100 rounded-full flex items-center justify-center">
                                    <span class="text-xs font-medium text-gray-700">
                                        ${contacto.nombre.substring(0, 2).toUpperCase()}
                                    </span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">${contacto.nombre}</p>
                                    <p class="text-xs text-gray-500">${contacto.cargo || 'Sin cargo'}</p>
                                </div>
                            </div>
                            <div class="flex space-x-1">
                                ${contacto.email ? `<a href="mailto:${contacto.email}" class="text-blue-600 hover:text-blue-900"><i class="fas fa-envelope text-xs"></i></a>` : ''}
                                ${contacto.telefono ? `<a href="tel:${contacto.telefono}" class="text-green-600 hover:text-green-900"><i class="fas fa-phone text-xs"></i></a>` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
                <div class="pt-4 border-t border-gray-200 mt-4">
                    <button onclick="verTodosContactos()" class="text-sm text-blue-600 hover:text-blue-900">
                        Ver todos los contactos →
                    </button>
                </div>
            `;
        } else {
            contenedor.innerHTML = `
                <div class="text-center py-4">
                    <p class="text-sm text-gray-500 mb-3">No hay contactos adicionales</p>
                    <button onclick="agregarContacto()" class="text-sm text-blue-600 hover:text-blue-900">
                        <i class="fas fa-plus mr-1"></i>
                        Agregar contacto
                    </button>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error al cargar contactos:', error);
    }
}

async function cargarCotizaciones() {
    const contenedor = document.getElementById('lista-cotizaciones');
    
    try {
        const response = await fetch(`/api/cotizaciones?cliente=${clienteId}`);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            contenedor.innerHTML = `
                <div class="space-y-4">
                    ${data.data.map(cotizacion => `
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h4 class="text-lg font-medium text-gray-900">${cotizacion.nombre_cotizacion}</h4>
                                    <p class="text-sm text-gray-500">Código: ${cotizacion.codigo || 'Sin código'}</p>
                                    <div class="flex items-center space-x-4 mt-2">
                                        <span class="text-sm font-medium text-green-600">${cotizacion.total_con_iva}</span>
                                        <span class="px-2 py-1 text-xs rounded-full ${getEstadoClass(cotizacion.estado)}">
                                            ${cotizacion.estado}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            Válida hasta: ${formatearFecha(cotizacion.validez_oferta)}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="/cotizaciones/${cotizacion.id}" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/cotizaciones/${cotizacion.id}/edit" class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <div class="mt-6 text-center">
                    <a href="/cotizaciones?cliente=${clienteId}" class="btn-secondary">
                        <i class="fas fa-list mr-2"></i>
                        Ver Todas las Cotizaciones
                    </a>
                </div>
            `;
        } else {
            contenedor.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-file-invoice text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay cotizaciones</h3>
                    <p class="text-gray-500 mb-4">Este cliente no tiene cotizaciones registradas.</p>
                    <a href="/cotizaciones/create?cliente=${clienteId}" class="btn-primary">
                        <i class="fas fa-plus mr-2"></i>
                        Crear Primera Cotización
                    </a>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error al cargar cotizaciones:', error);
        contenedor.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
                <p class="text-red-600">Error al cargar cotizaciones</p>
            </div>
        `;
    }
}

async function cargarSeguimientos() {
    const contenedor = document.getElementById('lista-seguimientos');
    
    try {
        const response = await fetch(`/api/seguimiento/data?cliente=${clienteId}`);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            contenedor.innerHTML = `
                <div class="space-y-4">
                    ${data.data.map(seguimiento => `
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <span class="px-2 py-1 text-xs rounded-full ${getPrioridadClass(seguimiento.prioridad)}">
                                            ${seguimiento.prioridad}
                                        </span>
                                        <span class="px-2 py-1 text-xs rounded-full ${getEstadoSeguimientoClass(seguimiento.estado)}">
                                            ${seguimiento.estado}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-900 mb-1">
                                        <strong>Última gestión:</strong> ${formatearFecha(seguimiento.ultima_gestion) || 'Sin gestión'}
                                    </p>
                                    <p class="text-sm text-gray-900 mb-1">
                                        <strong>Próxima gestión:</strong> ${formatearFecha(seguimiento.proxima_gestion)}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <strong>Vendedor:</strong> ${seguimiento.vendedor?.name || 'Sin asignar'}
                                    </p>
                                    ${seguimiento.notas ? `<p class="text-sm text-gray-500 mt-2 italic">"${seguimiento.notas}"</p>` : ''}
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="editarSeguimiento(${seguimiento.id})" class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="completarSeguimiento(${seguimiento.id})" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <div class="mt-6 text-center">
                    <a href="/seguimiento?cliente=${clienteId}" class="btn-secondary">
                        <i class="fas fa-chart-line mr-2"></i>
                        Ver Todos los Seguimientos
                    </a>
                </div>
            `;
        } else {
            contenedor.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-chart-line text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay seguimientos</h3>
                    <p class="text-gray-500 mb-4">Este cliente no tiene seguimientos registrados.</p>
                    <a href="/seguimiento/create?cliente=${clienteId}" class="btn-primary">
                        <i class="fas fa-plus mr-2"></i>
                        Crear Primer Seguimiento
                    </a>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error al cargar seguimientos:', error);
        contenedor.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
                <p class="text-red-600">Error al cargar seguimientos</p>
            </div>
        `;
    }
}

function cargarDocumentos() {
    const contenedor = document.getElementById('lista-documentos');
    
    // Placeholder para funcionalidad futura
    contenedor.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-folder-open text-4xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Gestión de Documentos</h3>
            <p class="text-gray-500 mb-4">Funcionalidad de documentos en desarrollo según el roadmap.</p>
            <div class="space-y-3">
                <button onclick="subirDocumento()" class="btn-primary block w-full">
                    <i class="fas fa-upload mr-2"></i>
                    Subir Documento
                </button>
                <button onclick="mostrarToast('Función en desarrollo', 'info')" class="btn-secondary block w-full">
                    <i class="fas fa-search mr-2"></i>
                    Buscar Documentos
                </button>
            </div>
        </div>
    `;
}

// Funciones de utilidad
function getEstadoClass(estado) {
    const clases = {
        'Pendiente': 'bg-yellow-100 text-yellow-800',
        'Enviada': 'bg-blue-100 text-blue-800',
        'Aceptada': 'bg-green-100 text-green-800',
        'Rechazada': 'bg-red-100 text-red-800',
        'Vencida': 'bg-gray-100 text-gray-800'
    };
    return clases[estado] || 'bg-gray-100 text-gray-800';
}

function getEstadoSeguimientoClass(estado) {
    const clases = {
        'pendiente': 'bg-yellow-100 text-yellow-800',
        'en_proceso': 'bg-blue-100 text-blue-800',
        'completado': 'bg-green-100 text-green-800',
        'vencido': 'bg-red-100 text-red-800',
        'reprogramado': 'bg-purple-100 text-purple-800'
    };
    return clases[estado] || 'bg-gray-100 text-gray-800';
}

function getPrioridadClass(prioridad) {
    const clases = {
        'baja': 'bg-gray-100 text-gray-800',
        'media': 'bg-blue-100 text-blue-800',
        'alta': 'bg-orange-100 text-orange-800',
        'urgente': 'bg-red-100 text-red-800'
    };
    return clases[prioridad] || 'bg-gray-100 text-gray-800';
}

function formatearFecha(fecha) {
    if (!fecha) return 'Sin fecha';
    try {
        return new Date(fecha).toLocaleDateString('es-CL');
    } catch (e) {
        return fecha;
    }
}

// Funciones de acciones
function editarContactoPrincipal() {
    window.location.href = `/clientes/${clienteId}/edit#contacto`;
}

function gestionarVendedores() {
    window.location.href = `/clientes/${clienteId}/edit#configuracion`;
}

function agregarContacto() {
    window.location.href = `/contactos/create?cliente=${clienteId}`;
}

function verTodosContactos() {
    window.location.href = `/contactos?cliente=${clienteId}`;
}

function contactarVendedor(email) {
    if (email) {
        window.location.href = `mailto:${email}`;
    } else {
        mostrarToast('Vendedor sin email registrado', 'warning');
    }
}

function verSeguimientosVendedor(vendedorId) {
    window.location.href = `/seguimiento?vendedor=${vendedorId}&cliente=${clienteId}`;
}

function programarTarea() {
    mostrarToast('Redirigiendo a agenda...', 'info');
    setTimeout(() => {
        window.location.href = `/agenda/tareas/create?cliente=${clienteId}`;
    }, 1000);
}

function generarReporte() {
    mostrarToast('Generando reporte del cliente...', 'info');
    setTimeout(() => {
        window.open(`/clientes/${clienteId}/reporte`, '_blank');
    }, 1000);
}

function exportarCliente() {
    mostrarToast('Exportando datos del cliente...', 'info');
    setTimeout(() => {
        window.location.href = `/clientes/${clienteId}/export`;
    }, 1000);
}

function subirDocumento() {
    mostrarToast('Función de documentos en desarrollo según roadmap', 'info');
}

function editarSeguimiento(seguimientoId) {
    window.location.href = `/seguimiento/${seguimientoId}/edit`;
}

async function completarSeguimiento(seguimientoId) {
    try {
        const response = await fetch(`/api/seguimiento/${seguimientoId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                estado: 'completado',
                ultima_gestion: new Date().toISOString().split('T')[0]
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarToast('Seguimiento completado exitosamente', 'success');
            cargarSeguimientos(); // Recargar lista
        } else {
            mostrarToast('Error al completar seguimiento', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error al completar seguimiento', 'error');
    }
}

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

// Navegación con teclado
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case 'e':
                e.preventDefault();
                window.location.href = `/clientes/${clienteId}/edit`;
                break;
            case 'n':
                e.preventDefault();
                window.location.href = `/cotizaciones/create?cliente=${clienteId}`;
                break;
        }
    }
});
</script>
@endsection@extends('layouts.app')

@section('title', 'Cliente: ' . $cliente->nombre_institucion)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header del Cliente -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <div class="h-16 w-16 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                        <span class="text-xl font-bold text-blue-700">
                            {{ strtoupper(substr($cliente->nombre_institucion, 0, 2)) }}
                        </span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $cliente->nombre_institucion }}</h1>
                        <div class="flex items-center space-x-4 mt-1">
                            <span class="px-3 py-1 text-sm font-medium rounded-full 
                                {{ $cliente->tipo_cliente === 'Cliente Público' ? 'bg-purple-100 text-purple-800' : 
                                   ($cliente->tipo_cliente === 'Cliente Privado' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800') }}">
                                {{ $cliente->tipo_cliente }}
                            </span>
                            @if($cliente->rut)
                                <span class="text-sm text-gray-600">
                                    <i class="fas fa-id-card mr-1"></i>{{ $cliente->rut }}
                                </span>
                            @endif
                            <span class="text-sm text-gray-600">
                                <i class="fas fa-calendar mr-1"></i>Cliente desde {{ $cliente->created_at->format('M Y') }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button onclick="exportarCliente()" class="btn-secondary">
                        <i class="fas fa-download mr-2"></i>
                        Exportar
                    </button>
                    <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn-primary">
                        <i class="fas fa-edit mr-2"></i>
                        Editar Cliente
                    </a>
                    <a href="{{ route('clientes.index') }}" class="btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div