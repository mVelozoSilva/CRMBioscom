@extends('layouts.app')

@section('title', 'Gestión de Seguimientos')

@section('content')
<div class="min-h-screen bg-gray-50" id="seguimiento-app">
    <!-- ENCABEZADO DE LA PÁGINA -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                            <i class="fas fa-tasks text-blue-600 mr-3"></i>
                            Gestión de Seguimientos
                        </h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Administra y da seguimiento a todas las oportunidades de venta
                        </p>
                    </div>
                    
                    <!-- ESTADÍSTICAS RÁPIDAS -->
                    <div class="hidden lg:flex items-center space-x-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600" id="contador-atrasados">0</div>
                            <div class="text-xs text-gray-500">Atrasados</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600" id="contador-proximos">0</div>
                            <div class="text-xs text-gray-500">Próximos</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600" id="contador-hoy">0</div>
                            <div class="text-xs text-gray-500">Hoy</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600" id="contador-completados">0</div>
                            <div class="text-xs text-gray-500">Completados</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <!-- ALERTAS Y MENSAJES -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                    <i class="fas fa-check-circle text-green-500 mr-3 mt-0.5"></i>
                    <div class="text-green-700">{{ session('success') }}</div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-0.5"></i>
                    <div class="text-red-700">{{ session('error') }}</div>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-3 mt-0.5"></i>
                    <div class="text-yellow-700">{{ session('warning') }}</div>
                </div>
            </div>
        @endif

        <!-- COMPONENTE VUE DE SEGUIMIENTO -->
        <seguimiento-table 
            :vendedores="{{ json_encode($vendedores ?? []) }}"
            :puede-importar="{{ auth()->user()->esJefe() || auth()->user()->esAdministrador() ? 'true' : 'false' }}"
        ></seguimiento-table>

        <!-- AYUDA Y ATAJOS DE TECLADO -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-blue-900 mb-2">
                <i class="fas fa-info-circle mr-1"></i>
                Atajos de Teclado
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-blue-700">
                <div>
                    <kbd class="px-2 py-1 bg-blue-100 rounded">Ctrl + N</kbd>
                    <span class="ml-2">Nuevo seguimiento</span>
                </div>
                <div>
                    <kbd class="px-2 py-1 bg-blue-100 rounded">Ctrl + S</kbd>
                    <span class="ml-2">Guardar cambios</span>
                </div>
                <div>
                    <kbd class="px-2 py-1 bg-blue-100 rounded">Ctrl + F</kbd>
                    <span class="ml-2">Buscar</span>
                </div>
                <div>
                    <kbd class="px-2 py-1 bg-blue-100 rounded">Ctrl + A</kbd>
                    <span class="ml-2">Seleccionar todos</span>
                </div>
                <div>
                    <kbd class="px-2 py-1 bg-blue-100 rounded">Ctrl + U</kbd>
                    <span class="ml-2">Actualización masiva</span>
                </div>
                <div>
                    <kbd class="px-2 py-1 bg-blue-100 rounded">F5</kbd>
                    <span class="ml-2">Actualizar vista</span>
                </div>
            </div>
        </div>

        <!-- DISTRIBUCIÓN AUTOMÁTICA (SOLO JEFES) -->
        @if(auth()->user()->esJefe())
        <div class="mt-6 bg-purple-50 border border-purple-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-purple-900 mb-3">
                <i class="fas fa-magic mr-1"></i>
                Distribución Automática de Seguimientos Vencidos
            </h3>
            <p class="text-xs text-purple-700 mb-3">
                Distribuye automáticamente los seguimientos vencidos de tu equipo a lo largo de los próximos días hábiles.
            </p>
            
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-purple-700 mb-1">Máximo por día</label>
                    <select id="max-por-dia" class="form-select text-xs">
                        <option value="3">3 seguimientos</option>
                        <option value="5" selected>5 seguimientos</option>
                        <option value="7">7 seguimientos</option>
                        <option value="10">10 seguimientos</option>
                    </select>
                </div>
                
                <div class="flex-1">
                    <label class="block text-xs font-medium text-purple-700 mb-1">Días a distribuir</label>
                    <select id="dias-distribucion" class="form-select text-xs">
                        <option value="5" selected>5 días</option>
                        <option value="7">7 días</option>
                        <option value="10">10 días</option>
                        <option value="15">15 días</option>
                    </select>
                </div>
                
                <div class="flex-shrink-0">
                    <button 
                        onclick="distribuirSeguimientosVencidos()"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-xs font-medium transition-colors"
                    >
                        <i class="fas fa-magic mr-1"></i>
                        Distribuir
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- MODAL DE DISTRIBUCIÓN (SOLO JEFES) -->
@if(auth()->user()->esJefe())
<div id="modal-distribucion" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Distribución Automática</h3>
            <div id="resultado-distribucion" class="space-y-3">
                <!-- Se llena dinámicamente -->
            </div>
            <div class="flex justify-end mt-6">
                <button 
                    onclick="cerrarModalDistribucion()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium"
                >
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Atajos de teclado
    document.addEventListener('keydown', function(e) {
        // Solo activar si no estamos en un input
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            return;
        }
        
        if (e.ctrlKey || e.metaKey) {
            switch(e.key) {
                case 'n':
                    e.preventDefault();
                    // Trigger nuevo seguimiento en Vue component
                    if (window.seguimientoApp) {
                        window.seguimientoApp.mostrarModalCrear = true;
                    }
                    break;
                    
                case 'f':
                    e.preventDefault();
                    // Focus en búsqueda
                    const searchInput = document.querySelector('input[placeholder*="Buscar"]');
                    if (searchInput) {
                        searchInput.focus();
                    }
                    break;
                    
                case 'a':
                    e.preventDefault();
                    // Seleccionar todos
                    if (window.seguimientoApp) {
                        window.seguimientoApp.todosMarcados = true;
                        window.seguimientoApp.toggleTodos();
                    }
                    break;
                    
                case 'u':
                    e.preventDefault();
                    // Actualización masiva
                    if (window.seguimientoApp && window.seguimientoApp.seguimientosSeleccionados.length > 0) {
                        window.seguimientoApp.actualizarMasivo();
                    }
                    break;
            }
        }
        
        if (e.key === 'F5') {
            e.preventDefault();
            // Actualizar vista
            if (window.seguimientoApp) {
                window.seguimientoApp.cargarSeguimientos();
            }
        }
    });
    
    // Auto-refresh cada 5 minutos
    setInterval(function() {
        if (window.seguimientoApp && !window.seguimientoApp.cargando) {
            window.seguimientoApp.cargarSeguimientos(false);
            actualizarContadores();
        }
    }, 300000);
    
    // Actualizar contadores inicialmente
    actualizarContadores();
});

