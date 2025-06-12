<template>
  <div class="seguimiento-container">
    <!-- BARRA DE HERRAMIENTAS -->
    <div class="bg-white shadow-sm border-b border-gray-200 p-4">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        
        <!-- FILTROS RÁPIDOS (CRÍTICOS) -->
        <div class="flex flex-wrap gap-2">
          <button
            @click="aplicarFiltroRapido('atrasados')"
            :class="['btn-filter', filtroActivo === 'atrasados' ? 'btn-filter-active bg-red-100 text-red-700 border-red-300' : 'hover:bg-red-50']"
          >
            <i class="fas fa-exclamation-triangle mr-1"></i>
            Atrasados ({{ estadisticas.atrasados || 0 }})
          </button>
          
          <button
            @click="aplicarFiltroRapido('proximos')"
            :class="['btn-filter', filtroActivo === 'proximos' ? 'btn-filter-active bg-yellow-100 text-yellow-700 border-yellow-300' : 'hover:bg-yellow-50']"
          >
            <i class="fas fa-clock mr-1"></i>
            Próximos 7 días ({{ estadisticas.proximos_7_dias || 0 }})
          </button>
          
          <button
            @click="aplicarFiltroRapido('hoy')"
            :class="['btn-filter', filtroActivo === 'hoy' ? 'btn-filter-active bg-blue-100 text-blue-700 border-blue-300' : 'hover:bg-blue-50']"
          >
            <i class="fas fa-calendar-day mr-1"></i>
            Hoy ({{ estadisticas.hoy || 0 }})
          </button>
          
          <button
            @click="aplicarFiltroRapido('todos')"
            :class="['btn-filter', filtroActivo === 'todos' ? 'btn-filter-active bg-green-100 text-green-700 border-green-300' : 'hover:bg-green-50']"
          >
            <i class="fas fa-list mr-1"></i>
            Todos ({{ estadisticas.total_activos || 0 }})
          </button>

          <button
            @click="aplicarFiltroRapido('completados_hoy')"
            :class="['btn-filter', filtroActivo === 'completados_hoy' ? 'btn-filter-active bg-purple-100 text-purple-700 border-purple-300' : 'hover:bg-purple-50']"
          >
            <i class="fas fa-check-circle mr-1"></i>
            Completados Hoy ({{ estadisticas.completados_hoy || 0 }})
          </button>
        </div>

        <!-- ACCIONES PRINCIPALES -->
        <div class="flex gap-2">
          <button
            @click="mostrarModalCrear = true"
            class="btn-primary"
          >
            <i class="fas fa-plus mr-1"></i>
            Nuevo Seguimiento
          </button>
          
          <button
            @click="actualizarMasivo"
            :disabled="seguimientosSeleccionados.length === 0"
            :class="['btn-secondary', seguimientosSeleccionados.length === 0 ? 'opacity-50 cursor-not-allowed' : '']"
          >
            <i class="fas fa-edit mr-1"></i>
            Actualizar Seleccionados ({{ seguimientosSeleccionados.length }})
          </button>
          
          <button
            @click="mostrarModalImportar = true"
            class="btn-outline"
            v-if="puedeImportar"
          >
            <i class="fas fa-upload mr-1"></i>
            Importar Excel
          </button>
        </div>
      </div>

      <!-- FILTROS AVANZADOS -->
      <div class="mt-4 flex flex-col lg:flex-row gap-4">
        <!-- Búsqueda rápida -->
        <div class="flex-1">
          <div class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            <input
              v-model="filtros.busqueda"
              @input="buscarConRetraso"
              type="text"
              placeholder="Buscar por cliente, RUT o cotización..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
          </div>
        </div>

        <!-- Filtros adicionales -->
        <div class="flex gap-2">
          <select v-model="filtros.vendedor" @change="aplicarFiltros" class="form-select">
            <option value="">Todos los vendedores</option>
            <option 
              v-for="vendedor in vendedores" 
              :key="vendedor.id" 
              :value="vendedor.id"
            >
              {{ vendedor.name }}
            </option>
          </select>

          <select v-model="filtros.estado" @change="aplicarFiltros" class="form-select">
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendiente</option>
            <option value="en_proceso">En Proceso</option>
            <option value="completado">Completado</option>
            <option value="vencido">Vencido</option>
            <option value="reprogramado">Reprogramado</option>
          </select>

          <select v-model="filtros.prioridad" @change="aplicarFiltros" class="form-select">
            <option value="">Todas las prioridades</option>
            <option value="baja">Baja</option>
            <option value="media">Media</option>
            <option value="alta">Alta</option>
            <option value="urgente">Urgente</option>
          </select>

          <button @click="limpiarFiltros" class="btn-outline">
            <i class="fas fa-times mr-1"></i>
            Limpiar
          </button>
        </div>
      </div>
    </div>

    <!-- TABLA PRINCIPAL - TIPO EXCEL EDITABLE -->
    <div class="bg-white shadow overflow-hidden">
      <!-- Loading Overlay -->
      <div v-if="cargando" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10">
        <div class="flex items-center">
          <i class="fas fa-spinner fa-spin text-blue-500 mr-2"></i>
          Cargando seguimientos...
        </div>
      </div>

      <!-- Encabezados de tabla -->
      <div class="bg-gray-50 border-b border-gray-200">
        <div class="grid grid-cols-12 gap-2 p-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
          <div class="col-span-1">
            <input 
              type="checkbox" 
              v-model="todosMarcados" 
              @change="toggleTodos"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            >
          </div>
          <div class="col-span-2 cursor-pointer hover:text-gray-700" @click="ordenar('cliente')">
            Cliente 
            <i :class="getIconoOrden('cliente')" class="ml-1"></i>
          </div>
          <div class="col-span-1 cursor-pointer hover:text-gray-700" @click="ordenar('cotizacion')">
            Cotización
            <i :class="getIconoOrden('cotizacion')" class="ml-1"></i>
          </div>
          <div class="col-span-1 cursor-pointer hover:text-gray-700" @click="ordenar('vendedor')">
            Vendedor
            <i :class="getIconoOrden('vendedor')" class="ml-1"></i>
          </div>
          <div class="col-span-1 cursor-pointer hover:text-gray-700" @click="ordenar('estado')">
            Estado
            <i :class="getIconoOrden('estado')" class="ml-1"></i>
          </div>
          <div class="col-span-1 cursor-pointer hover:text-gray-700" @click="ordenar('prioridad')">
            Prioridad
            <i :class="getIconoOrden('prioridad')" class="ml-1"></i>
          </div>
          <div class="col-span-1 cursor-pointer hover:text-gray-700" @click="ordenar('ultima_gestion')">
            Últ. Gestión
            <i :class="getIconoOrden('ultima_gestion')" class="ml-1"></i>
          </div>
          <div class="col-span-1 cursor-pointer hover:text-gray-700" @click="ordenar('proxima_gestion')">
            Próx. Gestión
            <i :class="getIconoOrden('proxima_gestion')" class="ml-1"></i>
          </div>
          <div class="col-span-2">Notas</div>
          <div class="col-span-1">Acciones</div>
        </div>
      </div>

      <!-- Filas de datos -->
      <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
        <div 
          v-for="seguimiento in seguimientos" 
          :key="seguimiento.id"
          :class="[
            'grid grid-cols-12 gap-2 p-3 hover:bg-gray-50 transition-colors',
            getClaseFilaSeguimiento(seguimiento)
          ]"
        >
          <!-- Checkbox -->
          <div class="col-span-1 flex items-center">
            <input 
              type="checkbox" 
              :value="seguimiento.id" 
              v-model="seguimientosSeleccionados"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            >
          </div>

          <!-- Cliente -->
          <div class="col-span-2">
            <div class="text-sm font-medium text-gray-900">
              {{ seguimiento.cliente }}
            </div>
            <div class="text-xs text-gray-500" v-if="seguimiento.rut_cliente">
              {{ seguimiento.rut_cliente }}
            </div>
          </div>

          <!-- Cotización -->
          <div class="col-span-1">
            <span class="text-sm text-blue-600 hover:text-blue-800 cursor-pointer" 
                  @click="verCotizacion(seguimiento.cotizacion)">
              {{ seguimiento.cotizacion || 'Sin cotización' }}
            </span>
          </div>

          <!-- Vendedor -->
          <div class="col-span-1">
            <div class="text-sm text-gray-900">{{ seguimiento.vendedor }}</div>
          </div>

          <!-- Estado (EDITABLE) -->
          <div class="col-span-1">
            <select 
              :value="seguimiento.estado"
              @change="actualizarCampo(seguimiento.id, 'estado', $event.target.value)"
              :class="[
                'text-xs px-2 py-1 rounded border-0 focus:ring-2 focus:ring-blue-500',
                getClaseEstado(seguimiento.estado)
              ]"
            >
              <option value="pendiente">Pendiente</option>
              <option value="en_proceso">En Proceso</option>
              <option value="completado">Completado</option>
              <option value="vencido">Vencido</option>
              <option value="reprogramado">Reprogramado</option>
            </select>
          </div>

          <!-- Prioridad (EDITABLE) -->
          <div class="col-span-1">
            <select 
              :value="seguimiento.prioridad"
              @change="actualizarCampo(seguimiento.id, 'prioridad', $event.target.value)"
              :class="[
                'text-xs px-2 py-1 rounded border-0 focus:ring-2 focus:ring-blue-500',
                getClasePrioridad(seguimiento.prioridad)
              ]"
            >
              <option value="baja">Baja</option>
              <option value="media">Media</option>
              <option value="alta">Alta</option>
              <option value="urgente">Urgente</option>
            </select>
          </div>

          <!-- Última Gestión -->
          <div class="col-span-1">
            <span class="text-xs text-gray-600">
              {{ seguimiento.ultima_gestion || 'Sin gestión' }}
            </span>
          </div>

          <!-- Próxima Gestión (EDITABLE) -->
          <div class="col-span-1">
            <input 
              type="date"
              :value="seguimiento.proxima_gestion"
              @change="actualizarCampo(seguimiento.id, 'proxima_gestion', $event.target.value)"
              class="text-xs px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 w-full"
            >
            <div v-if="seguimiento.dias_atraso > 0" class="text-xs text-red-600 mt-1">
              {{ seguimiento.dias_atraso }} día(s) atrasado
            </div>
          </div>

          <!-- Notas (EDITABLE) -->
          <div class="col-span-2">
            <textarea 
              :value="seguimiento.notas"
              @blur="actualizarCampo(seguimiento.id, 'notas', $event.target.value)"
              placeholder="Agregar notas..."
              class="text-xs p-1 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 w-full resize-none"
              rows="2"
            ></textarea>
          </div>

          <!-- Acciones -->
          <div class="col-span-1 flex items-center gap-1">
            <button 
              @click="verDetalle(seguimiento)"
              class="p-1 text-blue-600 hover:text-blue-800"
              title="Ver detalle"
            >
              <i class="fas fa-eye text-xs"></i>
            </button>
            <button 
              @click="editarSeguimiento(seguimiento)"
              class="p-1 text-green-600 hover:text-green-800"
              title="Editar"
            >
              <i class="fas fa-edit text-xs"></i>
            </button>
            <button 
              @click="eliminarSeguimiento(seguimiento.id)"
              class="p-1 text-red-600 hover:text-red-800"
              title="Eliminar"
            >
              <i class="fas fa-trash text-xs"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sin resultados -->
      <div v-if="!cargando && seguimientos.length === 0" class="text-center py-12">
        <i class="fas fa-search text-gray-400 text-4xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No se encontraron seguimientos</h3>
        <p class="text-gray-500">Intenta ajustar los filtros o crear un nuevo seguimiento</p>
      </div>
    </div>

    <!-- PAGINACIÓN -->
    <div v-if="pagination.total > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
      <div class="flex items-center justify-between">
        <div class="flex-1 flex justify-between sm:hidden">
          <button 
            @click="cambiarPagina(pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1"
            class="btn-outline"
          >
            Anterior
          </button>
          <button 
            @click="cambiarPagina(pagination.current_page + 1)"
            :disabled="pagination.current_page >= pagination.last_page"
            class="btn-outline"
          >
            Siguiente
          </button>
        </div>
        
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Mostrando 
              <span class="font-medium">{{ (pagination.current_page - 1) * pagination.per_page + 1 }}</span>
              a 
              <span class="font-medium">{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</span>
              de 
              <span class="font-medium">{{ pagination.total }}</span>
              resultados
            </p>
          </div>
          
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
              <button 
                @click="cambiarPagina(pagination.current_page - 1)"
                :disabled="pagination.current_page <= 1"
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
              >
                <i class="fas fa-chevron-left"></i>
              </button>
              
              <button 
                v-for="pagina in paginas"
                :key="pagina"
                @click="cambiarPagina(pagina)"
                :class="[
                  'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                  pagina === pagination.current_page 
                    ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                ]"
              >
                {{ pagina }}
              </button>
              
              <button 
                @click="cambiarPagina(pagination.current_page + 1)"
                :disabled="pagination.current_page >= pagination.last_page"
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
              >
                <i class="fas fa-chevron-right"></i>
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL ACTUALIZACIÓN MASIVA -->
    <div v-if="mostrarModalMasivo" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Actualizar {{ seguimientosSeleccionados.length }} seguimiento(s)
          </h3>
          
          <form @submit.prevent="confirmarActualizacionMasiva">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
              <select v-model="actualizacionMasiva.estado" class="form-select w-full">
                <option value="">No cambiar</option>
                <option value="pendiente">Pendiente</option>
                <option value="en_proceso">En Proceso</option>
                <option value="completado">Completado</option>
                <option value="vencido">Vencido</option>
                <option value="reprogramado">Reprogramado</option>
              </select>
            </div>
            
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Prioridad</label>
              <select v-model="actualizacionMasiva.prioridad" class="form-select w-full">
                <option value="">No cambiar</option>
                <option value="baja">Baja</option>
                <option value="media">Media</option>
                <option value="alta">Alta</option>
                <option value="urgente">Urgente</option>
              </select>
            </div>
            
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Próxima Gestión</label>
              <input 
                type="date" 
                v-model="actualizacionMasiva.proxima_gestion" 
                class="form-input w-full"
              >
            </div>
            
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Vendedor</label>
              <select v-model="actualizacionMasiva.vendedor_id" class="form-select w-full">
                <option value="">No cambiar</option>
                <option 
                  v-for="vendedor in vendedores" 
                  :key="vendedor.id" 
                  :value="vendedor.id"
                >
                  {{ vendedor.name }}
                </option>
              </select>
            </div>
            
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Notas adicionales</label>
              <textarea 
                v-model="actualizacionMasiva.notas" 
                class="form-textarea w-full"
                rows="3"
                placeholder="Agregar notas..."
              ></textarea>
            </div>
            
            <div class="flex gap-2">
              <button 
                type="submit" 
                :disabled="procesandoMasivo"
                class="btn-primary flex-1"
              >
                <i v-if="procesandoMasivo" class="fas fa-spinner fa-spin mr-1"></i>
                Actualizar
              </button>
              <button 
                type="button" 
                @click="mostrarModalMasivo = false"
                class="btn-secondary"
              >
                Cancelar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- TOAST NOTIFICATIONS -->
    <div 
      v-if="toast.mostrar"
      :class="[
        'fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transition-all duration-300',
        toast.tipo === 'success' ? 'bg-green-500 text-white' : '',
        toast.tipo === 'error' ? 'bg-red-500 text-white' : '',
        toast.tipo === 'warning' ? 'bg-yellow-500 text-white' : ''
      ]"
    >
      <div class="flex items-center">
        <i :class="[
          'mr-2',
          toast.tipo === 'success' ? 'fas fa-check-circle' : '',
          toast.tipo === 'error' ? 'fas fa-exclamation-circle' : '',
          toast.tipo === 'warning' ? 'fas fa-exclamation-triangle' : ''
        ]"></i>
        {{ toast.mensaje }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SeguimientoTable',
  props: {
    vendedores: {
      type: Array,
      default: () => []
    },
    puedeImportar: {
      type: Boolean,
      default: false
    }
  },
  
  data() {
    return {
      // Estados principales
      seguimientos: [],
      cargando: true,
      estadisticas: {},
      
      // Filtros y búsqueda
      filtros: {
        busqueda: '',
        vendedor: '',
        estado: '',
        prioridad: ''
      },
      filtroActivo: 'todos',
      busquedaTimeout: null,
      
      // Ordenamiento
      ordenActual: {
        campo: 'proxima_gestion',
        direccion: 'asc'
      },
      
      // Selección múltiple
      seguimientosSeleccionados: [],
      todosMarcados: false,
      
      // Paginación
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 50,
        total: 0
      },
      
      // Modales
      mostrarModalCrear: false,
      mostrarModalMasivo: false,
      mostrarModalImportar: false,
      
      // Actualización masiva
      actualizacionMasiva: {
        estado: '',
        prioridad: '',
        proxima_gestion: '',
        vendedor_id: '',
        notas: ''
      },
      procesandoMasivo: false,
      
      // Notificaciones
      toast: {
        mostrar: false,
        tipo: '',
        mensaje: ''
      }
    }
  },
  
  computed: {
    paginas() {
      const pages = [];
      const current = this.pagination.current_page;
      const last = this.pagination.last_page;
      
      // Mostrar máximo 5 páginas
      let start = Math.max(1, current - 2);
      let end = Math.min(last, current + 2);
      
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      
      return pages;
    }
  },
  
  mounted() {
    this.cargarSeguimientos();
    
    // Auto-refresh cada 5 minutos
    setInterval(() => {
      if (!this.cargando) {
        this.cargarSeguimientos(false);
      }
    }, 300000);
  },
  
  methods: {
    /**
     * CARGAR SEGUIMIENTOS - MÉTODO PRINCIPAL
     */
    async cargarSeguimientos(mostrarCarga = true) {
      try {
        if (mostrarCarga) {
          this.cargando = true;
        }
        
        const params = new URLSearchParams({
          page: this.pagination.current_page,
          per_page: this.pagination.per_page,
          sort: this.ordenActual.campo,
          direction: this.ordenActual.direccion,
          clasificacion: this.filtroActivo !== 'todos' ? this.filtroActivo : '',
          ...this.filtros
        });
        
        const response = await fetch(`/api/seguimiento/data?${params}`);
        const data = await response.json();
        
        if (data.success) {
          this.seguimientos = data.data.data;
          this.pagination = data.pagination;
          this.estadisticas = data.estadisticas;
        } else {
          this.mostrarToast('error', data.message || 'Error al cargar seguimientos');
        }
        
      } catch (error) {
        console.error('Error al cargar seguimientos:', error);
        this.mostrarToast('error', 'Error de conexión al cargar seguimientos');
      } finally {
        this.cargando = false;
      }
    },
    
    /**
     * APLICAR FILTRO RÁPIDO - FUNCIONALIDAD CRÍTICA
     */
    aplicarFiltroRapido(tipo) {
      this.filtroActivo = tipo;
      this.pagination.current_page = 1;
      this.seguimientosSeleccionados = [];
      this.cargarSeguimientos();
    },
    
    /**
     * BÚSQUEDA CON RETRASO
     */
    buscarConRetraso() {
      clearTimeout(this.busquedaTimeout);
      this.busquedaTimeout = setTimeout(() => {
        this.pagination.current_page = 1;
        this.cargarSeguimientos();
      }, 500);
    },
    
    /**
     * APLICAR FILTROS
     */
    aplicarFiltros() {
      this.pagination.current_page = 1;
      this.cargarSeguimientos();
    },
    
    /**
     * LIMPIAR FILTROS
     */
    limpiarFiltros() {
      this.filtros = {
        busqueda: '',
        vendedor: '',
        estado: '',
        prioridad: ''
      };
      this.filtroActivo = 'todos';
      this.pagination.current_page = 1;
      this.cargarSeguimientos();
    },
    
    /**
     * ORDENAMIENTO
     */
    ordenar(campo) {
      if (this.ordenActual.campo === campo) {
        this.ordenActual.direccion = this.ordenActual.direccion === 'asc' ? 'desc' : 'asc';
      } else {
        this.ordenActual.campo = campo;
        this.ordenActual.direccion = 'asc';
      }
      
      this.cargarSeguimientos();
    },
    
    getIconoOrden(campo) {
      if (this.ordenActual.campo !== campo) {
        return 'fas fa-sort text-gray-400';
      }
      
      return this.ordenActual.direccion === 'asc' 
        ? 'fas fa-sort-up text-blue-500' 
        : 'fas fa-sort-down text-blue-500';
    },
    
    /**
     * SELECCIÓN MÚLTIPLE
     */
    toggleTodos() {
      if (this.todosMarcados) {
        this.seguimientosSeleccionados = this.seguimientos.map(s => s.id);
      } else {
        this.seguimientosSeleccionados = [];
      }
    },
    
    /**
     * ACTUALIZAR CAMPO INDIVIDUAL - EDICIÓN EN LÍNEA
     */
    async actualizarCampo(seguimientoId, campo, valor) {
      try {
        const response = await fetch(`/seguimiento/${seguimientoId}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            [campo]: valor
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          // Actualizar el seguimiento en la lista
          const index = this.seguimientos.findIndex(s => s.id === seguimientoId);
          if (index !== -1) {
            this.seguimientos[index] = { ...this.seguimientos[index], ...data.data };
          }
          
          this.mostrarToast('success', 'Seguimiento actualizado correctamente');
        } else {
          this.mostrarToast('error', data.message || 'Error al actualizar seguimiento');
        }
        
      } catch (error) {
        console.error('Error al actualizar campo:', error);
        this.mostrarToast('error', 'Error de conexión al actualizar');
      }
    },
    
    /**
     * ACTUALIZACIÓN MASIVA
     */
    actualizarMasivo() {
      if (this.seguimientosSeleccionados.length === 0) {
        this.mostrarToast('warning', 'Selecciona al menos un seguimiento');
        return;
      }
      
      this.mostrarModalMasivo = true;
      
      // Resetear formulario
      this.actualizacionMasiva = {
        estado: '',
        prioridad: '',
        proxima_gestion: '',
        vendedor_id: '',
        notas: ''
      };
    },
    
    async confirmarActualizacionMasiva() {
      try {
        this.procesandoMasivo = true;
        
        // Filtrar solo campos con valores
        const datos = {};
        Object.keys(this.actualizacionMasiva).forEach(key => {
          if (this.actualizacionMasiva[key]) {
            datos[key] = this.actualizacionMasiva[key];
          }
        });
        
        if (Object.keys(datos).length === 0) {
          this.mostrarToast('warning', 'Selecciona al menos un campo para actualizar');
          return;
        }
        
        const response = await fetch('/seguimiento/update-masivo', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            seguimiento_ids: this.seguimientosSeleccionados,
            datos: datos
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.mostrarToast('success', data.message);
          this.mostrarModalMasivo = false;
          this.seguimientosSeleccionados = [];
          this.todosMarcados = false;
          this.cargarSeguimientos();
        } else {
          this.mostrarToast('error', data.message || 'Error en actualización masiva');
        }
        
      } catch (error) {
        console.error('Error en actualización masiva:', error);
        this.mostrarToast('error', 'Error de conexión en actualización masiva');
      } finally {
        this.procesandoMasivo = false;
      }
    },
    
    /**
     * PAGINACIÓN
     */
    cambiarPagina(pagina) {
      if (pagina >= 1 && pagina <= this.pagination.last_page) {
        this.pagination.current_page = pagina;
        this.cargarSeguimientos();
      }
    },
    
    /**
     * ACCIONES DE SEGUIMIENTO
     */
    verDetalle(seguimiento) {
      // TODO: Implementar modal de detalle
      console.log('Ver detalle de seguimiento:', seguimiento);
    },
    
    editarSeguimiento(seguimiento) {
      // TODO: Implementar modal de edición
      console.log('Editar seguimiento:', seguimiento);
    },
    
    async eliminarSeguimiento(seguimientoId) {
      if (!confirm('¿Estás seguro de que deseas eliminar este seguimiento?')) {
        return;
      }
      
      try {
        const response = await fetch(`/seguimiento/${seguimientoId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.mostrarToast('success', 'Seguimiento eliminado correctamente');
          this.cargarSeguimientos();
        } else {
          this.mostrarToast('error', data.message || 'Error al eliminar seguimiento');
        }
        
      } catch (error) {
        console.error('Error al eliminar seguimiento:', error);
        this.mostrarToast('error', 'Error de conexión al eliminar');
      }
    },
    
    verCotizacion(codigo) {
      if (codigo && codigo !== 'Sin cotización') {
        // TODO: Implementar navegación a cotización
        console.log('Ver cotización:', codigo);
      }
    },
    
    /**
     * ESTILOS Y CLASES
     */
    getClaseFilaSeguimiento(seguimiento) {
      const clases = [];
      
      // Color según clasificación
      if (seguimiento.color_clasificacion === 'danger') {
        clases.push('bg-red-50 border-l-4 border-red-400');
      } else if (seguimiento.color_clasificacion === 'warning') {
        clases.push('bg-yellow-50 border-l-4 border-yellow-400');
      } else if (seguimiento.color_clasificacion === 'success') {
        clases.push('bg-green-50 border-l-4 border-green-400');
      }
      
      return clases.join(' ');
    },
    
    getClaseEstado(estado) {
      const clases = {
        'pendiente': 'bg-blue-100 text-blue-800',
        'en_proceso': 'bg-yellow-100 text-yellow-800',
        'completado': 'bg-green-100 text-green-800',
        'vencido': 'bg-red-100 text-red-800',
        'reprogramado': 'bg-purple-100 text-purple-800'
      };
      
      return clases[estado] || 'bg-gray-100 text-gray-800';
    },
    
    getClasePrioridad(prioridad) {
      const clases = {
        'baja': 'bg-gray-100 text-gray-800',
        'media': 'bg-blue-100 text-blue-800',
        'alta': 'bg-orange-100 text-orange-800',
        'urgente': 'bg-red-100 text-red-800'
      };
      
      return clases[prioridad] || 'bg-gray-100 text-gray-800';
    },
    
    /**
     * NOTIFICACIONES
     */
    mostrarToast(tipo, mensaje) {
      this.toast = {
        mostrar: true,
        tipo: tipo,
        mensaje: mensaje
      };
      
      setTimeout(() => {
        this.toast.mostrar = false;
      }, 5000);
    }
  },
  
  watch: {
    seguimientosSeleccionados() {
      this.todosMarcados = this.seguimientos.length > 0 && 
                          this.seguimientosSeleccionados.length === this.seguimientos.length;
    }
  }
}
</script>

<style scoped>
/* Clases CSS personalizadas para Bioscom */
.btn-filter {
  @apply px-3 py-1.5 text-xs font-medium border border-gray-300 rounded-lg transition-colors duration-200;
}

.btn-filter-active {
  @apply font-semibold;
}

.btn-primary {
  @apply bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200;
}

.btn-secondary {
  @apply bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200;
}

.btn-outline {
  @apply border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors duration-200;
}

.form-select {
  @apply border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent;
}

.form-input {
  @apply border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent;
}

.form-textarea {
  @apply border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none;
}

.seguimiento-container {
  @apply relative;
}

/* Mejoras para el diseño responsivo */
@media (max-width: 768px) {
  .grid-cols-12 {
    @apply grid-cols-1;
  }
  
  .col-span-1, .col-span-2 {
    @apply col-span-1;
  }
}
</style>