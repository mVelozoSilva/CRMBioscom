@extends('layouts.app')

@section('title', 'Triaje de Seguimientos - CRM Bioscom')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header con Estadísticas Críticas -->
        <div class="mb-8">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-triage-medical text-blue-600 mr-3"></i>
                        Triaje Inteligente
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Sistema de clasificación automática y distribución de seguimientos
                    </p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                    <button type="button" id="btn-distribuir-automatico" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-magic mr-2"></i>
                        Distribución Automática
                    </button>
                    <button type="button" id="btn-configurar" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-cog mr-2"></i>
                        Configurar
                    </button>
                </div>
            </div>
        </div>

        <!-- Estadísticas de Triaje -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <!-- Críticos -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-red-900">Críticos</h3>
                        <p class="text-2xl font-bold text-red-600" id="stat-criticos">
                            {{ $estadisticas['vencidos_criticos'] ?? 0 }}
                        </p>
                        <p class="text-sm text-red-700">+7 días vencidos</p>
                    </div>
                </div>
            </div>

            <!-- Vencidos -->
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-clock text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-orange-900">Vencidos</h3>
                        <p class="text-2xl font-bold text-orange-600" id="stat-vencidos">
                            {{ $estadisticas['vencidos'] ?? 0 }}
                        </p>
                        <p class="text-sm text-orange-700">Requieren atención</p>
                    </div>
                </div>
            </div>

            <!-- Urgentes Hoy -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-bell text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-yellow-900">Hoy</h3>
                        <p class="text-2xl font-bold text-yellow-600" id="stat-hoy">
                            {{ $estadisticas['urgentes_hoy'] ?? 0 }}
                        </p>
                        <p class="text-sm text-yellow-700">Vencen hoy</p>
                    </div>
                </div>
            </div>

            <!-- Sin Asignar -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-user-plus text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-blue-900">Sin Asignar</h3>
                        <p class="text-2xl font-bold text-blue-600" id="stat-sin-asignar">
                            {{ $estadisticas['sin_asignar'] ?? 0 }}
                        </p>
                        <p class="text-sm text-blue-700">Requieren distribución</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros Avanzados -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Filtros de Clasificación</h3>
            </div>
            <div class="p-6">
                <form id="form-filtros" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Filtro por Urgencia -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Urgencia</label>
                        <select name="dias_vencimiento" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="">Todos</option>
                            <option value="vencidos">Vencidos</option>
                            <option value="hoy">Vencen Hoy</option>
                            <option value="manana">Vencen Mañana</option>
                            <option value="proximos_7">Próximos 7 días</option>
                        </select>
                    </div>

                    <!-- Filtro por Vendedor -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Vendedor</label>
                        <select name="vendedor_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="">Todos</option>
                            <option value="sin_asignar">Sin Asignar</option>
                            @foreach($vendedores ?? [] as $vendedor)
                                <option value="{{ $vendedor->id }}">{{ $vendedor->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Tipo de Cliente -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo Cliente</label>
                        <select name="tipo_cliente" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="">Todos</option>
                            <option value="Cliente Público">Cliente Público</option>
                            <option value="Cliente Privado">Cliente Privado</option>
                            <option value="Revendedor">Revendedor</option>
                        </select>
                    </div>

                    <!-- Filtro por Monto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Monto Mínimo</label>
                        <select name="monto_minimo" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="">Todos</option>
                            <option value="100000">$100.000+</option>
                            <option value="500000">$500.000+</option>
                            <option value="1000000">$1.000.000+</option>
                            <option value="5000000">$5.000.000+</option>
                        </select>
                    </div>
                </form>

                <div class="mt-4 flex justify-between">
                    <button type="button" id="btn-aplicar-filtros" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-filter mr-2"></i>
                        Aplicar Filtros
                    </button>
                    <button type="button" id="btn-limpiar-filtros" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-eraser mr-2"></i>
                        Limpiar
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de Seguimientos Clasificados -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Seguimientos Clasificados</h3>
                <div class="flex space-x-2">
                    <button type="button" id="btn-seleccionar-todos" 
                            class="text-sm text-blue-600 hover:text-blue-800">
                        Seleccionar Todos
                    </button>
                    <span class="text-gray-300">|</span>
                    <button type="button" id="btn-accion-masiva" 
                            class="text-sm text-green-600 hover:text-green-800"
                            disabled>
                        Acción Masiva
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="tabla-seguimientos">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left">
                                <input type="checkbox" id="check-todos" class="rounded border-gray-300">
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Urgencia
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cliente
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cotización
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Vendedor
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Próxima Gestión
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="tbody-seguimientos">
                        <!-- Contenido cargado dinámicamente por JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Loading State -->
            <div id="loading-state" class="hidden p-8 text-center">
                <div class="inline-flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Clasificando seguimientos...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Acción Masiva -->
<div id="modal-accion-masiva" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-magic text-blue-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Acción Masiva
                        </h3>
                        <div class="mt-4">
                            <select id="select-accion" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Seleccionar acción...</option>
                                <option value="distribuir">Distribuir Automáticamente</option>
                                <option value="reasignar">Reasignar Vendedor</option>
                                <option value="posponer">Posponer 3 días</option>
                                <option value="marcar_completado">Marcar Completado</option>
                            </select>
                            
                            <div id="parametros-adicionales" class="mt-4 hidden">
                                <!-- Parámetros adicionales según la acción -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="btn-confirmar-accion" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Ejecutar
                </button>
                <button type="button" id="btn-cancelar-accion" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let seguimientosSeleccionados = [];
    let tableLoading = false;

    // Elementos DOM
    const tablaSeguimientos = document.getElementById('tbody-seguimientos');
    const loadingState = document.getElementById('loading-state');
    const btnAplicarFiltros = document.getElementById('btn-aplicar-filtros');
    const btnLimpiarFiltros = document.getElementById('btn-limpiar-filtros');
    const btnAccionMasiva = document.getElementById('btn-accion-masiva');
    const modalAccionMasiva = document.getElementById('modal-accion-masiva');

    // Inicialización
    cargarSeguimientos();

    // Event Listeners
    btnAplicarFiltros.addEventListener('click', cargarSeguimientos);
    btnLimpiarFiltros.addEventListener('click', limpiarFiltros);
    btnAccionMasiva.addEventListener('click', abrirModalAccionMasiva);

    // Función principal para cargar seguimientos
    async function cargarSeguimientos() {
        if (tableLoading) return;
        
        setLoadingState(true);
        
        try {
            const formData = new FormData(document.getElementById('form-filtros'));
            const params = new URLSearchParams(formData);
            
            const response = await fetch(`/api/triaje/seguimientos?${params}`);
            const data = await response.json();
            
            if (data.success) {
                renderizarSeguimientos(data.data);
                actualizarEstadisticas(data.estadisticas);
            } else {
                mostrarError('Error al cargar seguimientos clasificados');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarError('Error de conexión al cargar seguimientos');
        } finally {
            setLoadingState(false);
        }
    }

    // Renderizar tabla de seguimientos
    function renderizarSeguimientos(seguimientos) {
        tablaSeguimientos.innerHTML = '';
        
        seguimientos.forEach(seguimiento => {
            const fila = crearFilaSeguimiento(seguimiento);
            tablaSeguimientos.appendChild(fila);
        });
        
        actualizarSeleccionMasiva();
    }

    // Crear fila individual de seguimiento
    function crearFilaSeguimiento(seguimiento) {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50';
        tr.dataset.seguimientoId = seguimiento.id;
        
        const metadata = seguimiento.metadata_triaje;
        const colorIndicador = metadata.color_indicador || 'bg-gray-200';
        
        tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="checkbox" class="check-seguimiento rounded border-gray-300" value="${seguimiento.id}">
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colorIndicador}">
                        ${metadata.mensaje_urgencia || 'Normal'}
                    </span>
                    ${metadata.requiere_accion_inmediata ? '<i class="fas fa-exclamation-triangle text-red-500 ml-2"></i>' : ''}
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${seguimiento.cliente?.nombre_institucion || 'N/A'}</div>
                <div class="text-sm text-gray-500">${seguimiento.cliente?.tipo_cliente || ''}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${seguimiento.cotizacion?.nombre_cotizacion || 'Sin cotización'}</div>
                ${seguimiento.cotizacion?.total_con_iva ? `<div class="text-sm text-gray-500">$${formatearMonto(seguimiento.cotizacion.total_con_iva)}</div>` : ''}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                ${seguimiento.vendedor ? `
                    <div class="text-sm text-gray-900">${seguimiento.vendedor.name}</div>
                ` : `
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Sin asignar
                    </span>
                `}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${formatearFecha(seguimiento.proxima_gestion)}
                <div class="text-xs text-gray-500">${metadata.dias_diferencia >= 0 ? `En ${metadata.dias_diferencia} días` : `${Math.abs(metadata.dias_diferencia)} días vencido`}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${obtenerColorEstado(seguimiento.estado)}">
                    ${seguimiento.estado}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button class="text-blue-600 hover:text-blue-900 mr-2" onclick="analizarSeguimiento(${seguimiento.id})">
                    <i class="fas fa-search"></i>
                </button>
                <button class="text-green-600 hover:text-green-900" onclick="asignarRapido(${seguimiento.id})">
                    <i class="fas fa-user-plus"></i>
                </button>
            </td>
        `;
        
        return tr;
    }

    // Funciones auxiliares
    function setLoadingState(loading) {
        tableLoading = loading;
        if (loading) {
            tablaSeguimientos.classList.add('hidden');
            loadingState.classList.remove('hidden');
        } else {
            tablaSeguimientos.classList.remove('hidden');
            loadingState.classList.add('hidden');
        }
    }

    function formatearMonto(monto) {
        return new Intl.NumberFormat('es-CL').format(monto);
    }

    function formatearFecha(fecha) {
        return new Date(fecha).toLocaleDateString('es-CL');
    }

    function obtenerColorEstado(estado) {
        const colores = {
            'pendiente': 'bg-yellow-100 text-yellow-800',
            'en_proceso': 'bg-blue-100 text-blue-800',
            'completado': 'bg-green-100 text-green-800',
            'vencido': 'bg-red-100 text-red-800',
            'reprogramado': 'bg-gray-100 text-gray-800'
        };
        return colores[estado] || 'bg-gray-100 text-gray-800';
    }

    function actualizarEstadisticas(estadisticas) {
        document.getElementById('stat-criticos').textContent = estadisticas.vencidos_criticos || 0;
        document.getElementById('stat-vencidos').textContent = estadisticas.vencidos || 0;
        document.getElementById('stat-hoy').textContent = estadisticas.urgentes_hoy || 0;
        document.getElementById('stat-sin-asignar').textContent = estadisticas.sin_asignar || 0;
    }

    function mostrarError(mensaje) {
        // Implementar sistema de notificaciones
        alert(mensaje);
    }

    function limpiarFiltros() {
        document.getElementById('form-filtros').reset();
        cargarSeguimientos();
    }

    function actualizarSeleccionMasiva() {
        const checkboxes = document.querySelectorAll('.check-seguimiento');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                if (this.checked) {
                    seguimientosSeleccionados.push(this.value);
                } else {
                    seguimientosSeleccionados = seguimientosSeleccionados.filter(id => id !== this.value);
                }
                btnAccionMasiva.disabled = seguimientosSeleccionados.length === 0;
            });
        });
    }

    function abrirModalAccionMasiva() {
        if (seguimientosSeleccionados.length === 0) return;
        modalAccionMasiva.classList.remove('hidden');
    }

    // Funciones globales para botones de acción
    window.analizarSeguimiento = function(id) {
        window.location.href = `/triaje/analizar/${id}`;
    };

    window.asignarRapido = function(id) {
        // Implementar asignación rápida
        console.log('Asignar rápido:', id);
    };
});
</script>
@endsection

@section('styles')
<style>
/* Estilos personalizados para el triaje */
.triaje-urgente {
    background: linear-gradient(90deg, #fee2e2 0%, #fef3f2 100%);
    border-left: 4px solid #ef4444;
}

.triaje-normal {
    background: linear-gradient(90deg, #f0fdf4 0%, #f7fee7 100%);
    border-left: 4px solid #22c55e;
}

.indicador-prioridad {
    position: relative;
}

.indicador-prioridad::before {
    content: '';
    position: absolute;
    left: -8px;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 100%;
    border-radius: 2px;
}

.prioridad-critica::before {
    background-color: #dc2626;
}

.prioridad-alta::before {
    background-color: #ea580c;
}

.prioridad-media::before {
    background-color: #ca8a04;
}

.prioridad-baja::before {
    background-color: #16a34a;
}
</style>
@endsection