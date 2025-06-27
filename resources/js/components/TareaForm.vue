<template>
    <div v-if="mostrarModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="cerrarModal">
        <div class="relative top-10 mx-auto p-5 border w-[700px] shadow-xl rounded-xl bg-white" @click.stop>
            
            <!-- 🎯 HEADER INTELIGENTE -->
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-200">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-magic text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">
                            {{ editando ? 'Editar Tarea' : 'Nueva Tarea' }}
                        </h3>
                        <p class="text-sm text-gray-500">{{ modoRapido ? 'Creación rápida' : 'Configuración completa' }}</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <!-- Toggle Modo Rápido/Completo -->
                    <div class="flex items-center bg-gray-100 rounded-lg p-1">
                        <button 
                            @click="modoRapido = true" 
                            :class="modoRapido ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                            class="px-3 py-1 rounded-md text-sm font-medium transition-all"
                        >
                            <i class="fas fa-bolt mr-1"></i>Rápido
                        </button>
                        <button 
                            @click="modoRapido = false" 
                            :class="!modoRapido ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                            class="px-3 py-1 rounded-md text-sm font-medium transition-all"
                        >
                            <i class="fas fa-cogs mr-1"></i>Completo
                        </button>
                    </div>
                    
                    <button @click="cerrarModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- 🚀 SHORTCUTS DE TAREAS COMUNES -->
            <div class="mb-6" v-if="!editando">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-zap text-yellow-500 mr-1"></i>
                    Shortcuts Rápidos
                </h4>
                <div class="grid grid-cols-4 gap-2">
                    <button 
                        v-for="shortcut in shortcuts" 
                        :key="shortcut.tipo"
                        @click="aplicarShortcut(shortcut)"
                        class="p-3 border-2 border-transparent rounded-lg hover:border-blue-300 hover:shadow-md transition-all group"
                        :class="formulario.tipo === shortcut.tipo ? 'border-blue-400 bg-blue-50' : 'bg-gray-50 hover:bg-white'"
                    >
                        <div :class="shortcut.color" class="w-8 h-8 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                            <i :class="shortcut.icono" class="text-sm"></i>
                        </div>
                        <div class="text-xs font-medium text-gray-700">{{ shortcut.nombre }}</div>
                    </button>
                </div>
            </div>

            <!-- 📝 FORMULARIO INTELIGENTE -->
            <form @submit.prevent="guardarTarea" @keydown.enter.prevent="guardarTarea" @keydown.esc="cerrarModal">
                
                <!-- 🚀 MODO RÁPIDO -->
                <div v-if="modoRapido" class="space-y-4">
                    
                    <!-- Título con Auto-complete Inteligente -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-edit text-blue-500 mr-1"></i>
                            ¿Qué tienes que hacer? <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                ref="inputTitulo"
                                v-model="formulario.titulo"
                                @input="buscarSugerenciasTitulo"
                                type="text"
                                placeholder="Ej: Llamar a cliente XYZ"
                                class="w-full text-lg border-2 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                :class="validacion.titulo ? 'border-green-300 bg-green-50' : 'border-gray-300'"
                                required
                            >
                            
                            <!-- Sugerencias Auto-complete -->
                            <div v-if="sugerenciasTitulo.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-40 overflow-y-auto">
                                <button 
                                    v-for="sugerencia in sugerenciasTitulo" 
                                    :key="sugerencia"
                                    @click="seleccionarSugerencia(sugerencia)"
                                    type="button"
                                    class="w-full text-left px-4 py-2 hover:bg-blue-50 transition-colors text-sm"
                                >
                                    <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>{{ sugerencia }}
                                </button>
                            </div>
                            
                            <!-- Indicador de validación -->
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <i v-if="validacion.titulo" class="fas fa-check text-green-500"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Fecha Inteligente -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-calendar text-green-500 mr-1"></i>
                                ¿Para cuándo?
                            </label>
                            <div class="flex space-x-2">
                                <button 
                                    v-for="opcionFecha in opcionesFechaRapida" 
                                    :key="opcionFecha.valor"
                                    @click="seleccionarFechaRapida(opcionFecha)"
                                    type="button"
                                    class="flex-1 p-2 text-xs rounded-lg border-2 transition-all"
                                    :class="formulario.fecha_vencimiento === opcionFecha.valor ? 'border-blue-400 bg-blue-50 text-blue-700' : 'border-gray-200 hover:border-blue-300'"
                                >
                                    <div class="font-semibold">{{ opcionFecha.nombre }}</div>
                                    <div class="text-gray-500">{{ opcionFecha.fecha }}</div>
                                </button>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-flag text-orange-500 mr-1"></i>
                                Urgencia
                            </label>
                            <div class="flex space-x-1">
                                <button 
                                    v-for="opcionPrioridad in opcionesPrioridad" 
                                    :key="opcionPrioridad.valor"
                                    @click="formulario.prioridad = opcionPrioridad.valor"
                                    type="button"
                                    class="flex-1 p-2 text-xs rounded-lg border-2 transition-all"
                                    :class="formulario.prioridad === opcionPrioridad.valor ? opcionPrioridad.estiloSeleccionado : opcionPrioridad.estiloNormal"
                                >
                                    <i :class="opcionPrioridad.icono"></i>
                                    <div class="font-semibold">{{ opcionPrioridad.nombre }}</div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Botón Crear Gigante -->
                    <div class="pt-4">
                        <button 
                            type="submit" 
                            :disabled="guardando || !formularioValidoRapido"
                            class="w-full py-4 bg-gradient-to-r from-green-500 to-blue-500 text-white rounded-xl font-bold text-lg hover:from-green-600 hover:to-blue-600 focus:outline-none focus:ring-4 focus:ring-green-300 shadow-lg hover:shadow-xl transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                        >
                            <i v-if="guardando" class="fas fa-spinner fa-spin mr-3"></i>
                            <i v-else class="fas fa-rocket mr-3"></i>
                            {{ guardando ? 'Creando...' : (editando ? 'ACTUALIZAR TAREA' : 'CREAR TAREA') }}
                        </button>
                    </div>

                </div>

                <!-- 🔧 MODO COMPLETO -->
                <div v-else class="space-y-4">
                    
                    <!-- Título -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Título de la Tarea <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="formulario.titulo"
                            type="text"
                            placeholder="Ej: Llamar a cliente XYZ"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea
                            v-model="formulario.descripcion"
                            rows="2"
                            placeholder="Detalles adicionales de la tarea..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        ></textarea>
                    </div>

                    <!-- Fila 1: Tipo, Prioridad, Estado -->
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                            <select v-model="formulario.tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="seguimiento">Seguimiento</option>
                                <option value="cotizacion">Cotización</option>
                                <option value="mantencion">Mantención</option>
                                <option value="reunion">Reunión</option>
                                <option value="llamada">Llamada</option>
                                <option value="email">Email</option>
                                <option value="visita">Visita</option>
                                <option value="cobranza">Cobranza</option>
                                <option value="administrativa">Administrativa</option>
                                <option value="personal">Personal</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prioridad</label>
                            <select v-model="formulario.prioridad" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="baja">Baja</option>
                                <option value="media">Media</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select v-model="formulario.estado" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="pendiente">Pendiente</option>
                                <option value="en_progreso">En Progreso</option>
                                <option value="completada">Completada</option>
                                <option value="pospuesta">Pospuesta</option>
                            </select>
                        </div>
                    </div>

                    <!-- Fila 2: Fecha, Hora -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Fecha de Vencimiento <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="formulario.fecha_vencimiento"
                                type="date"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hora Estimada</label>
                            <input
                                v-model="formulario.hora_estimada"
                                type="time"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>
                    </div>

                    <!-- Duración estimada -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Duración Estimada (minutos)</label>
                        <input
                            v-model.number="formulario.duracion_estimada_minutos"
                            type="number"
                            min="1"
                            max="480"
                            placeholder="Ej: 30"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>

                    <!-- Notas -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                        <textarea
                            v-model="formulario.notas"
                            rows="2"
                            placeholder="Notas adicionales..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        ></textarea>
                    </div>

                    <!-- Botones del Modo Completo -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button 
                            @click="cerrarModal" 
                            type="button" 
                            class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors"
                            :disabled="guardando"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center"
                            :disabled="guardando"
                        >
                            <i v-if="guardando" class="fas fa-spinner fa-spin mr-2"></i>
                            <i v-else class="fas fa-save mr-2"></i>
                            {{ guardando ? 'Guardando...' : (editando ? 'Actualizar' : 'Crear Tarea') }}
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TareaForm',
    
    props: {
        visible: {
            type: Boolean,
            default: false
        },
        tarea: {
            type: Object,
            default: null
        }
    },

    data() {
        return {
            mostrarModal: false,
            guardando: false,
            editando: false,
            modoRapido: true,
            
            formulario: {
                titulo: '',
                descripcion: '',
                tipo: 'seguimiento',
                prioridad: 'media',
                estado: 'pendiente',
                fecha_vencimiento: '',
                hora_estimada: '',
                duracion_estimada_minutos: null,
                notas: ''
            },

            validacion: {
                titulo: false
            },

            sugerenciasTitulo: [],

            // 🚀 Shortcuts de tareas comunes
            shortcuts: [
                {
                    tipo: 'llamada',
                    nombre: 'Llamada',
                    icono: 'fas fa-phone',
                    color: 'bg-green-100 text-green-600',
                    duracion: 15,
                    plantilla: 'Llamar a {cliente}'
                },
                {
                    tipo: 'reunion',
                    nombre: 'Reunión',
                    icono: 'fas fa-users',
                    color: 'bg-blue-100 text-blue-600',
                    duracion: 60,
                    plantilla: 'Reunión con {cliente}'
                },
                {
                    tipo: 'seguimiento',
                    nombre: 'Seguimiento',
                    icono: 'fas fa-chart-line',
                    color: 'bg-purple-100 text-purple-600',
                    duracion: 30,
                    plantilla: 'Seguimiento {cliente}'
                },
                {
                    tipo: 'email',
                    nombre: 'Email',
                    icono: 'fas fa-envelope',
                    color: 'bg-yellow-100 text-yellow-600',
                    duracion: 10,
                    plantilla: 'Enviar email a {cliente}'
                }
            ],

            // 📅 Opciones de fecha rápida
            opcionesFechaRapida: [],

            // 🚩 Opciones de prioridad visual
            opcionesPrioridad: [
                {
                    valor: 'baja',
                    nombre: 'Baja',
                    icono: 'fas fa-arrow-down',
                    estiloSeleccionado: 'border-gray-400 bg-gray-50 text-gray-700',
                    estiloNormal: 'border-gray-200 hover:border-gray-300'
                },
                {
                    valor: 'media',
                    nombre: 'Media',
                    icono: 'fas fa-minus',
                    estiloSeleccionado: 'border-blue-400 bg-blue-50 text-blue-700',
                    estiloNormal: 'border-gray-200 hover:border-blue-300'
                },
                {
                    valor: 'alta',
                    nombre: 'Alta',
                    icono: 'fas fa-arrow-up',
                    estiloSeleccionado: 'border-orange-400 bg-orange-50 text-orange-700',
                    estiloNormal: 'border-gray-200 hover:border-orange-300'
                },
                {
                    valor: 'urgente',
                    nombre: 'Urgente',
                    icono: 'fas fa-exclamation',
                    estiloSeleccionado: 'border-red-400 bg-red-50 text-red-700',
                    estiloNormal: 'border-gray-200 hover:border-red-300'
                }
            ]
        }
    },

    computed: {
        formularioValidoRapido() {
            return this.formulario.titulo.length >= 3 && this.formulario.fecha_vencimiento;
        }
    },

    watch: {
        visible(newVal) {
            this.mostrarModal = newVal;
            if (newVal) {
                this.inicializarFormulario();
                this.generarOpcionesFechaRapida();
                // Focus en el input principal después de un delay
                setTimeout(() => {
                    if (this.$refs.inputTitulo) {
                        this.$refs.inputTitulo.focus();
                    }
                }, 300);
            }
        },

        'formulario.titulo'(newVal) {
            this.validacion.titulo = newVal.length >= 3;
        },

        tarea(newVal) {
            if (newVal) {
                this.editando = true;
                this.cargarDatosTarea(newVal);
            } else {
                this.editando = false;
                this.limpiarFormulario();
            }
        }
    },

    mounted() {
        this.mostrarModal = this.visible;
        this.inicializarFormulario();
        this.generarOpcionesFechaRapida();
    },

    methods: {
        // 🧠 Funciones de Inteligencia
        generarOpcionesFechaRapida() {
            const hoy = new Date();
            const manana = new Date(hoy);
            manana.setDate(hoy.getDate() + 1);
            const pasadoManana = new Date(hoy);
            pasadoManana.setDate(hoy.getDate() + 2);
            const proximaSemanana = new Date(hoy);
            proximaSemanana.setDate(hoy.getDate() + 7);

            this.opcionesFechaRapida = [
                {
                    nombre: 'Hoy',
                    fecha: hoy.toLocaleDateString('es-CL'),
                    valor: hoy.toISOString().split('T')[0]
                },
                {
                    nombre: 'Mañana',
                    fecha: manana.toLocaleDateString('es-CL'),
                    valor: manana.toISOString().split('T')[0]
                },
                {
                    nombre: 'Pasado',
                    fecha: pasadoManana.toLocaleDateString('es-CL'),
                    valor: pasadoManana.toISOString().split('T')[0]
                }
            ];
        },

        seleccionarFechaRapida(opcion) {
            this.formulario.fecha_vencimiento = opcion.valor;
        },

        aplicarShortcut(shortcut) {
            this.formulario.tipo = shortcut.tipo;
            this.formulario.duracion_estimada_minutos = shortcut.duracion;
            
            // Si no hay título, aplicar plantilla
            if (!this.formulario.titulo) {
                this.formulario.titulo = shortcut.plantilla.replace('{cliente}', '');
            }
        },

        buscarSugerenciasTitulo() {
            const titulo = this.formulario.titulo.toLowerCase();
            
            if (titulo.length < 2) {
                this.sugerenciasTitulo = [];
                return;
            }

            // Sugerencias inteligentes basadas en el tipo
            const sugerenciasComunes = [
                'Llamar a cliente para seguimiento',
                'Enviar cotización por email',
                'Reunión de planificación',
                'Revisión de propuesta',
                'Seguimiento de oportunidad comercial',
                'Preparar presentación técnica',
                'Coordinar instalación de equipo',
                'Gestión de cobranza pendiente'
            ];

            this.sugerenciasTitulo = sugerenciasComunes.filter(s => 
                s.toLowerCase().includes(titulo)
            ).slice(0, 5);
        },

        seleccionarSugerencia(sugerencia) {
            this.formulario.titulo = sugerencia;
            this.sugerenciasTitulo = [];
        },

        // 📝 Funciones de formulario existentes
        inicializarFormulario() {
            if (this.tarea) {
                this.editando = true;
                this.cargarDatosTarea(this.tarea);
            } else {
                this.editando = false;
                this.limpiarFormulario();
                this.establecerValoresPorDefectoInteligentes();
            }
        },

        establecerValoresPorDefectoInteligentes() {
            const ahora = new Date();
            const esViernes = ahora.getDay() === 5;
            const esTarde = ahora.getHours() >= 16;
            
            // Si es viernes tarde o muy tarde cualquier día, sugerir lunes
            if (esViernes && esTarde || ahora.getHours() >= 18) {
                const proximoLunes = new Date(ahora);
                proximoLunes.setDate(ahora.getDate() + (8 - ahora.getDay()) % 7);
                this.formulario.fecha_vencimiento = proximoLunes.toISOString().split('T')[0];
            } else {
                // Sino, mañana
                const manana = new Date(ahora);
                manana.setDate(ahora.getDate() + 1);
                this.formulario.fecha_vencimiento = manana.toISOString().split('T')[0];
            }
        },

        cargarDatosTarea(tarea) {
            this.formulario = {
                titulo: tarea.titulo || '',
                descripcion: tarea.descripcion || '',
                tipo: tarea.tipo || 'seguimiento',
                prioridad: tarea.prioridad || 'media',
                estado: tarea.estado || 'pendiente',
                fecha_vencimiento: tarea.fecha_vencimiento || '',
                hora_estimada: tarea.hora_estimada || '',
                duracion_estimada_minutos: tarea.duracion_estimada_minutos || null,
                notas: tarea.notas || ''
            };
        },

        limpiarFormulario() {
            this.formulario = {
                titulo: '',
                descripcion: '',
                tipo: 'seguimiento',
                prioridad: 'media',
                estado: 'pendiente',
                fecha_vencimiento: new Date().toISOString().split('T')[0],
                hora_estimada: '',
                duracion_estimada_minutos: null,
                notas: ''
            };
            this.validacion.titulo = false;
        },

        async guardarTarea() {
            this.guardando = true;

            try {
                // Agregar campos requeridos que faltan
                const datosCompletos = {
                    ...this.formulario,
                    usuario_asignado_id: 1, // TODO: usar usuario actual
                    usuario_creador_id: 1   // TODO: usar usuario actual
                };

                let response;
                
                if (this.editando) {
                    response = await axios.put(`/crm-bioscom/public/api/agenda/tareas/${this.tarea.id}`, datosCompletos);
                } else {
                    response = await axios.post('/crm-bioscom/public/api/agenda/tareas', datosCompletos);
                }

                if (response.data.success) {
                    this.mostrarMensaje(
                        this.editando ? 'Tarea actualizada exitosamente' : '🎉 ¡Tarea creada exitosamente!', 
                        'success'
                    );
                    
                    this.$emit('tarea-guardada', response.data.data);
                    this.cerrarModal();
                } else {
                    this.mostrarMensaje(response.data.message || 'Error al guardar la tarea', 'error');
                }
            } catch (error) {
                console.error('Error al guardar tarea:', error);
                
                if (error.response && error.response.status === 422) {
                    const errores = error.response.data.errors;
                    const mensajes = Object.values(errores).flat();
                    this.mostrarMensaje(`Error de validación: ${mensajes.join(', ')}`, 'error');
                } else {
                    this.mostrarMensaje('Error de conexión. Por favor, inténtalo de nuevo.', 'error');
                }
            } finally {
                this.guardando = false;
            }
        },

        cerrarModal() {
            this.mostrarModal = false;
            this.sugerenciasTitulo = [];
            this.$emit('cerrar');
            
            setTimeout(() => {
                this.limpiarFormulario();
                this.editando = false;
                this.modoRapido = true;
            }, 300);
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
        }
    }
}
</script>