/**
 * ACTUALIZAR CONTADORES DE ESTADÍSTICAS
 */
function actualizarContadores() {
    fetch('/api/seguimiento/data?solo_estadisticas=1')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.estadisticas) {
                document.getElementById('contador-atrasados').textContent = data.estadisticas.atrasados || 0;
                document.getElementById('contador-proximos').textContent = data.estadisticas.proximos_7_dias || 0;
                document.getElementById('contador-hoy').textContent = data.estadisticas.hoy || 0;
                document.getElementById('contador-completados').textContent = data.estadisticas.completados_hoy || 0;
            }
        })
        .catch(error => console.error('Error al actualizar contadores:', error));
}

@if(auth()->user()->esJefe())
/**
 * DISTRIBUCIÓN AUTOMÁTICA DE SEGUIMIENTOS VENCIDOS
 */
function distribuirSeguimientosVencidos() {
    const maxPorDia = document.getElementById('max-por-dia').value;
    const diasDistribucion = document.getElementById('dias-distribucion').value;
    
    if (!confirm(`¿Estás seguro de distribuir seguimientos vencidos?\n\nConfiguración:\n- Máximo ${maxPorDia} por día\n- Durante ${diasDistribucion} días`)) {
        return;
    }
    
    // Obtener vendedores del equipo
    fetch('/api/seguimiento/vendedores')
        .then(response => response.json())
        .then(vendedores => {
            const vendedorIds = vendedores.map(v => v.id);
            
            return fetch('/agenda/distribuir-seguimientos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    vendedores_ids: vendedorIds,
                    max_por_dia: parseInt(maxPorDia),
                    dias_distribucion: parseInt(diasDistribucion),
                    incluir_fines_semana: false,
                    prioridad_defecto: 'media'
                })
            });
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarResultadoDistribucion(data.resultado);
                
                // Actualizar vista de seguimientos
                if (window.seguimientoApp) {
                    window.seguimientoApp.cargarSeguimientos();
                }
            } else {
                alert('Error en distribución: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión en distribución automática');
        });
}

function mostrarResultadoDistribucion(resultado) {
    const modal = document.getElementById('modal-distribucion');
    const contenido = document.getElementById('resultado-distribucion');
    
    contenido.innerHTML = `
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <h4 class="text-green-800 font-medium mb-2">
                <i class="fas fa-check-circle mr-1"></i>
                Distribución Completada
            </h4>
            <div class="text-sm text-green-700 space-y-1">
                <div>• Seguimientos procesados: ${resultado.seguimientos_procesados}</div>
                <div>• Tareas creadas: ${resultado.tareas_creadas}</div>
                <div>• Distribuidos hasta: ${resultado.distribuidos_hasta}</div>
            </div>
        </div>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="text-blue-800 font-medium mb-2">
                <i class="fas fa-info-circle mr-1"></i>
                ¿Qué pasó?
            </h4>
            <div class="text-sm text-blue-700">
                Los seguimientos vencidos se han convertido en tareas específicas en las agendas de los vendedores,
                distribuidas automáticamente durante los próximos días hábiles.
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function cerrarModalDistribucion() {
    document.getElementById('modal-distribucion').classList.add('hidden');
}
@endif

// Hacer disponible para el componente Vue
window.actualizarContadores = actualizarContadores;
</script>
@endpush

@push('styles')
<style>
/* Estilos específicos para la vista de seguimientos */
.form-select {
    @apply border border-gray-300 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent;
}

kbd {
    font-family: ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", Menlo, monospace;
    font-size: 0.75rem;
}

/* Animaciones suaves para contadores */
#contador-atrasados,
#contador-proximos,
#contador-hoy,
#contador-completados {
    transition: all 0.3s ease;
}

#contador-atrasados:hover,
#contador-proximos:hover,
#contador-hoy:hover,
#contador-completados:hover {
    transform: scale(1.1);
}

/* Mejoras responsive */
@media (max-width: 768px) {
    .hidden.lg\\:flex {
        display: none !important;
    }
}

/* Loading states */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 20px;
    height: 20px;
    border: 2px solid #3B82F6;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}
</style>
@endpush