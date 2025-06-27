<template>
    <div>
        <!-- Loading State -->
        <div v-if="loading" class="p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                <i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i>
            </div>
            <p class="text-gray-600">Cargando tareas del día...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
            </div>
            <p class="text-red-600 mb-4">{{ error }}</p>
            <button @click="cargarTareas" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <i class="fas fa-redo mr-2"></i>
                Reintentar
            </button>
        </div>

        <!-- Main Content -->
        <div v-else>
            <!-- 🎨 SECCIÓN DE FILTROS Y NAVEGACIÓN - DISEÑO PREMIUM -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 space-y-4">
                
                <!-- FILA 1: FILTROS PRINCIPALES -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Búsqueda -->
                        <div class="relative flex-1 min-w-[200px]">
                            <input
                                v-model="filtros.search"
                                @input="buscarConRetraso"
                                type="text"
                                placeholder="Buscar tareas..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors"
                            >
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>

                        <!-- Filtros en línea -->
                        <div class="flex items-center gap-3">
                            <select v-model="filtros.estado" @change="cargarTareas" class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white min-w-[140px]">
                                <option value="">Todos los estados</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="en_progreso">En Progreso</option>
                                <option value="completada">Completada</option>
                                <option value="vencida">Vencida</option>
                                <option value="pospuesta">Pospuesta</option>
                            </select>

                            <select v-model="filtros.prioridad" @change="cargarTareas" class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white min-w-[140px]">
                                <option value="">Todas las prioridades</option>
                                <option value="urgente">Urgente</option>
                                <option value="alta">Alta</option>
                                <option value="media">Media</option>
                                <option value="baja">Baja</option>
                            </select>

                            <select v-model="filtros.tipo" @change="cargarTareas" class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white min-w-[120px]">
                                <option value="">Todos los tipos</option>
                                <option value="seguimiento">Seguimiento</option>
                                <option value="cotizacion">Cotización</option>
                                <option value="mantencion">Mantención</option>
                                <option value="reunion">Reunión</option>
                                <option value="llamada">Llamada</option>
                                <option value="cobranza">Cobranza</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- FILA 2: NAVEGACIÓN TEMPORAL Y CONTROLES -->
                <div class="flex flex-wrap items-center justify-between gap-4">
                    
                    <!-- NAVEGACIÓN TEMPORAL (Izquierda) -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-3">
                        <div class="flex items-center space-x-3">
                            <!-- Botón Ayer -->
                            <button 
                                @click="irAyer"
                                class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 border border-transparent hover:border-blue-200">
                                <i class="fas fa-chevron-left text-xs"></i>
                                <span class="font-medium">Ayer</span>
                            </button>

                            <!-- Indicador de Fecha Central -->
                            <div class="px-4 py-2 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="text-center">
                                    <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">{{ fechaFormateada.diaSemana }}</div>
                                    <div class="text-xl font-bold text-blue-900">{{ fechaFormateada.diaNumero }}</div>
                                    <div class="text-xs text-blue-600">{{ fechaFormateada.mesAno }}</div>
                                </div>
                            </div>

                            <!-- Botón Mañana -->
                            <button 
                                @click="irManana"
                                class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 border border-transparent hover:border-blue-200">
                                <span class="font-medium">Mañana</span>
                                <i class="fas fa-chevron-right text-xs"></i>
                            </button>

                            <!-- Separador -->
                            <div class="w-px h-8 bg-gray-300 mx-2"></div>

                            <!-- Selector de Fecha -->
                            <div class="relative">
                                <input 
                                    type="date" 
                                    v-model="fechaSeleccionadaInput"
                                    @change="seleccionarFecha"
                                    class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                            </div>

                            <!-- Botón Hoy (ÚNICO) -->
                            <button 
                                @click="irHoy"
                                :class="esHoy ? 'bg-blue-600 text-white border-blue-600 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                                class="px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 flex items-center space-x-2 border">
                                <i class="fas fa-calendar-day text-xs"></i>
                                <span>Hoy</span>
                            </button>
                        </div>
                    </div>

                    <!-- CONTROLES Y ESTADO (Derecha) -->
                    <div class="flex items-center gap-4">
                        <!-- Indicador de Estado de Fecha -->
                        <div class="text-sm">
                            <span v-if="esHoy" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                <i class="fas fa-circle mr-1.5 text-xs"></i>
                                Visualizando hoy
                            </span>
                            <span v-else-if="esAyer" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                <i class="fas fa-history mr-1.5 text-xs"></i>
                                Tareas de ayer
                            </span>
                            <span v-else-if="esManana" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 border border-blue-200">
                                <i class="fas fa-arrow-right mr-1.5 text-xs"></i>
                                Tareas de mañana
                            </span>
                            <span v-else class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700 border border-purple-200">
                                <i class="fas fa-calendar-alt mr-1.5 text-xs"></i>
                                {{ fechaPersonalizada }}
                            </span>
                        </div>

                        <!-- Contador de Resultados -->
                        <div class="text-sm text-gray-600 bg-white px-3 py-2 rounded-lg border border-gray-200">
                            <span v-if="paginacion.total > 0" class="font-medium">
                                {{ paginacion.from }}-{{ paginacion.to }} de {{ paginacion.total }} tareas
                            </span>
                            <span v-else class="text-gray-500">Sin tareas</span>
                        </div>
                        
                        <!-- Botón de Columnas -->
                        <button @click="mostrarSelectorColumnas = !mostrarSelectorColumnas" class="relative inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors shadow-sm">
                            <i class="fas fa-columns mr-2"></i>
                            <span>Columnas</span>
                            <span v-if="columnasOcultas > 0" class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">{{ columnasOcultas }}</span>
                        </button>
                        
                        <!-- Column Selector Dropdown (mantener igual) -->
                        <div v-if="mostrarSelectorColumnas" class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                            <div class="p-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Mostrar columnas:</h4>
                                <div class="space-y-2">
                                    <label v-for="columna in opcionesColumnas" :key="columna.key" class="flex items-center">
                                        <input
                                            v-model="columnasVisibles[columna.key]"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        >
                                        <span class="ml-2 text-sm text-gray-700">{{ columna.label }}</span>
                                    </label>
                                </div>
                                <div class="mt-4 pt-3 border-t border-gray-200 flex justify-between">
                                    <button @click="mostrarTodasColumnas" class="text-xs text-blue-600 hover:text-blue-700">Mostrar todas</button>
                                    <button @click="mostrarSelectorColumnas = false" class="text-xs text-gray-500 hover:text-gray-700">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                <!-- Bulk Actions Bar -->
                <div v-if="tareasSeleccionadas.length > 0" class="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center">
                        <span class="text-sm font-medium text-blue-900">
                            {{ tareasSeleccionadas.length }} tarea(s) seleccionada(s)
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button @click="completarTareasMasivo" class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-check mr-2"></i>
                            Completar
                        </button>
                        <button @click="cambiarEstadoMasivo" class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-edit mr-2"></i>
                            Cambiar Estado
                        </button>
                        <button @click="limpiarSeleccion" class="inline-flex items-center px-3 py-1 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-times mr-2"></i>
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="tareas.length === 0 && !loading" class="p-12 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                    <i class="fas fa-calendar-check text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">¡Sin tareas para hoy!</h3>
                <p class="text-gray-600 mb-6">
                    {{ filtrosActivos ? 'No hay tareas que coincidan con los filtros seleccionados.' : 'No tienes tareas programadas para hoy. ¡Buen trabajo!' }}
                </p>
                <button @click="limpiarFiltros" v-if="filtrosActivos" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Limpiar Filtros
                </button>
            </div>

            <!-- Tasks Table -->
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <!-- Select All Checkbox -->
                            <th class="px-6 py-3 text-left">
                                <input
                                    v-model="todasSeleccionadas"
                                    @change="toggleTodasSeleccionadas"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                            </th>
                            <th v-if="columnasVisibles.tarea" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tarea
                            </th>
                            <th v-if="columnasVisibles.estado" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th v-if="columnasVisibles.prioridad" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Prioridad
                            </th>
                            <th v-if="columnasVisibles.tipo" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tipo
                            </th>
                            <th v-if="columnasVisibles.hora" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Hora
                            </th>
                            <th v-if="columnasVisibles.cliente" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cliente
                            </th>
                            <th v-if="columnasVisibles.asignado" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Asignado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="tarea in tareas" :key="tarea.id" class="hover:bg-gray-50 transition-colors" :class="{'bg-blue-25': tareasSeleccionadas.includes(tarea.id)}">
                            <!-- Select Checkbox -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input
                                    v-model="tareasSeleccionadas"
                                    :value="tarea.id"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                            </td>

                            <!-- Tarea Info -->
                            <td v-if="columnasVisibles.tarea" class="px-6 py-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mr-3">
                                        <div :class="getIconoTipo(tarea.tipo).class" class="w-8 h-8 rounded-lg flex items-center justify-center">
                                            <i :class="getIconoTipo(tarea.tipo).icon" class="text-sm"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ tarea.titulo }}</p>
                                        <p v-if="tarea.descripcion" class="text-sm text-gray-500 truncate">{{ tarea.descripcion }}</p>
                                        <p v-if="tarea.notas" class="text-xs text-gray-400 mt-1 truncate">
                                            <i class="fas fa-sticky-note mr-1"></i>{{ tarea.notas }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Estado -->
                            <td v-if="columnasVisibles.estado" class="px-6 py-4 whitespace-nowrap">
                                <span :class="getColorEstado(tarea.estado)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border">
                                    <i :class="getIconoEstado(tarea.estado)" class="mr-1"></i>
                                    {{ getTextoEstado(tarea.estado) }}
                                </span>
                            </td>

                            <!-- Prioridad -->
                            <td v-if="columnasVisibles.prioridad" class="px-6 py-4 whitespace-nowrap">
                                <span :class="getColorPrioridad(tarea.prioridad)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border">
                                    <i :class="getIconoPrioridad(tarea.prioridad)" class="mr-1"></i>
                                    {{ getTextoPrioridad(tarea.prioridad) }}
                                </span>
                            </td>

                            <!-- Tipo -->
                            <td v-if="columnasVisibles.tipo" class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-600">{{ getTextoTipo(tarea.tipo) }}</span>
                            </td>

                            <!-- Hora -->
                            <td v-if="columnasVisibles.hora" class="px-6 py-4 whitespace-nowrap">
                                <span v-if="tarea.hora_estimada" class="text-sm text-gray-900">
                                    <i class="fas fa-clock text-gray-400 mr-1"></i>
                                    {{ formatearHora(tarea.hora_estimada) }}
                                </span>
                                <span v-else class="text-sm text-gray-400">-</span>
                            </td>

                            <!-- Cliente -->
                            <td v-if="columnasVisibles.cliente" class="px-6 py-4 whitespace-nowrap">
                                <span v-if="tarea.cliente" class="text-sm text-gray-900">{{ tarea.cliente.nombre_institucion }}</span>
                                <span v-else class="text-sm text-gray-400">-</span>
                            </td>

                            <!-- Asignado -->
                            <td v-if="columnasVisibles.asignado" class="px-6 py-4 whitespace-nowrap">
                                <span v-if="tarea.usuario_asignado" class="text-sm text-gray-900">{{ tarea.usuario_asignado.name }}</span>
                                <span v-else class="text-sm text-gray-400">-</span>
                            </td>

                            <!-- Acciones -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button
                                        v-if="tarea.estado === 'pendiente' || tarea.estado === 'en_progreso'"
                                        @click="completarTarea(tarea)"
                                        class="text-green-600 hover:text-green-900 transition-colors"
                                        title="Completar tarea"
                                    >
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                    <button
                                        @click="verTarea(tarea)"
                                        class="text-blue-600 hover:text-blue-900 transition-colors"
                                        title="Ver detalles"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button
                                        @click="editarTarea(tarea)"
                                        class="text-yellow-600 hover:text-yellow-900 transition-colors"
                                        title="Editar tarea"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="paginacion.last_page > 1" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Página {{ paginacion.current_page }} de {{ paginacion.last_page }}
                    </div>
                    <div class="flex space-x-2">
                        <button
                            @click="cambiarPagina(paginacion.current_page - 1)"
                            :disabled="paginacion.current_page === 1"
                            class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100 transition-colors"
                        >
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button
                            @click="cambiarPagina(paginacion.current_page + 1)"
                            :disabled="paginacion.current_page === paginacion.last_page"
                            class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100 transition-colors"
                        >
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>

        <!-- Modal para cambio de estado masivo -->
        <div v-if="mostrarModalEstado" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="mostrarModalEstado = false">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Cambiar Estado Masivo</h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nuevo Estado:</label>
                        <select v-model="nuevoEstadoMasivo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="pendiente">Pendiente</option>
                            <option value="en_progreso">En Progreso</option>
                            <option value="completada">Completada</option>
                            <option value="pospuesta">Pospuesta</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button @click="mostrarModalEstado = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                            Cancelar
                        </button>
                        <button @click="aplicarCambioEstadoMasivo" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Aplicar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TareaDayList',
    data() {
        return {
            loading: true,
            error: null,
            tareas: [],
            fechaSeleccionada: new Date().toISOString().split('T')[0],
            fechaSeleccionadaInput: new Date().toISOString().split('T')[0],
            fechaHoy: new Date().toISOString().split('T')[0],
            
            paginacion: {
                current_page: 1,
                last_page: 1,
                total: 0,
                from: 0,
                to: 0
            },
            filtros: {
                search: '',
                estado: '',
                prioridad: '',
                tipo: '',
                fecha: 'hoy'
            },
            busquedaTimeout: null,
            
            // Selección masiva
            tareasSeleccionadas: [],
            todasSeleccionadas: false,
            
            // Configuración de columnas
            mostrarSelectorColumnas: false,
            columnasVisibles: {
                tarea: true,
                estado: true,
                prioridad: true,
                tipo: true,
                hora: true,
                cliente: true,
                asignado: false
            },
            opcionesColumnas: [
                { key: 'tarea', label: 'Tarea' },
                { key: 'estado', label: 'Estado' },
                { key: 'prioridad', label: 'Prioridad' },
                { key: 'tipo', label: 'Tipo' },
                { key: 'hora', label: 'Hora' },
                { key: 'cliente', label: 'Cliente' },
                { key: 'asignado', label: 'Asignado a' }
            ],
            
            // Modal de estado masivo
            mostrarModalEstado: false,
            nuevoEstadoMasivo: 'en_progreso'
        }
    },

    computed: {
        filtrosActivos() {
            return this.filtros.search || this.filtros.estado || this.filtros.prioridad || this.filtros.tipo;
        },
        
        columnasOcultas() {
            return Object.values(this.columnasVisibles).filter(visible => !visible).length;
        },

       fechaFormateada() {
            const fecha = new Date(this.fechaSeleccionada + 'T00:00:00');
            const diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
            const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            
            return {
                diaSemana: diasSemana[fecha.getDay()].substring(0, 3), // Toma las primeras 3 letras
                diaNumero: fecha.getDate(),
                mesAno: `${meses[fecha.getMonth()].substring(0, 3)} ${fecha.getFullYear()}`
            };
        },
        
        esHoy() {
            return this.fechaSeleccionada === this.fechaHoy;
        },
        
        esAyer() {
            const ayer = new Date();
            ayer.setDate(ayer.getDate() - 1);
            return this.fechaSeleccionada === ayer.toISOString().split('T')[0];
        },
        
        esManana() {
            const manana = new Date();
            manana.setDate(manana.getDate() + 1);
            return this.fechaSeleccionada === manana.toISOString().split('T')[0];
        },
        
        fechaPersonalizada() {
            if (this.esHoy || this.esAyer || this.esManana) return '';
            const fecha = new Date(this.fechaSeleccionada + 'T00:00:00');
            return fecha.toLocaleDateString('es-CL', { day: 'numeric', month: 'short' });
        }
    },

    mounted() {
        this.cargarTareas();
        
        // Cerrar selector de columnas al hacer click fuera
        document.addEventListener('click', (event) => {
            if (!this.$el.contains(event.target)) {
                this.mostrarSelectorColumnas = false;
            }
        });
    },

    watch: {
        fechaSeleccionada(nuevaFecha) {
            console.log('👁️ Fecha cambió en componente:', nuevaFecha);
            this.cargarTareas();
        }
    },

    methods: {
        async cargarTareas(pagina = 1) {
            this.loading = true;
            this.error = null;

            try {
                const params = new URLSearchParams({
                    page: pagina,
                    per_page: 20,
                    fecha: this.fechaSeleccionada,
                    ...(this.filtros.search && { search: this.filtros.search }),
                    ...(this.filtros.estado && { estado: this.filtros.estado }),
                    ...(this.filtros.prioridad && { prioridad: this.filtros.prioridad }),
                    ...(this.filtros.tipo && { tipo: this.filtros.tipo })
                });

                const response = await axios.get(`/crm-bioscom/public/api/agenda/tareas?${params}`);
                
                if (response.data.success) {
                    this.tareas = response.data.data.data;
                    this.paginacion = {
                        current_page: response.data.data.current_page,
                        last_page: response.data.data.last_page,
                        total: response.data.data.total,
                        from: response.data.data.from,
                        to: response.data.data.to
                    };

                    // Limpiar selección al cambiar de página
                    this.limpiarSeleccion();

                    // Emitir estadísticas al componente padre
                    if (response.data.estadisticas) {
                        console.log('📊 Estadísticas recibidas del backend:', response.data.estadisticas);
                        
                        this.$emit('estadisticas-actualizadas', response.data.estadisticas);
                        
                        // También actualizar las estadísticas globales si la función existe
                        if (window.actualizarEstadisticas) {
                            console.log('🔄 Llamando a window.actualizarEstadisticas...');
                            window.actualizarEstadisticas(response.data.estadisticas);
                        } else {
                            console.error('❌ window.actualizarEstadisticas no existe');
                        }
                    } else {
                        console.warn('⚠️ Backend no envió estadísticas');
                    }
                } else {
                    this.error = response.data.message || 'Error al cargar las tareas';
                }
            } catch (error) {
                console.error('Error al cargar tareas:', error);
                this.error = 'Error de conexión. Por favor, inténtalo de nuevo.';
            } finally {
                this.loading = false;
            }
        },

        buscarConRetraso() {
            if (this.busquedaTimeout) {
                clearTimeout(this.busquedaTimeout);
            }
            
            this.busquedaTimeout = setTimeout(() => {
                this.cargarTareas();
            }, 500);
        },

        cambiarPagina(pagina) {
            if (pagina >= 1 && pagina <= this.paginacion.last_page) {
                this.cargarTareas(pagina);
            }
        },

        limpiarFiltros() {
            this.filtros.search = '';
            this.filtros.estado = '';
            this.filtros.prioridad = '';
            this.filtros.tipo = '';
            this.cargarTareas();
        },

        // Funciones de selección masiva
        toggleTodasSeleccionadas() {
            if (this.todasSeleccionadas) {
                this.tareasSeleccionadas = this.tareas.map(tarea => tarea.id);
            } else {
                this.tareasSeleccionadas = [];
            }
        },

        limpiarSeleccion() {
            this.tareasSeleccionadas = [];
            this.todasSeleccionadas = false;
        },

        // Funciones de columnas
        mostrarTodasColumnas() {
            Object.keys(this.columnasVisibles).forEach(key => {
                this.columnasVisibles[key] = true;
            });
        },

        // Funciones de acciones masivas
        async completarTareasMasivo() {
            
            try {
                for (const tareaId of this.tareasSeleccionadas) {
                    await axios.post(`/crm-bioscom/public/api/agenda/tareas/${tareaId}/completar`);
                }
                
                this.mostrarMensaje(`${this.tareasSeleccionadas.length} tarea(s) completada(s) exitosamente`, 'success');
                this.limpiarSeleccion();
                this.cargarTareas(this.paginacion.current_page);
            } catch (error) {
                console.error('Error al completar tareas:', error);
                this.mostrarMensaje('Error al completar algunas tareas', 'error');
            }
        },

        cambiarEstadoMasivo() {
            this.mostrarModalEstado = true;
        },

        async aplicarCambioEstadoMasivo() {
            try {
                for (const tareaId of this.tareasSeleccionadas) {
                    await axios.put(`/crm-bioscom/public/api/agenda/tareas/${tareaId}`, {
                        estado: this.nuevoEstadoMasivo
                    });
                }
                
                this.mostrarMensaje(`Estado actualizado para ${this.tareasSeleccionadas.length} tarea(s)`, 'success');
                this.mostrarModalEstado = false;
                this.limpiarSeleccion();
                this.cargarTareas(this.paginacion.current_page);
            } catch (error) {
                console.error('Error al cambiar estado:', error);
                this.mostrarMensaje('Error al cambiar el estado', 'error');
            }
        },

        async completarTarea(tarea) {
            if (!confirm(`¿Estás seguro de marcar como completada la tarea "${tarea.titulo}"?`)) {
                return;
            }

            try {
                const response = await axios.post(`/crm-bioscom/public/api/agenda/tareas/${tarea.id}/completar`);
                
                if (response.data.success) {
                    const index = this.tareas.findIndex(t => t.id === tarea.id);
                    if (index !== -1) {
                        this.tareas[index] = response.data.data;
                    }
                    
                    this.mostrarMensaje('Tarea completada exitosamente', 'success');
                    this.cargarTareas(this.paginacion.current_page);
                } else {
                    this.mostrarMensaje(response.data.message || 'Error al completar la tarea', 'error');
                }
            } catch (error) {
                console.error('Error al completar tarea:', error);
                this.mostrarMensaje('Error de conexión', 'error');
            }
        },

        verTarea(tarea) {
            console.log('Ver tarea:', tarea);
            alert(`Ver detalles de: ${tarea.titulo}`);
        },

        editarTarea(tarea) {
            console.log('Editar tarea:', tarea);
            alert(`Editar: ${tarea.titulo}`);
        },

        mostrarMensaje(mensaje, tipo = 'info') {
            if (window.mostrarToast) {
                window.mostrarToast(tipo, mensaje);
            } else {
                if (tipo === 'error') {
                    alert('Error: ' + mensaje);
                } else {
                    alert(mensaje);
                }
            }
        },

        // Métodos de formateo y estilos (mantener los existentes)
        getColorEstado(estado) {
            const colores = {
                'completada': 'text-green-600 bg-green-50 border-green-200',
                'en_progreso': 'text-blue-600 bg-blue-50 border-blue-200',
                'pendiente': 'text-yellow-600 bg-yellow-50 border-yellow-200',
                'vencida': 'text-red-600 bg-red-50 border-red-200',
                'pospuesta': 'text-gray-600 bg-gray-50 border-gray-200',
                'cancelada': 'text-red-600 bg-red-50 border-red-200'
            };
            return colores[estado] || 'text-gray-600 bg-gray-50 border-gray-200';
        },

        getColorPrioridad(prioridad) {
            const colores = {
                'urgente': 'text-red-600 bg-red-50 border-red-200',
                'alta': 'text-orange-600 bg-orange-50 border-orange-200',
                'media': 'text-blue-600 bg-blue-50 border-blue-200',
                'baja': 'text-gray-600 bg-gray-50 border-gray-200'
            };
            return colores[prioridad] || 'text-gray-600 bg-gray-50 border-gray-200';
        },

        getIconoEstado(estado) {
            const iconos = {
                'completada': 'fas fa-check-circle',
                'en_progreso': 'fas fa-play-circle',
                'pendiente': 'fas fa-clock',
                'vencida': 'fas fa-exclamation-triangle',
                'pospuesta': 'fas fa-pause-circle',
                'cancelada': 'fas fa-times-circle'
            };
            return iconos[estado] || 'fas fa-question-circle';
        },

        getIconoPrioridad(prioridad) {
            const iconos = {
                'urgente': 'fas fa-exclamation',
                'alta': 'fas fa-angle-double-up',
                'media': 'fas fa-minus',
                'baja': 'fas fa-angle-double-down'
            };
            return iconos[prioridad] || 'fas fa-minus';
        },

        getIconoTipo(tipo) {
            const iconos = {
                'seguimiento': { icon: 'fas fa-chart-line', class: 'bg-green-100 text-green-600' },
                'cotizacion': { icon: 'fas fa-file-invoice', class: 'bg-blue-100 text-blue-600' },
                'mantencion': { icon: 'fas fa-tools', class: 'bg-yellow-100 text-yellow-600' },
                'reunion': { icon: 'fas fa-users', class: 'bg-purple-100 text-purple-600' },
                'llamada': { icon: 'fas fa-phone', class: 'bg-indigo-100 text-indigo-600' },
                'email': { icon: 'fas fa-envelope', class: 'bg-gray-100 text-gray-600' },
                'visita': { icon: 'fas fa-map-marker-alt', class: 'bg-red-100 text-red-600' },
                'cobranza': { icon: 'fas fa-dollar-sign', class: 'bg-green-100 text-green-600' },
                'administrativa': { icon: 'fas fa-clipboard', class: 'bg-gray-100 text-gray-600' },
                'personal': { icon: 'fas fa-user', class: 'bg-pink-100 text-pink-600' }
            };
            return iconos[tipo] || { icon: 'fas fa-task', class: 'bg-gray-100 text-gray-600' };
        },

        getTextoEstado(estado) {
            const textos = {
                'completada': 'Completada',
                'en_progreso': 'En Progreso',
                'pendiente': 'Pendiente',
                'vencida': 'Vencida',
                'pospuesta': 'Pospuesta',
                'cancelada': 'Cancelada'
            };
            return textos[estado] || estado;
        },

        getTextoPrioridad(prioridad) {
            const textos = {
                'urgente': 'Urgente',
                'alta': 'Alta',
                'media': 'Media',
                'baja': 'Baja'
            };
            return textos[prioridad] || prioridad;
        },

        getTextoTipo(tipo) {
            const textos = {
                'seguimiento': 'Seguimiento',
                'cotizacion': 'Cotización',
                'mantencion': 'Mantención',
                'reunion': 'Reunión',
                'llamada': 'Llamada',
                'email': 'Email',
                'visita': 'Visita',
                'cobranza': 'Cobranza',
                'administrativa': 'Administrativa',
                'personal': 'Personal'
            };
            return textos[tipo] || tipo;
        },

        formatearHora(hora) {
            if (!hora) return '';
            try {
                const date = new Date(`2000-01-01T${hora}`);
                return date.toLocaleTimeString('es-CL', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
            } catch (e) {
                return hora;
            }
        },

        // Métodos de navegación temporal
        irAyer() {
            const fecha = new Date(this.fechaSeleccionada + 'T00:00:00');
            fecha.setDate(fecha.getDate() - 1);
            this.cambiarFecha(fecha.toISOString().split('T')[0]);
        },

        irManana() {
            const fecha = new Date(this.fechaSeleccionada + 'T00:00:00');
            fecha.setDate(fecha.getDate() + 1);
            this.cambiarFecha(fecha.toISOString().split('T')[0]);
        },

        irHoy() {
            this.cambiarFecha(this.fechaHoy);
        },

        seleccionarFecha() {
            this.cambiarFecha(this.fechaSeleccionadaInput);
        },

        cambiarFecha(nuevaFecha) {
            console.log('📅 Cambiando fecha a:', nuevaFecha);
            this.fechaSeleccionada = nuevaFecha;
            this.fechaSeleccionadaInput = nuevaFecha;
            
            // Recargar tareas para la nueva fecha
            this.cargarTareas();
        },
    }
}
</script>