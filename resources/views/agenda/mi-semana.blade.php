@extends('layouts.app')

@section('title', 'Mi Semana - Agenda CRM Bioscom')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header con Control de Semana -->
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-calendar-week text-blue-600 mr-3"></i>
                    Mi Semana
                    <span class="ml-3 text-lg font-normal text-gray-500">
                        {{ $fechaInicio->format('d/m') }} - {{ $fechaFin->format('d/m/Y') }}
                    </span>
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Planifica y gestiona tu agenda semanal de manera estratégica
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                <!-- Control de Semana -->
                <div class="flex items-center space-x-2">
                    @php
                        $semanaAnterior = $fechaInicio->copy()->subWeek()->format('Y-W');
                        $semanaSiguiente = $fechaInicio->copy()->addWeek()->format('Y-W');
                    @endphp
                    <a href="{{ request()->fullUrlWithQuery(['semana' => $semanaAnterior]) }}" 
                       class="p-2 border border-gray-300 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <select id="semana-selector" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @for($i = -4; $i <= 8; $i++)
                            @php
                                $fecha = now()->addWeeks($i);
                                $valor = $fecha->format('Y-W');
                                $texto = $fecha->startOfWeek()->format('d/m') . ' - ' . $fecha->endOfWeek()->format('d/m');
                            @endphp
                            <option value="{{ $valor }}" {{ $semana === $valor ? 'selected' : '' }}>
                                @if($i === 0) Semana Actual ({{ $texto }})
                                @elseif($i === -1) Semana Pasada ({{ $texto }})
                                @elseif($i === 1) Próxima Semana ({{ $texto }})
                                @else Semana {{ $texto }}
                                @endif
                            </option>
                        @endfor
                    </select>
                    <a href="{{ request()->fullUrlWithQuery(['semana' => $semanaSiguiente]) }}" 
                       class="p-2 border border-gray-300 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
                
                <button onclick="abrirModalNuevaTarea()" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-plus mr-2"></i>
                    Nueva Tarea
                </button>
                
                <a href="{{ route('agenda.mi-dia') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-calendar-day mr-2"></i>
                    Vista Diaria
                </button>
            </div>
        </div>

        <!-- Estadísticas de la Semana -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-tasks text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 truncate">Total Tareas</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $estadisticasSemana['total_tareas'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-check-circle text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 truncate">Completadas</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $estadisticasSemana['completadas'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 truncate">Días Activos</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $estadisticasSemana['dias_con_tareas'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-stopwatch text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 truncate">Carga Total</p>
                            <p class="text-2xl font-semibold text-gray-900">
                                {{ intval($estadisticasSemana['carga_trabajo_total'] / 60) }}h
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendario Semanal -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Calendario Semanal</h3>
                    <div class="flex items-center space-x-4">
                        <!-- Leyenda de Capacidad -->
                        <div class="flex items-center space-x-2 text-sm">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-200 rounded mr-1"></div>
                                <span class="text-gray-600">Normal</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-yellow-300 rounded mr-1"></div>
                                <span class="text-gray-600">Alto</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-red-300 rounded mr-1"></div>
                                <span class="text-gray-600">Sobrecargado</span>
                            </div>
                        </div>
                        
                        <!-- Controles de Vista -->
                        <div class="flex space-x-2">
                            <button id="vista-compacta" 
                                    class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50"
                                    onclick="cambiarVista('compacta')">
                                Compacta
                            </button>
                            <button id="vista-detallada" 
                                    class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 bg-blue-50 border-blue-300"
                                    onclick="cambiarVista('detallada')">
                                Detallada
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid del Calendario -->
            <div class="grid grid-cols-7 gap-0 border-b border-gray-200">
                <!-- Headers de Días -->
                @foreach($diasSemana as $dia)
                    <div class="p-4 border-r border-gray-200 bg-gray-50 {{ $dia['es_hoy'] ? 'bg-blue-50' : '' }}">
                        <div class="text-center">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $dia['fecha']->format('l') }}
                                @if($dia['es_hoy'])
                                    <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        Hoy
                                    </span>
                                @endif
                            </div>
                            <div class="text-lg font-bold text-gray-900 mt-1">
                                {{ $dia['fecha']->format('d') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $dia['fecha']->format('M') }}
                            </div>
                            
                            <!-- Indicador de Carga -->
                            <div class="mt-2">
                                @php
                                    $porcentajeCarga = min(100, ($dia['carga_trabajo'] / $capacidadDiaria) * 100);
                                    $colorCarga = $porcentajeCarga <= 60 ? 'bg-green-400' : ($porcentajeCarga <= 80 ? 'bg-yellow-400' : 'bg-red-400');
                                @endphp
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="{{ $colorCarga }} h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ $porcentajeCarga }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ intval($dia['carga_trabajo'] / 60) }}h {{ $dia['carga_trabajo'] % 60 }}m
                                </div>
                            </div>

                            <!-- Resumen de Tareas -->
                            <div class="mt-2 text-xs">
                                <div class="text-gray-600">{{ $dia['tareas']->count() }} tareas</div>
                                @if($dia['tareas']->where('estado', 'completada')->count() > 0)
                                    <div class="text-green-600">
                                        {{ $dia['tareas']->where('estado', 'completada')->count() }} completadas
                                    </div>
                                @endif
                                @if($dia['tareas']->where('es_vencida', true)->count() > 0)
                                    <div class="text-red-600">
                                        {{ $dia['tareas']->where('es_vencida', true)->count() }} vencidas
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Contenido de Tareas por Día -->
            <div class="grid grid-cols-7 gap-0" id="calendario-contenido">
                @foreach($diasSemana as $dia)
                    <div class="min-h-96 p-3 border-r border-gray-200 bg-white {{ $dia['es_hoy'] ? 'bg-blue-25' : '' }} {{ $dia['es_fin_semana'] ? 'bg-gray-25' : '' }}" 
                         data-fecha="{{ $dia['fecha_str'] }}"
                         ondrop="drop(event)" 
                         ondragover="allowDrop(event)">
                        
                        <!-- Botón Agregar Tarea Rápida -->
                        <button onclick="agregarTareaRapida('{{ $dia['fecha_str'] }}')"
                                class="w-full mb-3 p-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-400 hover:border-blue-400 hover:text-blue-600 transition-colors text-sm">
                            <i class="fas fa-plus mr-1"></i>
                            Agregar Tarea
                        </button>

                        <!-- Lista de Tareas del Día -->
                        <div class="space-y-2" id="tareas-{{ $dia['fecha_str'] }}">
                            @foreach($dia['tareas'] as $tarea)
                                <div class="tarea-card bg-white border border-gray-200 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow cursor-pointer"
                                     data-tarea-id="{{ $tarea->id }}"
                                     draggable="true"
                                     ondragstart="drag(event)"
                                     onclick="abrirDetallesTarea({{ $tarea->id }})">
                                    
                                    <!-- Header de la Tarea -->
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex items-center space-x-2">
                                            <!-- Checkbox -->
                                            <input type="checkbox" 
                                                   {{ $tarea->estado === 'completada' ? 'checked' : '' }}
                                                   onclick="event.stopPropagation(); cambiarEstadoTarea({{ $tarea->id }}, this.checked)"
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            
                                            <!-- Prioridad -->
                                            <div class="w-2 h-2 rounded-full
                                                {{ $tarea->color_prioridad === 'red' ? 'bg-red-500' : '' }}
                                                {{ $tarea->color_prioridad === 'orange' ? 'bg-orange-500' : '' }}
                                                {{ $tarea->color_prioridad === 'yellow' ? 'bg-yellow-500' : '' }}
                                                {{ $tarea->color_prioridad === 'green' ? 'bg-green-500' : '' }}">
                                            </div>
                                        </div>
                                        
                                        <!-- Menú de Acciones -->
                                        <div class="relative">
                                            <button onclick="event.stopPropagation(); toggleMenuTarea({{ $tarea->id }})"
                                                    class="text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-ellipsis-v text-xs"></i>
                                            </button>
                                            <div id="menu-{{ $tarea->id }}" 
                                                 class="hidden absolute right-0 mt-1 w-32 bg-white border border-gray-200 rounded-md shadow-lg z-10">
                                                <div class="py-1">
                                                    <button onclick="editarTarea({{ $tarea->id }})"
                                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        Editar
                                                    </button>
                                                    <button onclick="duplicarTarea({{ $tarea->id }})"
                                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        Duplicar
                                                    </button>
                                                    @if($tarea->estado !== 'completada')
                                                        <button onclick="completarTarea({{ $tarea->id }})"
                                                                class="block w-full text-left px-4 py-2 text-sm text-green-700 hover:bg-green-100">
                                                            Completar
                                                        </button>
                                                    @endif
                                                    <button onclick="eliminarTarea({{ $tarea->id }})"
                                                            class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-100">
                                                        Eliminar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Título de la Tarea -->
                                    <h4 class="text-sm font-medium text-gray-900 {{ $tarea->estado === 'completada' ? 'line-through text-gray-500' : '' }} mb-1">
                                        {{ Str::limit($tarea->titulo, 40) }}
                                    </h4>

                                    <!-- Información Adicional -->
                                    <div class="text-xs text-gray-500 space-y-1">
                                        <!-- Hora -->
                                        @if($tarea->hora_inicio)
                                            <div class="flex items-center">
                                                <i class="fas fa-clock mr-1"></i>
                                                {{ Carbon::parse($tarea->hora_inicio)->format('H:i') }}
                                                @if($tarea->duracion_estimada)
                                                    <span class="ml-1">({{ $tarea->duracion_formateada }})</span>
                                                @endif
                                            </div>
                                        @endif

                                        <!-- Tipo y Cliente -->
                                        <div class="flex items-center justify-between">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium
                                                {{ $tarea->tipo === 'seguimiento' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $tarea->tipo === 'cotizacion' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $tarea->tipo === 'reunion' ? 'bg-purple-100 text-purple-800' : '' }}
                                                {{ $tarea->tipo === 'llamada' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ !in_array($tarea->tipo, ['seguimiento', 'cotizacion', 'reunion', 'llamada']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                                {{ $tarea->tipo_humano }}
                                            </span>
                                            
                                            @if($tarea->cliente)
                                                <span class="text-xs text-gray-600">
                                                    {{ Str::limit($tarea->cliente->nombre_institucion, 15) }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Estado -->
                                        @if($tarea->estado !== 'pendiente')
                                            <div class="flex items-center">
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium
                                                    {{ $tarea->color_estado === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $tarea->color_estado === 'blue' ? 'bg-blue-100 text-blue-800' : '' }}
                                                    {{ $tarea->color_estado === 'red' ? 'bg-red-100 text-red-800' : '' }}
                                                    {{ $tarea->color_estado === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}">
                                                    {{ $tarea->estado_humano }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Panel de Análisis Semanal -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Productividad Semanal -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Productividad</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Porcentaje de Completación -->
                        <div>
                            @php
                                $porcentajeCompletado = $estadisticasSemana['total_tareas'] > 0 ? 
                                    round(($estadisticasSemana['completadas'] / $estadisticasSemana['total_tareas']) * 100) : 0;
                            @endphp
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tareas Completadas</span>
                                <span class="font-medium">{{ $porcentajeCompletado }}%</span>
                            </div>
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-green-500 h-3 rounded-full transition-all duration-500" 
                                     style="width: {{ $porcentajeCompletado }}%"></div>
                            </div>
                        </div>

                        <!-- Promedio Diario -->
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Promedio de tareas/día</span>
                            <span class="text-sm font-medium">{{ $estadisticasSemana['promedio_tareas_dia'] }}</span>
                        </div>

                        <!-- Carga de Trabajo -->
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Carga total semanal</span>
                            <span class="text-sm font-medium">
                                {{ intval($estadisticasSemana['carga_trabajo_total'] / 60) }}h {{ $estadisticasSemana['carga_trabajo_total'] % 60 }}m
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribución por Tipo -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Tipos de Tareas</h3>
                </div>
                <div class="p-6">
                    @php
                        $tiposTareas = collect($diasSemana)
                            ->flatMap(function($dia) { return $dia['tareas']; })
                            ->groupBy('tipo')
                            ->map(function($grupo) { return $grupo->count(); })
                            ->sortDesc();
                    @endphp
                    
                    <div class="space-y-3">
                        @foreach($tiposTareas as $tipo => $cantidad)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded
                                        {{ $tipo === 'seguimiento' ? 'bg-blue-500' : '' }}
                                        {{ $tipo === 'cotizacion' ? 'bg-green-500' : '' }}
                                        {{ $tipo === 'reunion' ? 'bg-purple-500' : '' }}
                                        {{ $tipo === 'llamada' ? 'bg-yellow-500' : '' }}
                                        {{ !in_array($tipo, ['seguimiento', 'cotizacion', 'reunion', 'llamada']) ? 'bg-gray-500' : '' }} mr-2">
                                    </div>
                                    <span class="text-sm text-gray-700 capitalize">{{ $tipo }}</span>
                                </div>
                                <span class="text-sm font-medium">{{ $cantidad }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Acciones Rápidas</h3>
                </div>
                <div class="p-6 space-y-3">
                    <button onclick="planificarSemana()" 
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-magic mr-2"></i>
                        Planificación Inteligente
                    </button>
                    
                    <button onclick="balancearCarga()" 
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-balance-scale mr-2"></i>
                        Balancear Carga
                    </button>
                    
                    <button onclick="duplicarSemana()" 
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-copy mr-2"></i>
                        Duplicar Semana
                    </button>
                    
                    <button onclick="exportarSemana()" 
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-download mr-2"></i>
                        Exportar Semana
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Tarea Rápida -->
<div id="modal-tarea-rapida" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="cerrarModalTareaRapida()"></div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            <form id="form-tarea-rapida">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        Tarea Rápida
                    </h3>
                    
                    <div class="space-y-3">
                        <input type="hidden" id="fecha_rapida" name="fecha_tarea">
                        
                        <div>
                            <input type="text" 
                                   id="titulo_rapido" 
                                   name="titulo" 
                                   placeholder="¿Qué necesitas hacer?"
                                   required
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <select id="tipo_rapido" name="tipo" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="seguimiento">Seguimiento</option>
                                <option value="llamada">Llamada</option>
                                <option value="reunion">Reunión</option>
                                <option value="email">Email</option>
                                <option value="administrativa">Administrativa</option>
                            </select>
                            
                            <input type="time" 
                                   id="hora_rapida" 
                                   name="hora_inicio"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Crear
                    </button>
                    <button type="button" 
                            onclick="cerrarModalTareaRapida()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Selector de semana
    document.getElementById('semana-selector').addEventListener('change', function() {
        window.location.href = window.location.pathname + '?semana=' + this.value;
    });

    // Formulario de tarea rápida
    document.getElementById('form-tarea-rapida').addEventListener('submit', crearTareaRapida);
    
    // Cerrar menús al hacer click fuera
    document.addEventListener('click', function(event) {
        const menus = document.querySelectorAll('[id^="menu-"]');
        menus.forEach(menu => {
            if (!menu.contains(event.target) && !event.target.matches('[onclick*="toggleMenuTarea"]')) {
                menu.classList.add('hidden');
            }
        });
    });
});

// Funciones de vista
function cambiarVista(tipo) {
    const compacta = document.getElementById('vista-compacta');
    const detallada = document.getElementById('vista-detallada');
    
    if (tipo === 'compacta') {
        compacta.classList.add('bg-blue-50', 'border-blue-300');
        detallada.classList.remove('bg-blue-50', 'border-blue-300');
        // Implementar vista compacta
    } else {
        detallada.classList.add('bg-blue-50', 'border-blue-300');
        compacta.classList.remove('bg-blue-50', 'border-blue-300');
        // Implementar vista detallada
    }
}

// Funciones de drag and drop
function allowDrop(ev) {
    ev.preventDefault();
}

function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.dataset.tareaId);
}

function drop(ev) {
    ev.preventDefault();
    const tareaId = ev.dataTransfer.getData("text");
    const nuevaFecha = ev.currentTarget.dataset.fecha;
    
    // Mover tarea a nueva fecha
    moverTarea(tareaId, nuevaFecha);
}

// Funciones de tareas
async function moverTarea(tareaId, nuevaFecha) {
    try {
        const response = await fetch(`/agenda/tareas/${tareaId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                fecha_tarea: nuevaFecha
            })
        });

        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert('Error al mover la tarea');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al mover la tarea');
    }
}

async function cambiarEstadoTarea(tareaId, completada) {
    try {
        const nuevoEstado = completada ? 'completada' : 'pendiente';
        
        const response = await fetch(`/agenda/tareas/${tareaId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                estado: nuevoEstado,
                resultado: completada ? 'Completada desde vista semanal' : null
            })
        });

        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert('Error al actualizar la tarea');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al actualizar la tarea');
    }
}

function agregarTareaRapida(fecha) {
    document.getElementById('fecha_rapida').value = fecha;
    document.getElementById('modal-tarea-rapida').classList.remove('hidden');
    document.getElementById('titulo_rapido').focus();
}

function cerrarModalTareaRapida() {
    document.getElementById('modal-tarea-rapida').classList.add('hidden');
    document.getElementById('form-tarea-rapida').reset();
}

async function crearTareaRapida(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('prioridad', 'media'); // Prioridad por defecto
    
    try {
        const response = await fetch('/agenda/tareas', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        });

        const data = await response.json();
        
        if (data.success) {
            cerrarModalTareaRapida();
            location.reload();
        } else {
            alert('Error al crear la tarea: ' + (data.message || 'Error desconocido'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al crear la tarea');
    }
}

function toggleMenuTarea(tareaId) {
    const menu = document.getElementById(`menu-${tareaId}`);
    menu.classList.toggle('hidden');
}

function abrirDetallesTarea(tareaId) {
    // Implementar modal de detalles de tarea
    console.log('Abrir detalles de tarea:', tareaId);
}

// Funciones de acciones rápidas
function planificarSemana() {
    alert('Funcionalidad de planificación inteligente en desarrollo');
}

function balancearCarga() {
    alert('Funcionalidad de balanceo de carga en desarrollo');
}

function duplicarSemana() {
    if (confirm('¿Duplicar todas las tareas de esta semana para la próxima semana?')) {
        alert('Funcionalidad de duplicación en desarrollo');
    }
}

function exportarSemana() {
    alert('Funcionalidad de exportación en desarrollo');
}

// Funciones reutilizadas del día anterior
function editarTarea(id) {
    console.log('Editar tarea:', id);
}

function duplicarTarea(id) {
    console.log('Duplicar tarea:', id);
}

function completarTarea(id) {
    console.log('Completar tarea:', id);
}

function eliminarTarea(id) {
    if (confirm('¿Estás seguro de eliminar esta tarea?')) {
        console.log('Eliminar tarea:', id);
    }
}

function abrirModalNuevaTarea() {
    // Redirigir a vista diaria o abrir modal más completo
    window.location.href = '{{ route("agenda.mi-dia") }}';
}
</script>
@endsection

@section('styles')
<style>
/* Estilos para el calendario semanal */
.tarea-card {
    transition: all 0.2s ease;
    min-height: 60px;
}

.tarea-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.tarea-card.dragging {
    opacity: 0.5;
    transform: rotate(5deg);
}

/* Indicadores de capacidad */
.capacidad-normal {
    background-color: #ecfdf5;
}

.capacidad-alta {
    background-color: #fefce8;
}

.capacidad-sobrecarga {
    background-color: #fef2f2;
}

/* Grid responsivo */
@media (max-width: 1024px) {
    .grid-cols-7 {
        grid-template-columns: repeat(7, minmax(120px, 1fr));
    }
}

@media (max-width: 768px) {
    .grid-cols-7 {
        grid-template-columns: 1fr;
    }
    
    .calendario-movil .dia-semana {
        margin-bottom: 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1rem;
    }
}

/* Animaciones */
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

/* Drag and drop states */
.drag-over {
    background-color: #eff6ff;
    border: 2px dashed #3b82f6;
}

.drag-source {
    opacity: 0.5;
}

/* Estados del día */
.dia-hoy {
    background-color: #eff6ff;
}

.dia-fin-semana {
    background-color: #f9fafb;
}

/* Menu contextual */
.menu-tarea {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>
@endsection