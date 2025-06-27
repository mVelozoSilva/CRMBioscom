<template>
    <div>
        <!-- Loading State -->
        <div v-if="loading" class="p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                <i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i>
            </div>
            <p class="text-gray-600">Cargando tareas de la semana...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
            </div>
            <p class="text-red-600 mb-4">{{ error }}</p>
            <button @click="cargarTareasSemana" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <i class="fas fa-redo mr-2"></i>
                Reintentar
            </button>
        </div>

        <!-- Main Content -->
        <div v-else>
            <!-- Week Navigation -->
            <div class="mb-6 flex items-center justify-between bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <button @click="cambiarSemana('anterior')" class="flex items-center px-3 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="fas fa-chevron-left mr-2"></i>
                    Semana Anterior
                </button>
                
                <div class="text-center">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ estadisticas.fecha_inicio }} - {{ estadisticas.fecha_fin }}
                    </h3>
                    <p class="text-sm text-gray-600">
                        {{ estadisticas.total_semana }} tareas esta semana
                    </p>
                </div>
                
                <button @click="cambiarSemana('siguiente')" class="flex items-center px-3 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    Semana Siguiente
                    <i class="fas fa-chevron-right ml-2"></i>
                </button>
            </div>

            <!-- Week Stats -->
            <div class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-list text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total</p>
                            <p class="text-xl font-semibold text-gray-900">{{ estadisticas.total_semana }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-clock text-yellow-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Pendientes</p>
                            <p class="text-xl font-semibold text-gray-900">{{ estadisticas.pendientes }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-check text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Completadas</p>
                            <p class="text-xl font-semibold text-gray-900">{{ estadisticas.completadas }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Vencidas</p>
                            <p class="text-xl font-semibold text-gray-900">{{ estadisticas.vencidas }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Week Calendar Grid -->
            <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
                <div v-for="dia in diasSemana" :key="dia.fecha" class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <!-- Day Header -->
                    <div class="p-4 border-b border-gray-200" :class="{
                        'bg-blue-50 border-blue-200': dia.es_hoy,
                        'bg-gray-50': dia.es_pasado && !dia.es_hoy
                    }">
                        <div class="text-center">
                            <h4 class="font-semibold text-gray-900" :class="{ 'text-blue-600': dia.es_hoy }">
                                {{ dia.dia_nombre }}
                            </h4>
                            <p class="text-2xl font-bold" :class="{ 'text-blue-600': dia.es_hoy, 'text-gray-900': !dia.es_hoy }">
                                {{ dia.dia_numero }}
                            </p>
                            <p class="text-xs text-gray-500 uppercase">{{ dia.mes_nombre }}</p>
                            <div v-if="dia.es_hoy" class="mt-1">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Hoy
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Day Tasks -->
                    <div class="p-3 space-y-2" style="min-height: 200px;">
                        <!-- Empty State -->
                        <div v-if="dia.tareas.length === 0" class="text-center py-8">
                            <i class="fas fa-calendar-check text-2xl text-gray-300 mb-2"></i>
                            <p class="text-xs text-gray-400">Sin tareas</p>
                        </div>

                        <!-- Tasks List -->
                        <div v-for="tarea in dia.tareas" :key="tarea.id" class="relative group">
                            <div class="p-2 rounded-lg border transition-all hover:shadow-sm cursor-pointer" :class="getEstiloTarea(tarea)" @click="verTarea(tarea)">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium truncate" :class="getColorTextoTarea(tarea)">
                                            {{ tarea.titulo }}
                                        </p>
                                        <div class="flex items-center mt-1 space-x-2">
                                            <span v-if="tarea.hora_estimada" class="inline-flex items-center text-xs text-gray-500">
                                                <i class="fas fa-clock mr-1"></i>
                                                {{ formatearHora(tarea.hora_estimada) }}
                                            </span>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs" :class="getColorTipo(tarea.tipo)">
                                                <i :class="getIconoTipo(tarea.tipo)" class="mr-1"></i>
                                                {{ getTextoTipo(tarea.tipo) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 ml-2">
                                        <button v-if="tarea.estado === 'pendiente'" @click.stop="completarTarea(tarea)" class="opacity-0 group-hover:opacity-100 transition-opacity text-green-600 hover:text-green-800">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TareaWeekView',
    
    data() {
        return {
            loading: true,
            error: null,
            semanaActual: 'actual', // 'anterior', 'actual', 'siguiente'
            diasSemana: [],
            estadisticas: {
                total_semana: 0,
                completadas: 0,
                pendientes: 0,
                vencidas: 0,
                fecha_inicio: '',
                fecha_fin: ''
            }
        }
    },

    mounted() {
        this.cargarTareasSemana();
    },

    methods: {
        async cargarTareasSemana() {
            this.loading = true;
            this.error = null;

            try {
                const params = new URLSearchParams({
                    semana: this.semanaActual
                });

                const response = await axios.get(`/crm-bioscom/public/api/agenda/tareas-semana?${params}`);
                
                if (response.data.success) {
                    this.diasSemana = Object.values(response.data.data);
                    this.estadisticas = response.data.estadisticas;
                    // DESPUÉS de la línea: this.estadisticas = response.data.estadisticas;
                    console.log('🐛 DEBUG - diasSemana después de asignar:', this.diasSemana);
                    console.log('🐛 DEBUG - loading:', this.loading);
                    console.log('🐛 DEBUG - error:', this.error);
                    console.log('🐛 DEBUG - primer día tareas:', this.diasSemana[0]?.tareas);
                    
                    console.log('📅 Tareas semanales cargadas:', this.diasSemana);
                } else {
                    this.error = response.data.message || 'Error al cargar las tareas semanales';
                }
            } catch (error) {
                console.error('Error al cargar tareas semanales:', error);
                this.error = 'Error de conexión. Por favor, inténtalo de nuevo.';
            } finally {
                this.loading = false;
            }
        },

        cambiarSemana(direccion) {
            if (direccion === 'anterior') {
                this.semanaActual = this.semanaActual === 'actual' ? 'anterior' : 'anterior';
            } else if (direccion === 'siguiente') {
                this.semanaActual = this.semanaActual === 'actual' ? 'siguiente' : 'siguiente';
            }
            
            this.cargarTareasSemana();
        },

        async completarTarea(tarea) {
            try {
                const response = await axios.post(`/crm-bioscom/public/api/agenda/tareas/${tarea.id}/completar`);
                
                if (response.data.success) {
                    this.mostrarMensaje('Tarea completada exitosamente', 'success');
                    this.cargarTareasSemana(); // Recargar
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

        // Funciones de estilo
        getEstiloTarea(tarea) {
            const estilos = {
                'completada': 'bg-green-50 border-green-200',
                'en_progreso': 'bg-blue-50 border-blue-200',
                'pendiente': 'bg-yellow-50 border-yellow-200',
                'vencida': 'bg-red-50 border-red-200',
                'pospuesta': 'bg-gray-50 border-gray-200'
            };
            return estilos[tarea.estado] || 'bg-gray-50 border-gray-200';
        },

        getColorTextoTarea(tarea) {
            const colores = {
                'completada': 'text-green-700',
                'en_progreso': 'text-blue-700',
                'pendiente': 'text-yellow-700',
                'vencida': 'text-red-700',
                'pospuesta': 'text-gray-700'
            };
            return colores[tarea.estado] || 'text-gray-700';
        },

        getColorTipo(tipo) {
            const colores = {
                'seguimiento': 'bg-green-100 text-green-700',
                'cotizacion': 'bg-blue-100 text-blue-700',
                'mantencion': 'bg-yellow-100 text-yellow-700',
                'reunion': 'bg-purple-100 text-purple-700',
                'llamada': 'bg-indigo-100 text-indigo-700',
                'email': 'bg-gray-100 text-gray-700',
                'visita': 'bg-red-100 text-red-700',
                'cobranza': 'bg-green-100 text-green-700'
            };
            return colores[tipo] || 'bg-gray-100 text-gray-700';
        },

        getIconoTipo(tipo) {
            const iconos = {
                'seguimiento': 'fas fa-chart-line',
                'cotizacion': 'fas fa-file-invoice',
                'mantencion': 'fas fa-tools',
                'reunion': 'fas fa-users',
                'llamada': 'fas fa-phone',
                'email': 'fas fa-envelope',
                'visita': 'fas fa-map-marker-alt',
                'cobranza': 'fas fa-dollar-sign'
            };
            return iconos[tipo] || 'fas fa-task';
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
                'cobranza': 'Cobranza'
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
        }
    }
}
</script>