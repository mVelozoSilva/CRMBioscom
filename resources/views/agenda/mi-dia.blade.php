@extends('layouts.app')

@section('title', 'Mi Día - Agenda CRM Bioscom')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header con Control de Fecha -->
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-calendar-day text-blue-600 mr-3"></i>
                    Mi Día
                    <span class="ml-3 text-lg font-normal text-gray-500">
                        {{ $fechaCarbon->format('l, d/m/Y') }}
                    </span>
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Gestiona tu agenda y tareas del día de manera eficiente
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                <!-- Control de Fecha -->
                <div class="flex items-center space-x-2">
                    <a href="{{ request()->fullUrlWithQuery(['fecha' => $fechaCarbon->copy()->subDay()->format('Y-m-d')]) }}" 
                       class="p-2 border border-gray-300 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <input type="date" 
                           id="fecha-selector"
                           value="{{ $fechaCarbon->format('Y-m-d') }}"
                           class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <a href="{{ request()->fullUrlWithQuery(['fecha' => $fechaCarbon->copy()->addDay()->format('Y-m-d')]) }}" 
                       class="p-2 border border-gray-300 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
                
                <button onclick="abrirModalNuevaTarea()" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-plus mr-2"></i>
                    Nueva Tarea
                </button>
                
                <a href="{{ route('agenda.mi-semana') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-calendar-week mr-2"></i>
                    Vista Semanal
                </a>
            </div>
        </div>

        <!-- Alertas y Notificaciones -->
        @if(!empty($alertas))
            <div class="mb-6 space-y-3">
                @foreach($alertas as $alerta)
                    <div class="rounded-md p-4 
                        {{ $alerta['tipo'] === 'error' ? 'bg-red-50 border border-red-200' : '' }}
                        {{ $alerta['tipo'] === 'warning' ? 'bg-yellow-50 border border-yellow-200' : '' }}
                        {{ $alerta['tipo'] === 'info' ? 'bg-blue-50 border border-blue-200' : '' }}">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle 
                                    {{ $alerta['tipo'] === 'error' ? 'text-red-400' : '' }}
                                    {{ $alerta['tipo'] === 'warning' ? 'text-yellow-400' : '' }}
                                    {{ $alerta['tipo'] === 'info' ? 'text-blue-400' : '' }}"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-medium 
                                    {{ $alerta['tipo'] === 'error' ? 'text-red-800' : '' }}
                                    {{ $alerta['tipo'] === 'warning' ? 'text-yellow-800' : '' }}
                                    {{ $alerta['tipo'] === 'info' ? 'text-blue-800' : '' }}">
                                    {{ $alerta['titulo'] }}
                                </h3>
                                <div class="mt-2 text-sm 
                                    {{ $alerta['tipo'] === 'error' ? 'text-red-700' : '' }}
                                    {{ $alerta['tipo'] === 'warning' ? 'text-yellow-700' : '' }}
                                    {{ $alerta['tipo'] === 'info' ? 'text-blue-700' : '' }}">
                                    <p>{{ $alerta['mensaje'] }}</p>
                                </div>
                                @if($alerta['accion'] === 'distribuir_seguimientos')
                                    <div class="mt-3">
                                        <button onclick="abrirModalDistribucion()" 
                                                class="text-sm bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700">
                                            Distribuir Automáticamente
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Estadísticas Rápidas -->
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
                            <p class="text-2xl font-semibold text-gray-900">{{ $estadisticas['total_tareas'] }}</p>
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
                            <p class="text-2xl font-semibold text-gray-900">{{ $estadisticas['completadas'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 truncate">Tiempo Total</p>
                            <p class="text-2xl font-semibold text-gray-900">
                                {{ intval($estadisticas['tiempo_estimado_total'] / 60) }}h 
                                {{ $estadisticas['tiempo_estimado_total'] % 60 }}m
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 truncate">Seguimientos Vencidos</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $estadisticas['seguimientos_vencidos'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Lista de Tareas Principal -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">
                            Tareas del Día
                            @if($fechaCarbon->isToday())
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Hoy
                                </span>
                            @elseif($fechaCarbon->isTomorrow())
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Mañana
                                </span>
                            @elseif($fechaCarbon->isPast())
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Pasado
                                </span>
                            @endif
                        </h3>
                        <div class="flex space-x-2">
                            <select id="filtro-estado" class="text-sm border-gray-300 rounded-md">
                                <option value="">Todos los estados</option>
                                <option value="pendiente">Pendientes</option>
                                <option value="en_progreso">En Progreso</option>
                                <option value="completada">Completadas</option>
                            </select>
                            <select id="filtro-tipo" class="text-sm border-gray-300 rounded-md">
                                <option value="">Todos los tipos</option>
                                <option value="seguimiento">Seguimiento</option>
                                <option value="cotizacion">Cotización</option>
                                <option value="reunion">Reunión</option>
                                <option value="llamada">Llamada</option>
                            </select>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-200" id="lista-tareas">
                        @forelse($tareas as $tarea)
                            <div class="p-6 hover:bg-gray-50 transition-colors tarea-item" 
                                 data-estado="{{ $tarea->estado }}" 
                                 data-tipo="{{ $tarea->tipo }}"
                                 data-tarea-id="{{ $tarea->id }}">
                                <div class="flex items-start space-x-4">
                                    <!-- Checkbox de Estado -->
                                    <div class="flex-shrink-0 pt-1">
                                        <input type="checkbox" 
                                               {{ $tarea->estado === 'completada' ? 'checked' : '' }}
                                               onchange="cambiarEstadoTarea({{ $tarea->id }}, this.checked)"
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    </div>

                                    <!-- Contenido Principal -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-lg font-medium text-gray-900 {{ $tarea->estado === 'completada' ? 'line-through text-gray-500' : '' }}">
                                                {{ $tarea->titulo }}
                                            </h4>
                                            <div class="flex items-center space-x-2">
                                                <!-- Prioridad -->
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $tarea->color_prioridad === 'red' ? 'bg-red-100 text-red-800' : '' }}
                                                    {{ $tarea->color_prioridad === 'orange' ? 'bg-orange-100 text-orange-800' : '' }}
                                                    {{ $tarea->color_prioridad === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $tarea->color_prioridad === 'green' ? 'bg-green-100 text-green-800' : '' }}">
                                                    {{ $tarea->prioridad_humana }}
                                                </span>
                                                
                                                <!-- Estado -->
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $tarea->color_estado === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $tarea->color_estado === 'blue' ? 'bg-blue-100 text-blue-800' : '' }}
                                                    {{ $tarea->color_estado === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $tarea->color_estado === 'red' ? 'bg-red-100 text-red-800' : '' }}
                                                    {{ $tarea->color_estado === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}">
                                                    {{ $tarea->estado_humano }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Información Adicional -->
                                        <div class="mt-2 flex items-center text-sm text-gray-500 space-x-4">
                                            <!-- Hora -->
                                            @if($tarea->hora_inicio)
                                                <div class="flex items-center">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    {{ Carbon::parse($tarea->hora_inicio)->format('H:i') }}
                                                    @if($tarea->hora_fin)
                                                        - {{ Carbon::parse($tarea->hora_fin)->format('H:i') }}
                                                    @endif
                                                </div>
                                            @endif

                                            <!-- Duración -->
                                            @if($tarea->duracion_estimada)
                                                <div class="flex items-center">
                                                    <i class="fas fa-stopwatch mr-1"></i>
                                                    {{ $tarea->duracion_formateada }}
                                                </div>
                                            @endif

                                            <!-- Tipo -->
                                            <div class="flex items-center">
                                                <i class="fas fa-tag mr-1"></i>
                                                {{ $tarea->tipo_humano }}
                                            </div>

                                            <!-- Cliente -->
                                            @if($tarea->cliente)
                                                <div class="flex items-center">
                                                    <i class="fas fa-building mr-1"></i>
                                                    {{ Str::limit($tarea->cliente->nombre_institucion, 30) }}
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Descripción -->
                                        @if($tarea->descripcion)
                                            <p class="mt-2 text-sm text-gray-600">{{ Str::limit($tarea->descripcion, 150) }}</p>
                                        @endif

                                        <!-- Resultado si está completada -->
                                        @if($tarea->estado === 'completada' && $tarea->resultado)
                                            <div class="mt-2 p-3 bg-green-50 border border-green-200 rounded-md">
                                                <p class="text-sm text-green-800">
                                                    <strong>Resultado:</strong> {{ $tarea->resultado }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Acciones -->
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center space-x-2">
                                            @if($tarea->estado !== 'completada')
                                                <button onclick="iniciarTarea({{ $tarea->id }})" 
                                                        class="text-blue-600 hover:text-blue-800"
                                                        title="Iniciar tarea">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                                <button onclick="posponerTarea({{ $tarea->id }})" 
                                                        class="text-yellow-600 hover:text-yellow-800"
                                                        title="Posponer">
                                                    <i class="fas fa-calendar-plus"></i>
                                                </button>
                                            @endif
                                            <button onclick="editarTarea({{ $tarea->id }})" 
                                                    class="text-indigo-600 hover:text-indigo-800"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if($tarea->puedeSerEliminada())
                                                <button onclick="eliminarTarea({{ $tarea->id }})" 
                                                        class="text-red-600 hover:text-red-800"
                                                        title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-12 text-center">
                                <i class="fas fa-calendar-check text-4xl text-gray-400 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No hay tareas programadas</h3>
                                <p class="text-gray-500 mb-4">
                                    @if($fechaCarbon->isToday())
                                        ¡Perfecto! No tienes tareas pendientes para hoy.
                                    @else
                                        No hay tareas programadas para {{ $fechaCarbon->format('d/m/Y') }}.
                                    @endif
                                </p>
                                <button onclick="abrirModalNuevaTarea()" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                    <i class="fas fa-plus mr-2"></i>
                                    Agregar Tarea
                                </button>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Seguimientos Vencidos -->
                @if($seguimientosVencidos->count() > 0)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">Seguimientos Vencidos</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                {{ $seguimientosVencidos->count() }}
                            </span>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @foreach($seguimientosVencidos as $seguimiento)
                                <div class="p-4">
                                    <h4 class="text-sm font-medium text-gray-900">
                                        {{ $seguimiento->cliente->nombre_institucion }}
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Vencido: {{ $seguimiento->proxima_gestion->diffForHumans() }}
                                    </p>
                                    @if($seguimiento->cotizacion)
                                        <p class="text-xs text-blue-600 mt-1">
                                            {{ $seguimiento->cotizacion->nombre_cotizacion }}
                                        </p>
                                    @endif
                                    <button onclick="convertirSeguimientoATarea({{ $seguimiento->id }})"
                                            class="mt-2 text-xs bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700">
                                        Crear Tarea
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                            <button onclick="abrirModalDistribucion()" 
                                    class="w-full text-sm text-blue-600 hover:text-blue-800">
                                Distribuir Todos Automáticamente
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Próximas Tareas -->
                @if($proximasTareas->count() > 0)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Próximas Tareas</h3>
                            <p class="text-sm text-gray-500">Siguiente día laboral</p>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @foreach($proximasTareas as $tarea)
                                <div class="p-4">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $tarea->titulo }}</h4>
                                    <div class="mt-1 flex items-center text-xs text-gray-500 space-x-2">
                                        @if($tarea->hora_inicio)
                                            <span>{{ Carbon::parse($tarea->hora_inicio)->format('H:i') }}</span>
                                        @endif
                                        <span>{{ $tarea->tipo_humano }}</span>
                                        @if($tarea->cliente)
                                            <span>{{ Str::limit($tarea->cliente->nombre_institucion, 20) }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Carga de Trabajo Semanal -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Carga de Trabajo Semanal</h3>
                    </div>
                    <div class="p-6">
                        @php
                            $diasSemana = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
                            $inicioSemana = $fechaCarbon->copy()->startOfWeek();
                        @endphp
                        
                        <div class="space-y-3">
                            @for($i = 0; $i < 7; $i++)
                                @php
                                    $fecha = $inicioSemana->copy()->addDays($i);
                                    $fechaStr = $fecha->format('Y-m-d');
                                    $datosDia = $cargaSemana->get($fechaStr);
                                    $tiempoTotal = $datosDia ? $datosDia->tiempo_total : 0;
                                    $totalTareas = $datosDia ? $datosDia->total_tareas : 0;
                                    $porcentajeCarga = min(100, ($tiempoTotal / 480) * 100); // 480min = 8h
                                @endphp
                                
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-gray-700 w-8">{{ $diasSemana[$i] }}</span>
                                        <span class="text-xs text-gray-500">{{ $fecha->format('d/m') }}</span>
                                        @if($fecha->isToday())
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                Hoy
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-16 bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" 
                                                 style="width: {{ $porcentajeCarga }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-500 w-8">{{ $totalTareas }}</span>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Acciones Rápidas</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <button onclick="abrirModalNuevaTarea()" 
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-plus mr-2"></i>
                            Nueva Tarea
                        </button>
                        
                        <button onclick="marcarTodasCompletadas()" 
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-check-double mr-2"></i>
                            Completar Todas
                        </button>
                        
                        <a href="{{ route('seguimiento.index') }}" 
                           class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-tasks mr-2"></i>
                            Ver Seguimientos
                        </a>
                        
                        <button onclick="exportarDia()" 
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-download mr-2"></i>
                            Exportar Día
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Nueva Tarea -->
<div id="modal-nueva-tarea" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="cerrarModalNuevaTarea()"></div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="form-nueva-tarea">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Nueva Tarea
                            </h3>
                            
                            <div class="space-y-4">
                                <!-- Título -->
                                <div>
                                    <label for="tarea_titulo" class="block text-sm font-medium text-gray-700">
                                        Título <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="tarea_titulo" 
                                           name="titulo" 
                                           required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <!-- Fecha y Hora -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="tarea_fecha" class="block text-sm font-medium text-gray-700">
                                            Fecha <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" 
                                               id="tarea_fecha" 
                                               name="fecha_tarea" 
                                               value="{{ $fechaCarbon->format('Y-m-d') }}"
                                               required
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="tarea_hora" class="block text-sm font-medium text-gray-700">
                                            Hora Inicio
                                        </label>
                                        <input type="time" 
                                               id="tarea_hora" 
                                               name="hora_inicio"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                </div>

                                <!-- Tipo y Prioridad -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="tarea_tipo" class="block text-sm font-medium text-gray-700">
                                            Tipo <span class="text-red-500">*</span>
                                        </label>
                                        <select id="tarea_tipo" 
                                                name="tipo" 
                                                required
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="seguimiento">Seguimiento</option>
                                            <option value="cotizacion">Cotización</option>
                                            <option value="reunion">Reunión</option>
                                            <option value="llamada">Llamada</option>
                                            <option value="email">Email</option>
                                            <option value="administrativa">Administrativa</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="tarea_prioridad" class="block text-sm font-medium text-gray-700">
                                            Prioridad <span class="text-red-500">*</span>
                                        </label>
                                        <select id="tarea_prioridad" 
                                                name="prioridad" 
                                                required
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="baja">Baja</option>
                                            <option value="media" selected>Media</option>
                                            <option value="alta">Alta</option>
                                            <option value="urgente">Urgente</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Descripción -->
                                <div>
                                    <label for="tarea_descripcion" class="block text-sm font-medium text-gray-700">
                                        Descripción
                                    </label>
                                    <textarea id="tarea_descripcion" 
                                              name="descripcion" 
                                              rows="3"
                                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Crear Tarea
                    </button>
                    <button type="button" 
                            onclick="cerrarModalNuevaTarea()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Distribución Automática -->
<div id="modal-distribucion" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="cerrarModalDistribucion()"></div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="form-distribucion">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Distribución Automática de Seguimientos
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="max_tareas_dia" class="block text-sm font-medium text-gray-700">
                                        Máximo de tareas por día
                                    </label>
                                    <input type="number" 
                                           id="max_tareas_dia" 
                                           name="max_tareas_por_dia" 
                                           value="5"
                                           min="1" 
                                           max="20"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="dias_distribucion" class="block text-sm font-medium text-gray-700">
                                        Distribuir en los próximos X días
                                    </label>
                                    <input type="number" 
                                           id="dias_distribucion" 
                                           name="dias_distribucion" 
                                           value="10"
                                           min="1" 
                                           max="30"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="priorizar_por" class="block text-sm font-medium text-gray-700">
                                        Priorizar por
                                    </label>
                                    <select id="priorizar_por" 
                                            name="priorizar_por"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="fecha_vencimiento">Fecha de vencimiento</option>
                                        <option value="valor_cotizacion">Valor de cotización</option>
                                        <option value="tipo_cliente">Tipo de cliente</option>
                                    </select>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           id="incluir_fin_semana" 
                                           name="incluir_fin_semana"
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="incluir_fin_semana" class="ml-2 block text-sm text-gray-900">
                                        Incluir fines de semana
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Distribuir Automáticamente
                    </button>
                    <button type="button" 
                            onclick="cerrarModalDistribucion()"
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
    // Selector de fecha
    document.getElementById('fecha-selector').addEventListener('change', function() {
        window.location.href = window.location.pathname + '?fecha=' + this.value;
    });

    // Filtros
    document.getElementById('filtro-estado').addEventListener('change', aplicarFiltros);
    document.getElementById('filtro-tipo').addEventListener('change', aplicarFiltros);

    // Formularios
    document.getElementById('form-nueva-tarea').addEventListener('submit', crearTarea);
    document.getElementById('form-distribucion').addEventListener('submit', distribuirSeguimientos);
});

