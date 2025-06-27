@extends('layouts.app')

@section('title', 'Mi Día - Agenda')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl font-bold text-gray-900 leading-tight">
                        <i class="fas fa-calendar-day text-blue-600 mr-3"></i>
                        Mi Día
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Gestiona tus tareas del día {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
                <div class="mt-4 md:mt-0 md:ml-4">
                    <div class="flex items-center space-x-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-clock mr-1"></i>
                            {{ \Carbon\Carbon::now('America/Santiago')->format('H:i') }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <i class="fas fa-calendar mr-1"></i>
                            {{ \Carbon\Carbon::now('America/Santiago')->locale('es')->translatedFormat('l') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
       <!-- 🚨 BOTÓN DE EMERGENCIA - DISTRIBUCIÓN AUTOMÁTICA -->
        <div class="mb-6">
            <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-lg border-2 border-orange-200 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-magic text-2xl text-orange-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">¿Seguimientos Acumulados?</h3>
                            <p class="text-sm text-gray-600">Organiza automáticamente todos los vencidos en tu agenda</p>
                        </div>
                    </div>
                    <button onclick="abrirDistribucionAutomatica()" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg font-bold text-sm hover:from-orange-600 hover:to-red-600 focus:outline-none focus:ring-4 focus:ring-orange-300 shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                        <i class="fas fa-magic mr-2"></i>
                        DISTRIBUIR AUTOMÁTICAMENTE
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Stats Section con Análisis Inteligente -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Pendientes Hoy -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-tasks text-yellow-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">Pendientes Hoy</h3>
                            <p class="text-2xl font-semibold text-gray-900" id="stat-pendientes">-</p>
                        </div>
                    </div>
                </div>

                <!-- En Progreso -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-play-circle text-blue-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">En Progreso</h3>
                            <p class="text-2xl font-semibold text-gray-900" id="stat-progreso">-</p>
                        </div>
                    </div>
                </div>

                <!-- Completadas -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">Completadas</h3>
                            <p class="text-2xl font-semibold text-gray-900" id="stat-completadas">-</p>
                        </div>
                    </div>
                </div>

                <!-- Vencidas -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">Vencidas</h3>
                            <p class="text-2xl font-semibold text-gray-900" id="stat-vencidas">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🧠 WIDGET DE ANÁLISIS INTELIGENTE -->
        <div id="widgetAnalisisInteligente" class="mb-8 hidden">
            <!-- Estado Normal/Bueno -->
            <div id="estadoNormal" class="hidden">
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200 p-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-smile text-green-600 text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-green-900">¡Agenda Balanceada!</h3>
                            <p class="text-xs text-green-700" id="mensajeNormal">Tu carga de trabajo está bien distribuida esta semana.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerta de Sobrecarga -->
            <div id="alertaSobrecarga" class="hidden">
                <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-lg border-2 border-orange-300 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-exclamation-triangle text-orange-600 text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-orange-900">¡Alerta de Sobrecarga!</h3>
                                <p class="text-xs text-orange-700" id="mensajeSobrecarga">Detectamos días muy cargados en tu agenda.</p>
                            </div>
                        </div>
                        <button onclick="abrirSugerenciasInteligentes()" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg text-xs font-bold hover:from-orange-600 hover:to-red-600 focus:outline-none focus:ring-2 focus:ring-orange-300 shadow-md hover:shadow-lg transition-all">
                            <i class="fas fa-magic mr-1"></i>
                            VER SUGERENCIAS
                        </button>
                    </div>
                </div>
            </div>

            <!-- Alerta Moderada -->
            <div id="alertaModerada" class="hidden">
                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg border border-yellow-300 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-clock text-yellow-600 text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-yellow-900">Carga Alta Detectada</h3>
                                <p class="text-xs text-yellow-700" id="mensajeModerado">Algunos días tienen bastantes tareas.</p>
                            </div>
                        </div>
                        <button onclick="abrirSugerenciasInteligentes()" class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white rounded-lg text-xs font-medium hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-300 shadow-sm hover:shadow-md transition-all">
                            <i class="fas fa-lightbulb mr-1"></i>
                            Ver Tips
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content: Lista de Tareas -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <!-- Component Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-list mr-2 text-blue-600"></i>
                        Mis Tareas del Día
                    </h2>
                    <div class="flex space-x-3">
                        <button onclick="abrirModalNuevaTarea()" type="button" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Nueva Tarea
                        </button>
                        <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Actualizar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Vue Component Mount Point -->
            <div id="tarea-day-list-container">
                <tarea-day-list></tarea-day-list>
            </div>
        </div>
       <!-- Modal de Nueva Tarea -->
        <tarea-form 
            :visible="false" 
            ref="tareaForm"
        ></tarea-form>
        <!-- Modal de Distribución Automática -->
    <div id="modalDistribucion" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
        <div class="relative top-20 mx-auto p-5 border w-[500px] shadow-xl rounded-lg bg-white">
            <!-- Header del Modal -->
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-magic text-orange-500 mr-2"></i>
                    Distribución Automática
                </h3>
                <button onclick="cerrarDistribucionAutomatica()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Explicación Simple -->
            <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                    <div>
                        <h4 class="font-semibold text-blue-900 mb-1">¿Qué hace esta función?</h4>
                        <p class="text-sm text-blue-700">
                            Toma todos los seguimientos vencidos y los distribuye automáticamente en tu agenda 
                            durante los próximos días hábiles. <strong>¡Problema resuelto en 30 segundos!</strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Formulario Ultra-Simple -->
            <form id="formDistribucion" onsubmit="ejecutarDistribucion(event)">
                <div class="space-y-4">
                    <!-- Tareas por día -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-calendar-day text-green-500 mr-1"></i>
                            ¿Cuántas tareas máximo por día?
                        </label>
                        <select id="tareasPorDia" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="3">3 tareas por día (relajado)</option>
                            <option value="5" selected>5 tareas por día (recomendado)</option>
                            <option value="8">8 tareas por día (intensivo)</option>
                            <option value="10">10 tareas por día (máximo)</option>
                        </select>
                    </div>

                    <!-- Días a distribuir -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt text-blue-500 mr-1"></i>
                            ¿En cuántos días distribuir?
                        </label>
                        <select id="diasDistribuir" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="5">5 días hábiles (1 semana)</option>
                            <option value="10" selected>10 días hábiles (2 semanas)</option>
                            <option value="15">15 días hábiles (3 semanas)</option>
                            <option value="20">20 días hábiles (1 mes)</option>
                        </select>
                    </div>

                    <!-- Previsualización -->
                    <div class="p-3 bg-green-50 rounded-lg border border-green-200">
                        <p class="text-sm text-green-700">
                            <i class="fas fa-calculator text-green-500 mr-1"></i>
                            <strong>Resultado:</strong> Se podrán distribuir hasta <span id="maxTareas" class="font-bold">50</span> seguimientos vencidos
                        </p>
                    </div>
                </div>

                <!-- Botones del Modal -->
                <div class="flex justify-end space-x-3 mt-8 pt-4 border-t border-gray-200">
                    <button type="button" onclick="cerrarDistribucionAutomatica()" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" id="btnDistribuir" class="px-8 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg font-bold hover:from-orange-600 hover:to-red-600 focus:outline-none focus:ring-4 focus:ring-orange-300 shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                        <i class="fas fa-magic mr-2"></i>
                        <span id="textoBoton">DISTRIBUIR AHORA</span>
                    </button>
                </div>
            </form>

            <!-- Estado de Éxito -->
            <div id="estadoExito" class="hidden">
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check text-3xl text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-green-900 mb-2">¡Distribución Completada!</h3>
                    <p class="text-green-700 mb-4" id="mensajeExito">
                        Se crearon 47 tareas distribuidas en 10 días hábiles
                    </p>
                    <button onclick="cerrarDistribucionAutomatica()" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-thumbs-up mr-2"></i>
                        ¡Perfecto!
                    </button>
                </div>
            </div>
        </div>
    </div>
        <!-- Fin del Modal de Distribución Automática -->
        <!-- Modal de Sugerencias Inteligentes -->
    <div id="modalSugerencias" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
        <div class="relative top-10 mx-auto p-5 border w-[700px] shadow-xl rounded-lg bg-white">
            <!-- Header del Modal -->
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-brain text-purple-500 mr-2"></i>
                    Análisis Inteligente de Tu Agenda
                </h3>
                <button onclick="cerrarSugerenciasInteligentes()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Loading State -->
            <div id="loadingSugerencias" class="text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-100 rounded-full mb-4">
                    <i class="fas fa-brain text-2xl text-purple-600 animate-pulse"></i>
                </div>
                <p class="text-gray-600">Analizando tu carga de trabajo...</p>
            </div>

            <!-- Contenido Principal -->
            <div id="contenidoSugerencias" class="hidden">
                <!-- Diagnóstico Semanal -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-3">
                        <i class="fas fa-stethoscope text-blue-500 mr-2"></i>
                        Diagnóstico de Tu Semana
                    </h4>
                    <div id="diagnosticoSemanal" class="grid grid-cols-7 gap-2">
                        <!-- Los días se llenarán dinámicamente -->
                    </div>
                </div>

                <!-- Alertas Críticas -->
                <div id="seccionAlertas" class="mb-6 hidden">
                    <h4 class="text-lg font-semibold text-red-900 mb-3">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                        Alertas Críticas
                    </h4>
                    <div id="listaAlertas" class="space-y-2">
                        <!-- Las alertas se llenarán dinámicamente -->
                    </div>
                </div>

                <!-- Sugerencias de Optimización -->
                <div id="seccionSugerencias" class="mb-6 hidden">
                    <h4 class="text-lg font-semibold text-green-900 mb-3">
                        <i class="fas fa-lightbulb text-green-500 mr-2"></i>
                        Sugerencias de Optimización
                    </h4>
                    <div id="listaSugerencias" class="space-y-4">
                        <!-- Las sugerencias se llenarán dinámicamente -->
                    </div>
                </div>

                <!-- Estado Saludable -->
                <div id="estadoSaludable" class="hidden text-center py-8">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-smile text-3xl text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-green-900 mb-2">¡Agenda Perfectamente Balanceada!</h3>
                    <p class="text-green-700 mb-4">
                        Tu carga de trabajo está muy bien distribuida. No necesitas optimizaciones.
                    </p>
                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                        <p class="text-sm text-green-700">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <strong>Consejo:</strong> Mantén este ritmo sostenible para evitar el burnout.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Botones del Modal -->
            <div id="botonesSugerencias" class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200 hidden">
                <button onclick="cerrarSugerenciasInteligentes()" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Cerrar
                </button>
                <button id="btnAplicarTodo" onclick="aplicarTodasLasSugerencias()" class="hidden px-8 py-3 bg-gradient-to-r from-green-500 to-blue-500 text-white rounded-lg font-bold hover:from-green-600 hover:to-blue-600 focus:outline-none focus:ring-4 focus:ring-green-300 shadow-lg hover:shadow-xl transition-all">
                    <i class="fas fa-magic mr-2"></i>
                    APLICAR TODAS LAS OPTIMIZACIONES
                </button>
            </div>
        </div>
    </div>

    <!-- Template para Día del Diagnóstico -->
    <template id="templateDiaDiagnostico">
        <div class="text-center p-2 rounded-lg border">
            <div class="text-xs font-medium text-gray-600 mb-1"></div>
            <div class="text-lg font-bold mb-1"></div>
            <div class="text-xs"></div>
        </div>
    </template>

    <!-- Template para Alerta -->
    <template id="templateAlerta">
        <div class="p-3 rounded-lg border-l-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span class="font-medium"></span>
            </div>
        </div>
    </template>

    <!-- Template para Sugerencia -->
    <template id="templateSugerencia">
        <div class="bg-blue-50 rounded-lg border border-blue-200 p-4">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h5 class="font-semibold text-blue-900 mb-2">
                        <i class="fas fa-arrows-alt mr-2"></i>
                        <span class="titulo-sugerencia"></span>
                    </h5>
                    <p class="text-sm text-blue-700 mb-3 descripcion-sugerencia"></p>
                    
                    <!-- Vista Antes/Después -->
                    <div class="grid grid-cols-2 gap-4 mb-3">
                        <div class="antes-despues bg-white rounded p-2 border">
                            <div class="text-xs font-medium text-gray-500 mb-1">ANTES</div>
                            <div class="antes-contenido"></div>
                        </div>
                        <div class="antes-despues bg-white rounded p-2 border">
                            <div class="text-xs font-medium text-gray-500 mb-1">DESPUÉS</div>
                            <div class="despues-contenido"></div>
                        </div>
                    </div>
                </div>
                <button class="btn-aplicar-sugerencia ml-4 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                    <i class="fas fa-check mr-1"></i>
                    Aplicar
                </button>
            </div>
        </div>
    </template>    
    </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Configurar locale español
    if (window.dayjs) {
        dayjs.locale('es');
    }
});  
// Función mejorada para actualizar estadísticas desde el componente Vue
function actualizarEstadisticas(estadisticas) {
    console.log('🔄 Actualizando estadísticas:', estadisticas);
    
    if (estadisticas) {
        // Actualizar Pendientes Hoy
        const pendientesEl = document.getElementById('stat-pendientes');
        if (pendientesEl) {
            pendientesEl.textContent = estadisticas.pendientes_hoy || 0;
            console.log('✅ Pendientes actualizados:', estadisticas.pendientes_hoy);
        } else {
            console.error('❌ Elemento stat-pendientes no encontrado');
        }
        
        // Actualizar En Progreso
        const progresoEl = document.getElementById('stat-progreso');
        if (progresoEl) {
            progresoEl.textContent = estadisticas.en_progreso_hoy || 0;
            console.log('✅ En progreso actualizados:', estadisticas.en_progreso_hoy);
        } else {
            console.error('❌ Elemento stat-progreso no encontrado');
        }
        
        // Actualizar Completadas
        const completadasEl = document.getElementById('stat-completadas');
        if (completadasEl) {
            completadasEl.textContent = estadisticas.completadas_hoy || 0;
            console.log('✅ Completadas actualizadas:', estadisticas.completadas_hoy);
        } else {
            console.error('❌ Elemento stat-completadas no encontrado');
        }
        
        // Actualizar Vencidas
        const vencidasEl = document.getElementById('stat-vencidas');
        if (vencidasEl) {
            vencidasEl.textContent = estadisticas.vencidas || 0;
            console.log('✅ Vencidas actualizadas:', estadisticas.vencidas);
        } else {
            console.error('❌ Elemento stat-vencidas no encontrado');
        }
    } else {
        console.warn('⚠️ No se recibieron estadísticas para actualizar');
    }
}

// Función simple para abrir modal de nueva tarea
function abrirModalNuevaTarea() {
    console.log('🔥 Abriendo modal de nueva tarea');
    // Acceder al componente TareaForm y mostrarlo
    const app = window.vueApp;
    if (app && app.$refs && app.$refs.tareaForm) {
        app.$refs.tareaForm.mostrarModal = true;
    }
}

window.abrirModalNuevaTarea = abrirModalNuevaTarea;

// Hacer la función global para que Vue pueda accederla
window.actualizarEstadisticas = actualizarEstadisticas;

// Test inicial para verificar que la función funciona
console.log('🧪 Función actualizarEstadisticas disponible:', typeof window.actualizarEstadisticas);

    
        // ========================================
        // 🚨 FUNCIONES DE DISTRIBUCIÓN AUTOMÁTICA
        // ========================================

        // Variables para el modal de distribución
        let modalDistribucion = null;
        let procesandoDistribucion = false;

        // Función para abrir modal de distribución automática
        function abrirDistribucionAutomatica() {
            modalDistribucion = document.getElementById('modalDistribucion');
            modalDistribucion.classList.remove('hidden');
            
            // Resetear modal a estado inicial
            document.getElementById('formDistribucion').classList.remove('hidden');
            document.getElementById('estadoExito').classList.add('hidden');
            
            // Calcular previsualización inicial
            actualizarPrevision();
            
            console.log('🚨 Abriendo modal de distribución automática');
        }

        // Función para cerrar modal de distribución automática
        function cerrarDistribucionAutomatica() {
            if (modalDistribucion) {
                modalDistribucion.classList.add('hidden');
            }
            
            // Resetear formulario
            procesandoDistribucion = false;
            document.getElementById('btnDistribuir').disabled = false;
            document.getElementById('textoBoton').textContent = 'DISTRIBUIR AHORA';
            
            console.log('❌ Cerrando modal de distribución automática');
        }

        // Función para actualizar la previsualización
        function actualizarPrevision() {
            const tareasPorDia = parseInt(document.getElementById('tareasPorDia').value);
            const diasDistribuir = parseInt(document.getElementById('diasDistribuir').value);
            const maxTareas = tareasPorDia * diasDistribuir;
            
            document.getElementById('maxTareas').textContent = maxTareas;
        }

        // Función para ejecutar la distribución
        async function ejecutarDistribucion(event) {
            event.preventDefault();
            
            if (procesandoDistribucion) return;
            
            procesandoDistribucion = true;
            const btnDistribuir = document.getElementById('btnDistribuir');
            const textoBoton = document.getElementById('textoBoton');
            
            // Cambiar estado del botón
            btnDistribuir.disabled = true;
            textoBoton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>DISTRIBUYENDO...';
            
            try {
                const tareasPorDia = parseInt(document.getElementById('tareasPorDia').value);
                const diasDistribuir = parseInt(document.getElementById('diasDistribuir').value);
                
                console.log('🚀 Iniciando distribución automática:', { tareasPorDia, diasDistribuir });
                
                const response = await axios.post('/crm-bioscom/public/api/agenda/distribuir-seguimientos', {
                    usuario_asignado_id: 1, // TODO: usar usuario actual cuando se implemente auth
                    tareas_por_dia: tareasPorDia,
                    dias_habiles: diasDistribuir
                });
                
                if (response.data.success) {
                    console.log('✅ Distribución exitosa:', response.data);
                    
                    // Mostrar estado de éxito
                    mostrarExitoDistribucion(response.data.data);
                    
                    // Recargar estadísticas después de 2 segundos
                    setTimeout(() => {
                        if (window.location.pathname.includes('/agenda/mi-dia')) {
                            location.reload();
                        }
                    }, 3000);
                    
                } else {
                    throw new Error(response.data.message || 'Error desconocido en la distribución');
                }
                
            } catch (error) {
                console.error('❌ Error en distribución automática:', error);
                
                let mensajeError = 'Error de conexión. Por favor, inténtalo de nuevo.';
                
                if (error.response) {
                    if (error.response.status === 422) {
                        // Errores de validación
                        const errores = error.response.data.errors;
                        const mensajes = Object.values(errores).flat();
                        mensajeError = `Error de validación: ${mensajes.join(', ')}`;
                    } else {
                        mensajeError = error.response.data.message || mensajeError;
                    }
                }
                
                // Mostrar error
                if (window.mostrarToast) {
                    window.mostrarToast('error', mensajeError);
                } else {
                    alert('Error: ' + mensajeError);
                }
                
                // Restaurar botón
                btnDistribuir.disabled = false;
                textoBoton.textContent = 'DISTRIBUIR AHORA';
                procesandoDistribucion = false;
            }
        }

        // Función para mostrar el estado de éxito
        function mostrarExitoDistribucion(data) {
            const formDistribucion = document.getElementById('formDistribucion');
            const estadoExito = document.getElementById('estadoExito');
            const mensajeExito = document.getElementById('mensajeExito');
            
            // Ocultar formulario y mostrar éxito
            formDistribucion.classList.add('hidden');
            estadoExito.classList.remove('hidden');
            
            // Personalizar mensaje de éxito
            const tareasCreadas = data.tareas_creadas || 0;
            const diasDistribuir = parseInt(document.getElementById('diasDistribuir').value);
            
            if (tareasCreadas === 0) {
                mensajeExito.textContent = '¡Perfecto! No hay seguimientos vencidos que distribuir. Tu agenda está al día.';
            } else {
                mensajeExito.textContent = `Se crearon ${tareasCreadas} tareas distribuidas en ${diasDistribuir} días hábiles. ¡Tu agenda está organizada!`;
            }
            
            console.log('🎉 Mostrando éxito de distribución:', data);
        }

        // Hacer funciones globales
        window.abrirDistribucionAutomatica = abrirDistribucionAutomatica;
        window.cerrarDistribucionAutomatica = cerrarDistribucionAutomatica;
        window.actualizarPrevision = actualizarPrevision;
        window.ejecutarDistribucion = ejecutarDistribucion;

        // Event listeners para actualizar previsualización
        document.addEventListener('DOMContentLoaded', function() {
            const tareasPorDiaSelect = document.getElementById('tareasPorDia');
            const diasDistribuirSelect = document.getElementById('diasDistribuir');
            
            if (tareasPorDiaSelect) {
                tareasPorDiaSelect.addEventListener('change', actualizarPrevision);
            }
            
            if (diasDistribuirSelect) {
                diasDistribuirSelect.addEventListener('change', actualizarPrevision);
            }
        });
        // ==========================================
        // 🧠 SISTEMA DE ANÁLISIS INTELIGENTE
        // ==========================================

        // Variables globales para análisis inteligente
        let datosAnalisisActual = null;
        let modalSugerencias = null;

        // ==========================================
        // 🔍 FUNCIONES DE ANÁLISIS Y DETECCIÓN
        // ==========================================

        // Función principal para cargar análisis inteligente
        async function cargarAnalisisInteligente() {
            try {
                console.log('🧠 Iniciando análisis inteligente...');
                
                const response = await axios.get('/crm-bioscom/public/api/agenda/analizar-carga');
                
                if (response.data.success) {
                    datosAnalisisActual = response.data.data;
                    console.log('✅ Análisis completado:', datosAnalisisActual);
                    
                    // Mostrar widget inteligente apropiado
                    mostrarWidgetInteligente(datosAnalisisActual);
                } else {
                    console.warn('⚠️ Error en análisis:', response.data.message);
                }
            } catch (error) {
                console.error('❌ Error cargando análisis:', error);
            }
        }

        // Mostrar el widget inteligente apropiado
        function mostrarWidgetInteligente(datos) {
            const widget = document.getElementById('widgetAnalisisInteligente');
            const estadoNormal = document.getElementById('estadoNormal');
            const alertaSobrecarga = document.getElementById('alertaSobrecarga');
            const alertaModerada = document.getElementById('alertaModerada');
            
            // Ocultar todos los estados
            estadoNormal.classList.add('hidden');
            alertaSobrecarga.classList.add('hidden');
            alertaModerada.classList.add('hidden');
            
            const { alertas, resumen } = datos;
            
            if (alertas.length === 0) {
                // 🟢 Estado normal - todo bien
                estadoNormal.classList.remove('hidden');
                document.getElementById('mensajeNormal').textContent = 
                    `Tu agenda está balanceada. ${resumen.total_tareas_semana} tareas distribuidas correctamente.`;
            } else {
                // Verificar si hay alertas de sobrecarga
                const alertasSobrecarga = alertas.filter(a => a.tipo === 'sobrecarga');
                
                if (alertasSobrecarga.length > 0) {
                    // 🔴 Alerta de sobrecarga crítica
                    alertaSobrecarga.classList.remove('hidden');
                    document.getElementById('mensajeSobrecarga').textContent = 
                        `${alertasSobrecarga.length} día(s) con sobrecarga detectada. Necesitas redistribuir tareas.`;
                } else {
                    // 🟡 Alerta moderada
                    alertaModerada.classList.remove('hidden');
                    document.getElementById('mensajeModerado').textContent = 
                        `${resumen.dias_alta_carga} día(s) con carga alta. Podrías optimizar tu agenda.`;
                }
            }
            
            // Mostrar el widget
            widget.classList.remove('hidden');
        }

// ==========================================
// 🎯 MODAL DE SUGERENCIAS INTELIGENTES
// ==========================================

// Abrir modal de sugerencias inteligentes
function abrirSugerenciasInteligentes() {
    modalSugerencias = document.getElementById('modalSugerencias');
    modalSugerencias.classList.remove('hidden');
    
    // Mostrar loading
    document.getElementById('loadingSugerencias').classList.remove('hidden');
    document.getElementById('contenidoSugerencias').classList.add('hidden');
    document.getElementById('botonesSugerencias').classList.add('hidden');
    
    console.log('🎯 Abriendo modal de sugerencias inteligentes');
    
    // Cargar contenido del modal
    setTimeout(() => {
        cargarContenidoSugerencias();
    }, 1000); // Dar tiempo para que se vea el loading
}

// Cerrar modal de sugerencias inteligentes
function cerrarSugerenciasInteligentes() {
    if (modalSugerencias) {
        modalSugerencias.classList.add('hidden');
    }
    console.log('❌ Cerrando modal de sugerencias');
}

// Cargar contenido del modal con análisis
function cargarContenidoSugerencias() {
    if (!datosAnalisisActual) {
        console.error('❌ No hay datos de análisis disponibles');
        return;
    }
    
    const { analisis_semanal, alertas, sugerencias } = datosAnalisisActual;
    
    // Ocultar loading
    document.getElementById('loadingSugerencias').classList.add('hidden');
    document.getElementById('contenidoSugerencias').classList.remove('hidden');
    document.getElementById('botonesSugerencias').classList.remove('hidden');
    
    // Generar diagnóstico visual semanal (SIN TEMPLATES)
    function generarDiagnosticoSemanal(analisisSemanal) {
        const contenedor = document.getElementById('diagnosticoSemanal');
        
        if (!contenedor) {
            console.error('❌ Contenedor diagnosticoSemanal no encontrado');
            return;
        }
        
        contenedor.innerHTML = '';
        
        Object.values(analisisSemanal).forEach(dia => {
            // Crear elemento dinámicamente
            const divDia = document.createElement('div');
            divDia.className = 'text-center p-2 rounded-lg border';
            
            // Configurar colores según nivel de carga
            if (dia.nivel_carga === 'sobrecarga') {
                divDia.classList.add('bg-red-100', 'border-red-300', 'text-red-800');
            } else if (dia.nivel_carga === 'alta') {
                divDia.classList.add('bg-yellow-100', 'border-yellow-300', 'text-yellow-800');
            } else {
                divDia.classList.add('bg-green-100', 'border-green-300', 'text-green-800');
            }
            
            // Resaltar día actual
            if (dia.es_hoy) {
                divDia.classList.add('ring-2', 'ring-blue-400');
            }
            
            // Configurar contenido
            divDia.innerHTML = `
                <div class="text-xs font-medium text-current mb-1">${dia.dia_nombre.substring(0, 3).toUpperCase()}</div>
                <div class="text-lg font-bold mb-1">${dia.cantidad_tareas}</div>
                <div class="text-xs text-current">tareas</div>
            `;
            
            contenedor.appendChild(divDia);
        });
        
        console.log('✅ Diagnóstico semanal generado');
    }
    
    // Mostrar alertas si las hay
    if (alertas.length > 0) {
        mostrarAlertas(alertas);
    }
    
    // Mostrar sugerencias si las hay
    if (sugerencias.length > 0) {
        mostrarSugerencias(sugerencias);
        document.getElementById('btnAplicarTodo').classList.remove('hidden');
    } else {
        // Mostrar estado saludable
        document.getElementById('estadoSaludable').classList.remove('hidden');
    }
}

// Generar diagnóstico visual semanal
function generarDiagnosticoSemanal(analisisSemanal) {
    const contenedor = document.getElementById('diagnosticoSemanal');
    const template = document.getElementById('templateDiaDiagnostico');
    
    contenedor.innerHTML = '';
    
    Object.values(analisisSemanal).forEach(dia => {
        const elemento = template.content.cloneNode(true);
        const divDia = elemento.querySelector('div');
        
        // Configurar contenido
        divDia.querySelector('.text-xs.font-medium').textContent = dia.dia_nombre.substring(0, 3).toUpperCase();
        divDia.querySelector('.text-lg.font-bold').textContent = dia.cantidad_tareas;
        divDia.querySelector('.text-xs:last-child').textContent = 'tareas';
        
        // Configurar colores según nivel de carga
        if (dia.nivel_carga === 'sobrecarga') {
            divDia.classList.add('bg-red-100', 'border-red-300', 'text-red-800');
        } else if (dia.nivel_carga === 'alta') {
            divDia.classList.add('bg-yellow-100', 'border-yellow-300', 'text-yellow-800');
        } else {
            divDia.classList.add('bg-green-100', 'border-green-300', 'text-green-800');
        }
        
        // Resaltar día actual
        if (dia.es_hoy) {
            divDia.classList.add('ring-2', 'ring-blue-400');
        }
        
        contenedor.appendChild(elemento);
    });
}

// Mostrar alertas críticas
// Mostrar alertas críticas (SIN TEMPLATES)
function mostrarAlertas(alertas) {
    const seccion = document.getElementById('seccionAlertas');
    const lista = document.getElementById('listaAlertas');
    
    if (!lista) return;
    
    lista.innerHTML = '';
    
    alertas.forEach(alerta => {
        const divAlerta = document.createElement('div');
        divAlerta.className = 'p-3 rounded-lg border-l-4';
        
        // Configurar colores según prioridad
        if (alerta.prioridad === 'urgente') {
            divAlerta.classList.add('bg-red-50', 'border-red-400', 'text-red-800');
        } else if (alerta.prioridad === 'alta') {
            divAlerta.classList.add('bg-orange-50', 'border-orange-400', 'text-orange-800');
        } else {
            divAlerta.classList.add('bg-yellow-50', 'border-yellow-400', 'text-yellow-800');
        }
        
        divAlerta.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span class="font-medium">${alerta.mensaje}</span>
            </div>
        `;
        
        lista.appendChild(divAlerta);
    });
    
    seccion.classList.remove('hidden');
}

// Mostrar sugerencias (SIMPLIFICADO)
    function mostrarSugerencias(sugerencias) {
        const seccion = document.getElementById('seccionSugerencias');
        const lista = document.getElementById('listaSugerencias');
        
        if (!lista) return;
        
        lista.innerHTML = '';
        
        sugerencias.forEach((sugerencia, index) => {
            const divSugerencia = document.createElement('div');
            divSugerencia.className = 'bg-blue-50 rounded-lg border border-blue-200 p-4';
            
            const impacto = sugerencia.impacto;
            divSugerencia.innerHTML = `
                <h5 class="font-semibold text-blue-900 mb-2">
                    <i class="fas fa-arrows-alt mr-2"></i>
                    Redistribuir ${sugerencia.cantidad_tareas} tareas
                </h5>
                <p class="text-sm text-blue-700 mb-3">${sugerencia.beneficio}</p>
                
                <div class="grid grid-cols-2 gap-4 mb-3">
                    <div class="bg-white rounded p-2 border">
                        <div class="text-xs font-medium text-gray-500 mb-1">ANTES</div>
                        <div class="text-red-600 font-bold">${impacto.origen_antes} tareas</div>
                        <div class="text-xs text-gray-500">Sobrecargado</div>
                    </div>
                    <div class="bg-white rounded p-2 border">
                        <div class="text-xs font-medium text-gray-500 mb-1">DESPUÉS</div>
                        <div class="text-green-600 font-bold">${impacto.origen_despues} tareas</div>
                        <div class="text-xs text-gray-500">Balanceado</div>
                    </div>
                </div>
                
                <button onclick="aplicarSugerencia(${index})" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                    <i class="fas fa-check mr-1"></i>
                    Aplicar Optimización
                </button>
            `;
            
            lista.appendChild(divSugerencia);
        });
        
        seccion.classList.remove('hidden');
    }

    // ==========================================
    // ⚡ APLICACIÓN DE OPTIMIZACIONES
    // ==========================================

    // Aplicar una sugerencia específica
    async function aplicarSugerencia(index) {
        if (!datosAnalisisActual || !datosAnalisisActual.sugerencias[index]) {
            console.error('❌ Sugerencia no encontrada');
            return;
        }
        
        const sugerencia = datosAnalisisActual.sugerencias[index];
        console.log('⚡ Aplicando sugerencia:', sugerencia);
        
        try {
            // Aplicar cada tarea individualmente
            for (const tareaId of sugerencia.tareas_a_mover) {
                await axios.put(`/crm-bioscom/public/api/agenda/tareas/${tareaId}`, {
                    fecha_vencimiento: sugerencia.dia_destino
                });
            }
            
            if (window.mostrarToast) {
                window.mostrarToast('success', `¡Optimización aplicada! ${sugerencia.cantidad_tareas} tareas redistribuidas.`);
            }
            
            // Cerrar modal y recargar página
            cerrarSugerenciasInteligentes();
            setTimeout(() => location.reload(), 1000);
            
        } catch (error) {
            console.error('❌ Error aplicando sugerencia:', error);
            if (window.mostrarToast) {
                window.mostrarToast('error', 'Error al aplicar la optimización');
            }
        }
    }

    // Aplicar todas las sugerencias
    async function aplicarTodasLasSugerencias() {
        if (!datosAnalisisActual || !datosAnalisisActual.sugerencias.length) {
            return;
        }
        
        if (!confirm('¿Estás seguro de aplicar todas las optimizaciones? Esta acción reorganizará tu agenda automáticamente.')) {
            return;
        }
        
        console.log('⚡ Aplicando todas las sugerencias...');
        
        try {
            for (let i = 0; i < datosAnalisisActual.sugerencias.length; i++) {
                await aplicarSugerencia(i);
            }
        } catch (error) {
            console.error('❌ Error aplicando todas las sugerencias:', error);
        }
    }

    // ==========================================
    // 🚀 INICIALIZACIÓN AUTOMÁTICA
    // ==========================================

    // Hacer funciones globales
    window.abrirSugerenciasInteligentes = abrirSugerenciasInteligentes;
    window.cerrarSugerenciasInteligentes = cerrarSugerenciasInteligentes;
    window.aplicarSugerencia = aplicarSugerencia;
    window.aplicarTodasLasSugerencias = aplicarTodasLasSugerencias;
    window.cargarAnalisisInteligente = cargarAnalisisInteligente;

    // Ejecutar análisis automáticamente cuando se carga la página
    document.addEventListener('DOMContentLoaded', function() {
        // Esperar a que Vue termine de cargar las estadísticas básicas
        setTimeout(() => {
            cargarAnalisisInteligente();
        }, 2000);
    });

</script>
@endpush