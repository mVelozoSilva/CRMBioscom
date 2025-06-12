@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50" id="dashboard-app">
    <!-- ENCABEZADO PERSONALIZADO -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ $dashboardData['saludo'] }}, {{ $dashboardData['usuario'] }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ $dashboardData['rol'] }} • {{ $dashboardData['fecha_actual'] }}
                            @if($dashboardData['ultimo_acceso'])
                                • Último acceso: {{ $dashboardData['ultimo_acceso'] }}
                            @endif
                        </p>
                    </div>
                    
                    <!-- ACCIONES RÁPIDAS -->
                    <div class="flex items-center space-x-3">
                        @if(auth()->user()->esVendedor() || auth()->user()->esJefe())
                            <a href="{{ route('cotizaciones.create') }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Nueva Cotización
                            </a>
                        @endif
                        
                        <a href="{{ route('agenda.mi-dia') }}" 
                           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            Mi Agenda
                        </a>
                        
                        <button 
                            onclick="actualizarDashboard()"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                        >
                            <i class="fas fa-sync-alt mr-2"></i>
                            Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <!-- ALERTAS CRÍTICAS - ROADMAP: Visibilidad inmediata -->
        @if(!empty($dashboardData['alertas']['rojas']) || !empty($dashboardData['alertas']['amarillas']))
        <div class="mb-6 space-y-4">
            
            <!-- ALERTAS ROJAS (CRÍTICAS) -->
            @if(!empty($dashboardData['alertas']['rojas']))
            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            Atención Inmediata Requerida
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($dashboardData['alertas']['rojas'] as $alerta)
                                <li>
                                    <a href="{{ $alerta['url'] }}" class="hover:underline font-medium">
                                        {{ $alerta['titulo'] }}: {{ $alerta['cantidad'] }} 
                                        {{ $alerta['mensaje'] }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- ALERTAS AMARILLAS (PRÓXIMAS A VENCER) -->
            @if(!empty($dashboardData['alertas']['amarillas']))
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                            Próximas Acciones
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($dashboardData['alertas']['amarillas'] as $alerta)
                                <li>
                                    <a href="{{ $alerta['url'] }}" class="hover:underline">
                                        {{ $alerta['titulo'] }}: {{ $alerta['cantidad'] }} 
                                        {{ $alerta['mensaje'] }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- SECCIÓN "MI DÍA HOY" - ROADMAP: Panel de carga de trabajo -->
        <div class="mb-8">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-sun text-yellow-500 mr-2"></i>
                Mi Día Hoy
            </h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- RESUMEN DEL DÍA -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-4">Resumen del Día</h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Tareas Pendientes</span>
                            <span class="text-lg font-semibold {{ $dashboardData['mi_dia']['tareas_urgentes'] > 0 ? 'text-red-600' : 'text-blue-600' }}">
                                {{ $dashboardData['mi_dia']['tareas_pendientes'] }}
                                @if($dashboardData['mi_dia']['tareas_urgentes'] > 0)
                                    <span class="text-xs text-red-500">({{ $dashboardData['mi_dia']['tareas_urgentes'] }} urgentes)</span>
                                @endif
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Seguimientos Hoy</span>
                            <span class="text-lg font-semibold text-green-600">
                                {{ $dashboardData['mi_dia']['seguimientos_hoy'] }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Tiempo Estimado</span>
                            <span class="text-lg font-semibold {{ $dashboardData['mi_dia']['sobrecargado'] ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $dashboardData['mi_dia']['duracion_estimada_horas'] }}h
                                @if($dashboardData['mi_dia']['sobrecargado'])
                                    <i class="fas fa-exclamation-triangle text-red-500 ml-1" title="Día sobrecargado"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('agenda.mi-dia') }}" 
                           class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Ver agenda completa <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                <!-- PRÓXIMAS TAREAS -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-4">Próximas Tareas</h3>
                    
                    @if(!empty($dashboardData['mi_dia']['tareas_detalle']) && count($dashboardData['mi_dia']['tareas_detalle']) > 0)
                        <div class="space-y-3">
                            @foreach($dashboardData['mi_dia']['tareas_detalle'] as $tarea)
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-2 h-2 rounded-full mt-2 {{ 
                                        $tarea['prioridad'] === 'urgente' ? 'bg-red-500' : 
                                        ($tarea['prioridad'] === 'alta' ? 'bg-orange-500' : 
                                        ($tarea['prioridad'] === 'media' ? 'bg-blue-500' : 'bg-gray-500'))
                                    }}"></div>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $tarea['titulo'] }}</p>
                                    <div class="mt-1 text-xs text-gray-500">
                                        @if($tarea['hora_inicio'])
                                            {{ $tarea['hora_inicio'] }}
                                        @endif
                                        @if($tarea['cliente'])
                                            • {{ $tarea['cliente'] }}
                                        @endif
                                        @if($tarea['duracion_estimada'])
                                            • {{ $tarea['duracion_estimada'] }}min
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-calendar-check text-3xl mb-2"></i>
                            <p class="text-sm">No tienes tareas programadas para hoy</p>
                        </div>
                    @endif
                </div>

                <!-- SEGUIMIENTOS DEL DÍA -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-4">Seguimientos de Hoy</h3>
                    
                    @if(!empty($dashboardData['mi_dia']['seguimientos_detalle']) && count($dashboardData['mi_dia']['seguimientos_detalle']) > 0)
                        <div class="space-y-3">
                            @foreach($dashboardData['mi_dia']['seguimientos_detalle'] as $seguimiento)
                            <div class="border-l-2 border-blue-200 pl-3">
                                <p class="text-sm font-medium text-gray-900">{{ $seguimiento['cliente'] }}</p>
                                @if($seguimiento['cotizacion'])
                                    <p class="text-xs text-blue-600">{{ $seguimiento['cotizacion'] }}</p>
                                @endif
                                @if($seguimiento['notas'])
                                    <p class="text-xs text-gray-500 mt-1">{{ $seguimiento['notas'] }}</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-phone text-3xl mb-2"></i>
                            <p class="text-sm">No tienes seguimientos para hoy</p>
                        </div>
                    @endif
                    
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('seguimiento.index', ['clasificacion' => 'hoy']) }}" 
                           class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Ver todos los seguimientos <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- MÉTRICAS ESPECÍFICAS POR ROL -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- MÉTRICAS DE VENTAS (Vendedores y Jefes) -->
            @if(isset($dashboardData['ventas']))
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-chart-line text-green-500 mr-2"></i>
                        Rendimiento de Ventas
                    </h2>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">
                                {{ $dashboardData['ventas']['cotizaciones_mes'] }}
                            </div>
                            <div class="text-xs text-gray-500">Cotizaciones del mes</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">
                                {{ $dashboardData['ventas']['cotizaciones_ganadas_mes'] }}
                            </div>
                            <div class="text-xs text-gray-500">Ganadas</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">
                                {{ $dashboardData['ventas']['tasa_conversion_mes'] }}%
                            </div>
                            <div class="text-xs text-gray-500">Tasa de conversión</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-lg font-bold text-gray-900">
                                {{ $dashboardData['ventas']['valor_vendido_formateado'] }}
                            </div>
                            <div class="text-xs text-gray-500">Valor vendido</div>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Seguimientos Pendientes:</span>
                            <span class="font-medium">{{ $dashboardData['ventas']['seguimientos_pendientes'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600">Cotizaciones Activas:</span>
                            <span class="font-medium">{{ $dashboardData['ventas']['cotizaciones_activas'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- MÉTRICAS DE EQUIPO (Jefes y Administradores) -->
            @if(isset($dashboardData['equipo']))
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-users text-blue-500 mr-2"></i>
                        Rendimiento del Equipo
                    </h2>
                </div>
                
                <div class="p-6">
                    @if(isset($dashboardData['equipo']['mensaje']))
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-info-circle text-3xl mb-2"></i>
                            <p class="text-sm">{{ $dashboardData['equipo']['mensaje'] }}</p>
                        </div>
                    @else
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">
                                    {{ $dashboardData['equipo']['vendedores_activos'] }}
                                </div>
                                <div class="text-xs text-gray-500">Vendedores activos</div>
                            </div>
                            
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">
                                    {{ $dashboardData['equipo']['cotizaciones_equipo_mes'] }}
                                </div>
                                <div class="text-xs text-gray-500">Cotizaciones del equipo</div>
                            </div>
                        </div>
                        
                        <div class="text-center mb-6">
                            <div class="text-lg font-bold text-gray-900">
                                ${{ number_format($dashboardData['equipo']['valor_equipo_mes'], 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-gray-500">Valor vendido del equipo</div>
                        </div>
                        
                        @if($dashboardData['equipo']['seguimientos_atrasados_equipo'] > 0)
                        <div class="bg-red-50 rounded-lg p-3 mb-4">
                            <div class="text-center">
                                <div class="text-lg font-bold text-red-600">
                                    {{ $dashboardData['equipo']['seguimientos_atrasados_equipo'] }}
                                </div>
                                <div class="text-xs text-red-600">Seguimientos atrasados del equipo</div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Top vendedores -->
                        @if(isset($dashboardData['equipo']['top_vendedores_mes']) && count($dashboardData['equipo']['top_vendedores_mes']) > 0)
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Top Vendedores del Mes</h4>
                            <div class="space-y-2">
                                @foreach($dashboardData['equipo']['top_vendedores_mes'] as $index => $vendedor)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold mr-2">
                                            {{ $index + 1 }}
                                        </div>
                                        <span class="text-sm text-gray-900">{{ $vendedor->vendedor->name ?? 'N/A' }}</span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-600">
                                        ${{ number_format($vendedor->valor_total, 0, ',', '.') }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
            @endif

            <!-- MÉTRICAS GENERALES (Administradores) -->
            @if(isset($dashboardData['general']))
            <div class="bg-white rounded-lg shadow lg:col-span-2">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-tachometer-alt text-purple-500 mr-2"></i>
                        Métricas Generales del Sistema
                    </h2>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">
                                {{ $dashboardData['general']['total_usuarios'] }}
                            </div>
                            <div class="text-xs text-gray-500">Usuarios activos</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">
                                {{ $dashboardData['general']['total_clientes'] }}
                            </div>
                            <div class="text-xs text-gray-500">Clientes registrados</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">
                                {{ $dashboardData['general']['cotizaciones_mes'] }}
                            </div>
                            <div class="text-xs text-gray-500">Cotizaciones del mes</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-lg font-bold text-gray-900">
                                ${{ number_format($dashboardData['general']['valor_total_mes'], 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-gray-500">Valor total mes</div>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Estado del Sistema</h4>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-lg font-bold text-red-600">
                                    {{ $dashboardData['general']['seguimientos_sistema']['atrasados'] }}
                                </div>
                                <div class="text-xs text-gray-500">Seguimientos atrasados</div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-yellow-600">
                                    {{ $dashboardData['general']['seguimientos_sistema']['hoy'] }}
                                </div>
                                <div class="text-xs text-gray-500">Para hoy</div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-blue-600">
                                    {{ $dashboardData['general']['seguimientos_sistema']['activos'] }}
                                </div>
                                <div class="text-xs text-gray-500">Activos totales</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>

        <!-- MOTIVACIÓN Y CONTEXTO -->
        @if(isset($dashboardData['metricas_mes']))
        <div class="mt-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-lg text-white">
            <div class="p-6">
                <h2 class="text-lg font-medium mb-4">
                    <i class="fas fa-trophy mr-2"></i>
                    Tu Progreso Este Mes
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold">{{ $dashboardData['metricas_mes']['logros_ayer']['tareas_completadas'] }}</div>
                        <div class="text-xs opacity-90">Tareas completadas ayer</div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-2xl font-bold">{{ $dashboardData['metricas_mes']['logros_ayer']['seguimientos_realizados'] }}</div>
                        <div class="text-xs opacity-90">Seguimientos realizados ayer</div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-2xl font-bold">{{ $dashboardData['metricas_mes']['racha_dias_productivos'] }}</div>
                        <div class="text-xs opacity-90">Días productivos consecutivos</div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-2xl font-bold">
                            @if($dashboardData['metricas_mes']['comparacion_mes_anterior']['tendencia'] === 'positiva')
                                <i class="fas fa-arrow-up text-green-300"></i>
                                +{{ $dashboardData['metricas_mes']['comparacion_mes_anterior']['porcentaje_cambio'] }}%
                            @elseif($dashboardData['metricas_mes']['comparacion_mes_anterior']['tendencia'] === 'negativa')
                                <i class="fas fa-arrow-down text-red-300"></i>
                                {{ $dashboardData['metricas_mes']['comparacion_mes_anterior']['porcentaje_cambio'] }}%
                            @else
                                <i class="fas fa-minus text-gray-300"></i>
                                0%
                            @endif
                        </div>
                        <div class="text-xs opacity-90">vs. mes anterior</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-actualización cada 5 minutos
    setInterval(actualizarDashboard, 300000);
    
    // Actualización de la hora cada minuto
    setInterval(actualizarHora, 60000);
});

function actualizarDashboard() {
    // Mostrar indicador de carga
    const button = document.querySelector('button[onclick="actualizarDashboard()"]');
    const icon = button.querySelector('i');
    const originalClass = icon.className;
    
    icon.className = 'fas fa-spinner fa-spin mr-2';
    button.disabled = true;
    
    // Hacer petición AJAX para actualizar datos
    fetch('/dashboard', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Aquí podrías actualizar dinámicamente partes específicas del dashboard
            // Por ahora, simplemente recargar la página
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error al actualizar dashboard:', error);
    })
    .finally(() => {
        icon.className = originalClass;
        button.disabled = false;
    });
}

function actualizarHora() {
    // Actualizar timestamp si existe
    const timestampElement = document.querySelector('.timestamp');
    if (timestampElement) {
        timestampElement.textContent = new Date().toLocaleString('es-CL');
    }
}

// Hacer funciones disponibles globalmente
window.actualizarDashboard = actualizarDashboard;
</script>
@endpush

@push('styles')
<style>
/* Animaciones y efectos para el dashboard */
.dashboard-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.dashboard-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

/* Pulso para elementos críticos */
.pulso-rojo {
    animation: pulso-rojo 2s infinite;
}

@keyframes pulso-rojo {
    0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
    100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

/* Mejoras responsive */
@media (max-width: 768px) {
    .grid.grid-cols-2 {
        grid-template-columns: 1fr;
    }
    
    .hidden.sm\\:block {
        display: none !important;
    }
}

/* Gradientes personalizados */
.bg-gradient-bioscom {
    background: linear-gradient(135deg, #6284b8 0%, #5f87b8 100%);
}

/* Estados de elementos */
.estado-critico {
    background-color: #fef2f2;
    border-color: #fecaca;
    color: #dc2626;
}

.estado-warning {
    background-color: #fffbeb;
    border-color: #fed7aa;
    color: #d97706;
}

.estado-success {
    background-color: #f0fdf4;
    border-color: #bbf7d0;
    color: #16a34a;
}
</style>
@endpush