// Funciones de filtrado
function aplicarFiltros() {
    const filtroEstado = document.getElementById('filtro-estado').value;
    const filtroTipo = document.getElementById('filtro-tipo').value;
    const tareas = document.querySelectorAll('.tarea-item');

    tareas.forEach(tarea => {
        const estado = tarea.dataset.estado;
        const tipo = tarea.dataset.tipo;
        
        const mostrarEstado = !filtroEstado || estado === filtroEstado;
        const mostrarTipo = !filtroTipo || tipo === filtroTipo;
        
        if (mostrarEstado && mostrarTipo) {
            tarea.style.display = 'block';
        } else {
            tarea.style.display = 'none';
        }
    });
}

// Funciones de tareas
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
                resultado: completada ? 'Completada desde agenda' : null
            })
        });

        const data = await response.json();
        
        if (data.success) {
            // Actualizar la interfaz
            location.reload();
        } else {
            alert('Error al actualizar la tarea');
            // Revertir checkbox
            event.target.checked = !completada;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al actualizar la tarea');
        event.target.checked = !completada;
    }
}

async function crearTarea(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
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
            cerrarModalNuevaTarea();
            location.reload();
        } else {
            alert('Error al crear la tarea: ' + (data.message || 'Error desconocido'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al crear la tarea');
    }
}

async function distribuirSeguimientos(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    try {
        const response = await fetch('/agenda/distribuir-seguimientos', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        });

        const data = await response.json();
        
        if (data.success) {
            alert(`Distribución completada: ${data.tareas_creadas} tareas creadas`);
            cerrarModalDistribucion();
            location.reload();
        } else {
            alert('Error en la distribución: ' + (data.message || 'Error desconocido'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error en la distribución automática');
    }
}

// Funciones de modales
function abrirModalNuevaTarea() {
    document.getElementById('modal-nueva-tarea').classList.remove('hidden');
}

function cerrarModalNuevaTarea() {
    document.getElementById('modal-nueva-tarea').classList.add('hidden');
    document.getElementById('form-nueva-tarea').reset();
}

function abrirModalDistribucion() {
    document.getElementById('modal-distribucion').classList.remove('hidden');
}

function cerrarModalDistribucion() {
    document.getElementById('modal-distribucion').classList.add('hidden');
}

// Funciones adicionales (simplificadas)
function iniciarTarea(id) {
    // Implementar inicio de tarea
    console.log('Iniciar tarea:', id);
}

function posponerTarea(id) {
    // Implementar posponer tarea
    console.log('Posponer tarea:', id);
}

function editarTarea(id) {
    // Implementar edición de tarea
    console.log('Editar tarea:', id);
}

function eliminarTarea(id) {
    if (confirm('¿Estás seguro de eliminar esta tarea?')) {
        // Implementar eliminación
        console.log('Eliminar tarea:', id);
    }
}

function convertirSeguimientoATarea(id) {
    // Implementar conversión de seguimiento a tarea
    console.log('Convertir seguimiento a tarea:', id);
}

function marcarTodasCompletadas() {
    if (confirm('¿Marcar todas las tareas pendientes como completadas?')) {
        // Implementar completar todas
        console.log('Completar todas las tareas');
    }
}

function exportarDia() {
    // Implementar exportación del día
    console.log('Exportar día');
}
</script>
@endsection

@section('styles')
<style>
/* Estilos personalizados para la agenda */
.tarea-item {
    transition: all 0.2s ease;
}

.tarea-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.tarea-completada {
    opacity: 0.7;
}

.filtro-aplicado {
    background-color: #eff6ff;
    border-color: #3b82f6;
}

/* Animaciones para estadísticas */
.estadistica-card {
    transition: transform 0.2s ease;
}

.estadistica-card:hover {
    transform: scale(1.02);
}

/* Estilos para alertas */
.alerta-animada {
    animation: slideInDown 0.3s ease;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .tarea-item {
        padding: 1rem;
    }
    
    .estadistica-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
@endsection