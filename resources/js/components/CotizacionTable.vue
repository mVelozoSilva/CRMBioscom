<template>
  <div class="cotizacion-table-container">
    <!-- HEADER CON CONTROLES -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-900">
            <i class="fas fa-file-invoice mr-2 text-bioscom-primary"></i>
            Gestión de Cotizaciones
          </h2>
          <p class="text-sm text-gray-600 mt-1">
            {{ estadisticas.total }} cotizaciones totales
            <span v-if="cotizacionesSeleccionadas.length > 0" class="ml-2 text-bioscom-primary font-medium">
              • {{ cotizacionesSeleccionadas.length }} seleccionadas
            </span>
          </p>
        </div>
        
        <!-- ACCIONES PRINCIPALES -->
        <div class="flex items-center gap-3">
          <!-- Edición masiva -->
          <button
            v-if="cotizacionesSeleccionadas.length > 0"
            @click="abrirEdicionMasiva"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm bg-bioscom-primary text-sm font-medium text-white hover:bg-bioscom-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bioscom-primary transition-colors"
          >
            <i class="fas fa-edit mr-2"></i>
            Editar {{ cotizacionesSeleccionadas.length }} cotizaciones
          </button>
          
          <!-- Nueva cotización -->
          <a href="/crm-bioscom/public/cotizaciones/create" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm bg-green-600 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Nueva Cotización
          </a>
        </div>
      </div>
    </div>

    <!-- FILTROS Y BÚSQUEDA -->
    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
      <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <!-- Búsqueda general -->
        <div class="md:col-span-2">
          <input
            v-model="filtros.busqueda"
            @input="buscarConRetraso"
            type="text"
            placeholder="Buscar por código, nombre, cliente..."
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-bioscom-primary focus:border-bioscom-primary"
          >
        </div>
        
        <!-- Filtro por estado -->
        <div>
          <select v-model="filtros.estado" @change="aplicarFiltros" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-bioscom-primary focus:border-bioscom-primary">
            <option value="">Todos los estados</option>
            <option value="Pendiente">Pendiente</option>
            <option value="Enviada">Enviada</option>
            <option value="En Revisión">En Revisión</option>
            <option value="Ganada">Ganada</option>
            <option value="Perdida">Perdida</option>
            <option value="Vencida">Vencida</option>
          </select>
        </div>
        
        <!-- Filtro por vendedor -->
        <div>
          <select v-model="filtros.vendedor" @change="aplicarFiltros" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-bioscom-primary focus:border-bioscom-primary">
            <option value="">Todos los vendedores</option>
            <option v-for="vendedor in vendedoresDisponibles" :key="vendedor.id" :value="vendedor.id">
              {{ vendedor.name }}
            </option>
          </select>
        </div>
        
        <!-- Filtro por fecha -->
        <div>
          <input
            v-model="filtros.fecha_desde"
            @change="aplicarFiltros"
            type="date"
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-bioscom-primary focus:border-bioscom-primary"
            placeholder="Desde"
          >
        </div>
        
        <!-- Botones de filtro rápido -->
        <div class="flex gap-2">
          <button
            @click="filtroRapido('vencidas')"
            class="px-3 py-2 text-xs font-medium rounded-lg border transition-colors"
            :class="filtros.rapido === 'vencidas' ? 'bg-red-100 text-red-800 border-red-300' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
          >
            Vencidas
          </button>
          <button
            @click="filtroRapido('mes')"
            class="px-3 py-2 text-xs font-medium rounded-lg border transition-colors"
            :class="filtros.rapido === 'mes' ? 'bg-blue-100 text-blue-800 border-blue-300' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
          >
            Este mes
          </button>
        </div>
      </div>
      
      <!-- Limpiar filtros -->
      <div class="mt-3 flex justify-between items-center">
        <button
          v-if="tieneAltrosFiltrosActivos"
          @click="limpiarFiltros"
          class="text-sm text-gray-600 hover:text-gray-800"
        >
          <i class="fas fa-times mr-1"></i>
          Limpiar filtros
        </button>
        <div v-else></div>
        
        <!-- Configurar columnas -->
        <button
          @click="mostrarConfigColumnas = !mostrarConfigColumnas"
          class="text-sm text-gray-600 hover:text-gray-800"
        >
          <i class="fas fa-cog mr-1"></i>
          Configurar columnas
        </button>
      </div>
      
      <!-- Panel de configuración de columnas -->
      <div v-if="mostrarConfigColumnas" class="mt-4 p-4 bg-white rounded-lg border border-gray-200">
        <h4 class="text-sm font-medium text-gray-900 mb-3">Columnas visibles:</h4>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
          <label v-for="columna in columnasDisponibles" :key="columna.key" class="flex items-center">
            <input
              type="checkbox"
              v-model="columnasVisibles"
              :value="columna.key"
              class="rounded border-gray-300 text-bioscom-primary focus:border-bioscom-primary focus:ring-bioscom-primary"
            >
            <span class="ml-2 text-sm text-gray-700">{{ columna.label }}</span>
          </label>
        </div>
      </div>
    </div>

    <!-- ESTADÍSTICAS RÁPIDAS -->
    <div class="bg-white border-b border-gray-200 px-6 py-3">
      <div class="flex items-center justify-between text-sm">
        <div class="flex gap-6">
          <span class="text-gray-600">
            <span class="font-medium text-green-600">{{ estadisticas.ganadas }}</span> Ganadas
          </span>
          <span class="text-gray-600">
            <span class="font-medium text-yellow-600">{{ estadisticas.pendientes }}</span> Pendientes
          </span>
          <span class="text-gray-600">
            <span class="font-medium text-red-600">{{ estadisticas.perdidas }}</span> Perdidas
          </span>
          <span class="text-gray-600">
            <span class="font-medium text-purple-600">{{ estadisticas.vencidas }}</span> Vencidas
          </span>
        </div>
        
        <div class="text-gray-600">
          Mostrando {{ cotizaciones.length }} de {{ estadisticas.total }}
        </div>
      </div>
    </div>

    <!-- INDICADOR DE CARGA -->
    <div v-if="cargando" class="bg-white p-8 text-center">
      <i class="fas fa-spinner fa-spin text-2xl text-bioscom-primary mb-2"></i>
      <p class="text-gray-600">Cargando cotizaciones...</p>
    </div>

    <!-- TABLA OPTIMIZADA -->
    <div v-else class="bg-white overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <!-- Checkbox seleccionar todo -->
              <th class="w-12 px-6 py-3">
                <input
                  type="checkbox"
                  :checked="todasSeleccionadas"
                  @change="toggleTodasSeleccionadas"
                  class="rounded border-gray-300 text-bioscom-primary focus:border-bioscom-primary focus:ring-bioscom-primary"
                >
              </th>
              
              <!-- Columnas con filtros estilo Excel -->
              <th
                v-for="columna in columnasMostradas"
                :key="columna.key"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider relative"
              >
                <div class="flex items-center justify-between">
                  <!-- Nombre de la columna (clickeable para ordenar) -->
                  <span 
                    @click="ordenarPor(columna.key)"
                    class="cursor-pointer hover:text-gray-700 flex items-center"
                    :title="`Ordenar por ${columna.label}`"
                  >
                    {{ columna.label }}
                    <span v-if="ordenamiento.campo === columna.key" class="ml-1">
                      <i :class="ordenamiento.direccion === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down'" class="text-bioscom-primary"></i>
                    </span>
                    <i v-else class="fas fa-sort ml-1 text-gray-400"></i>
                  </span>
                  
                  <!-- Botón de filtro estilo Excel -->
                  <button
                    @click="toggleFiltroColumna(columna.key)"
                    class="ml-2 p-1 rounded hover:bg-gray-200 transition-colors"
                    :class="{
                      'text-bioscom-primary bg-blue-50': filtrosColumna[columna.key]?.activo,
                      'text-gray-400 hover:text-gray-600': !filtrosColumna[columna.key]?.activo
                    }"
                    :title="`Filtrar ${columna.label}`"
                  >
                    <i class="fas fa-filter text-xs"></i>
                  </button>
                </div>
                
                <!-- Dropdown de filtro estilo Excel -->
                <div
                  v-if="filtrosColumna[columna.key]?.abierto"
                  class="absolute top-full left-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-50 min-w-[200px] max-w-[300px]"
                  @click.stop
                >
                  <div class="p-3">
                    <!-- Header del filtro -->
                    <div class="flex items-center justify-between mb-3 pb-2 border-b">
                      <span class="font-medium text-gray-900">Filtrar {{ columna.label }}</span>
                      <button
                        @click="cerrarFiltroColumna(columna.key)"
                        class="text-gray-400 hover:text-gray-600"
                      >
                        <i class="fas fa-times"></i>
                      </button>
                    </div>
                    
                    <!-- Búsqueda dentro del filtro -->
                    <div class="mb-3">
                      <input
                        v-model="filtrosColumna[columna.key].busqueda"
                        @input="filtrarOpcionesColumna(columna.key)"
                        type="text"
                        placeholder="Buscar..."
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-bioscom-primary focus:border-bioscom-primary"
                      >
                    </div>
                    
                    <!-- Acciones rápidas -->
                    <div class="flex gap-2 mb-3 text-xs">
                      <button
                        @click="seleccionarTodosEnFiltro(columna.key)"
                        class="px-2 py-1 bg-gray-100 text-gray-600 rounded hover:bg-gray-200"
                      >
                        Todos
                      </button>
                      <button
                        @click="deseleccionarTodosEnFiltro(columna.key)"
                        class="px-2 py-1 bg-gray-100 text-gray-600 rounded hover:bg-gray-200"
                      >
                        Ninguno
                      </button>
                    </div>
                    
                    <!-- Lista de opciones -->
                    <div class="max-h-48 overflow-y-auto">
                      <label
                        v-for="opcion in filtrosColumna[columna.key].opcionesFiltradas"
                        :key="opcion.valor"
                        class="flex items-center py-1 px-2 hover:bg-gray-50 cursor-pointer text-sm"
                      >
                        <input
                          type="checkbox"
                          :checked="filtrosColumna[columna.key].seleccionados.includes(opcion.valor)"
                          @change="toggleOpcionFiltro(columna.key, opcion.valor)"
                          class="rounded border-gray-300 text-bioscom-primary focus:border-bioscom-primary focus:ring-bioscom-primary mr-2"
                        >
                        <span class="flex-1 truncate" :title="opcion.texto">
                          {{ opcion.texto }}
                        </span>
                        <span class="text-xs text-gray-400 ml-1">({{ opcion.count }})</span>
                      </label>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="flex gap-2 mt-3 pt-2 border-t">
                      <button
                        @click="aplicarFiltroColumna(columna.key)"
                        class="flex-1 px-3 py-1 bg-bioscom-primary text-white text-sm rounded hover:bg-bioscom-secondary"
                      >
                        Aplicar
                      </button>
                      <button
                        @click="limpiarFiltroColumna(columna.key)"
                        class="px-3 py-1 bg-gray-100 text-gray-600 text-sm rounded hover:bg-gray-200"
                      >
                        Limpiar
                      </button>
                    </div>
                  </div>
                </div>
              </th>
              
              <!-- Acciones -->
              <th class="w-20 px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
          </thead>
          
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="cotizacion in cotizaciones"
              :key="cotizacion.id"
              class="hover:bg-gray-50 transition-colors"
              :class="{ 'bg-blue-50': cotizacionesSeleccionadas.includes(cotizacion.id) }"
            >
              <!-- Checkbox individual -->
              <td class="px-6 py-4">
                <input
                  type="checkbox"
                  :value="cotizacion.id"
                  v-model="cotizacionesSeleccionadas"
                  class="rounded border-gray-300 text-bioscom-primary focus:border-bioscom-primary focus:ring-bioscom-primary"
                >
              </td>
              
              <!-- Código -->
              <td v-if="columnasVisibles.includes('codigo')" class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ cotizacion.codigo || 'Sin código' }}</div>
              </td>
              
              <!-- Nombre -->
              <td v-if="columnasVisibles.includes('nombre')" class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ cotizacion.nombre_cotizacion }}</div>
              </td>
              
              <!-- Cliente -->
              <td v-if="columnasVisibles.includes('cliente')" class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ cotizacion.cliente }}</div>
              </td>
              
              <!-- Estado -->
              <td v-if="columnasVisibles.includes('estado')" class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="getEstadoColor(cotizacion.estado)"
                >
                  {{ cotizacion.estado }}
                </span>
              </td>
              
              <!-- Total -->
              <td v-if="columnasVisibles.includes('total')" class="px-6 py-4 whitespace-nowrap text-right">
                <div class="text-sm font-medium text-gray-900">{{ cotizacion.total_formateado }}</div>
              </td>
              
              <!-- Vendedor -->
              <td v-if="columnasVisibles.includes('vendedor')" class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ cotizacion.vendedor }}</div>
              </td>
              
              <!-- Validez -->
              <td v-if="columnasVisibles.includes('validez')" class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ cotizacion.validez_oferta }}</div>
                <div v-if="cotizacion.vencida" class="text-xs text-red-600">
                  <i class="fas fa-exclamation-triangle mr-1"></i>
                  Vencida
                </div>
              </td>
              
              <!-- Fecha creación -->
              <td v-if="columnasVisibles.includes('created_at')" class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ cotizacion.created_at }}</div>
              </td>
              
              <!-- Acciones -->
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end gap-2">
                  <a
                    :href="`/crm-bioscom/public/cotizaciones/${cotizacion.id}`"
                    class="text-bioscom-primary hover:text-bioscom-secondary"
                    title="Ver detalles"
                  >
                    <i class="fas fa-eye"></i>
                  </a>
                  <a
                    :href="`/crm-bioscom/public/cotizaciones/${cotizacion.id}/edit`"
                    class="text-gray-600 hover:text-gray-900"
                    title="Editar"
                  >
                    <i class="fas fa-edit"></i>
                  </a>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- PAGINACIÓN -->
    <div class="bg-white px-6 py-4 border-t border-gray-200">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Mostrando {{ paginacion.desde }} - {{ paginacion.hasta }} de {{ paginacion.total }} cotizaciones
        </div>
        
        <div class="flex items-center gap-2">
          <!-- Tamaño de página -->
          <select v-model="paginacion.porPagina" @change="cambiarTamanoPagina" class="block px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-bioscom-primary focus:border-bioscom-primary text-sm">
            <option value="25">25 por página</option>
            <option value="50">50 por página</option>
            <option value="100">100 por página</option>
            <option value="200">200 por página</option>
          </select>
          
          <!-- Navegación -->
          <div class="flex gap-1">
            <button
              @click="irAPagina(paginacion.paginaActual - 1)"
              :disabled="paginacion.paginaActual <= 1"
              class="px-3 py-2 text-sm border rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
            >
              <i class="fas fa-chevron-left"></i>
            </button>
            
            <span class="px-3 py-2 text-sm text-gray-700">
              Página {{ paginacion.paginaActual }} de {{ paginacion.totalPaginas }}
            </span>
            
            <button
              @click="irAPagina(paginacion.paginaActual + 1)"
              :disabled="paginacion.paginaActual >= paginacion.totalPaginas"
              class="px-3 py-2 text-sm border rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
            >
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL DE EDICIÓN MASIVA -->
    <div v-if="mostrarModalEdicionMasiva" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">
            Editar {{ cotizacionesSeleccionadas.length }} cotizaciones
          </h3>
        </div>
        
        <div class="px-6 py-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Cambiar estado a:
          </label>
          <select v-model="edicionMasiva.nuevoEstado" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-bioscom-primary focus:border-bioscom-primary">
            <option value="Pendiente">Pendiente</option>
            <option value="Enviada">Enviada</option>
            <option value="En Revisión">En Revisión</option>
            <option value="Ganada">Ganada</option>
            <option value="Perdida">Perdida</option>
            <option value="Vencida">Vencida</option>
          </select>
          
          <label class="block text-sm font-medium text-gray-700 mb-2 mt-4">
            Motivo del cambio (opcional):
          </label>
          <textarea
            v-model="edicionMasiva.motivo"
            rows="3"
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-bioscom-primary focus:border-bioscom-primary"
            placeholder="Ej: Actualización masiva de cotizaciones perdidas..."
          ></textarea>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
          <button
            @click="cerrarEdicionMasiva"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bioscom-primary transition-colors"
          >
            Cancelar
          </button>
          <button
            @click="ejecutarEdicionMasiva"
            :disabled="!edicionMasiva.nuevoEstado || edicionMasiva.procesando"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm bg-bioscom-primary text-sm font-medium text-white hover:bg-bioscom-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bioscom-primary transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <i v-if="edicionMasiva.procesando" class="fas fa-spinner fa-spin mr-2"></i>
            <i v-else class="fas fa-check mr-2"></i>
            {{ edicionMasiva.procesando ? 'Procesando...' : 'Actualizar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CotizacionTable',
  
  data() {
    return {
      // Datos de cotizaciones
      cotizaciones: [],
      cotizacionesSeleccionadas: [],
      vendedoresDisponibles: [],
      
      // Filtros
      filtros: {
        busqueda: '',
        estado: '',
        vendedor: '',
        fecha_desde: '',
        fecha_hasta: '',
        rapido: ''
      },
      
      // Ordenamiento
      ordenamiento: {
        campo: 'created_at',
        direccion: 'desc'
      },
      
      // Paginación
      paginacion: {
        paginaActual: 1,
        porPagina: 50,
        total: 0,
        totalPaginas: 0,
        desde: 0,
        hasta: 0
      },
      
      // Columnas
      mostrarConfigColumnas: false,
      columnasDisponibles: [
        { key: 'codigo', label: 'Código' },
        { key: 'nombre', label: 'Nombre' },
        { key: 'cliente', label: 'Cliente' },
        { key: 'estado', label: 'Estado' },
        { key: 'total', label: 'Total' },
        { key: 'vendedor', label: 'Vendedor' },
        { key: 'validez', label: 'Validez' },
        { key: 'created_at', label: 'Fecha' }
      ],
      columnasVisibles: ['codigo', 'nombre', 'cliente', 'estado', 'total', 'validez'],
      
      // Estadísticas
      estadisticas: {
        total: 0,
        ganadas: 0,
        pendientes: 0,
        perdidas: 0,
        vencidas: 0
      },
      
      // Edición masiva
      mostrarModalEdicionMasiva: false,
      edicionMasiva: {
        nuevoEstado: '',
        motivo: '',
        procesando: false
      },
      
      // Control de búsqueda
      busquedaTimeout: null,
      cargando: false,
      filtrosColumna: {
      codigo: { abierto: false, activo: false, seleccionados: [], opciones: [], opcionesFiltradas: [], busqueda: '' },
      nombre: { abierto: false, activo: false, seleccionados: [], opciones: [], opcionesFiltradas: [], busqueda: '' },
      cliente: { abierto: false, activo: false, seleccionados: [], opciones: [], opcionesFiltradas: [], busqueda: '' },
      estado: { abierto: false, activo: false, seleccionados: [], opciones: [], opcionesFiltradas: [], busqueda: '' },
      vendedor: { abierto: false, activo: false, seleccionados: [], opciones: [], opcionesFiltradas: [], busqueda: '' },
      validez: { abierto: false, activo: false, seleccionados: [], opciones: [], opcionesFiltradas: [], busqueda: '' }
      }
    }
  },
  
  computed: {
    todasSeleccionadas() {
      return this.cotizaciones.length > 0 && this.cotizacionesSeleccionadas.length === this.cotizaciones.length;
    },
    
    tieneAltrosFiltrosActivos() {
      return this.filtros.busqueda || this.filtros.estado || this.filtros.vendedor || this.filtros.fecha_desde || this.filtros.rapido;
    },
    
    columnasMostradas() {
      return this.columnasDisponibles.filter(columna => this.columnasVisibles.includes(columna.key));
    }
  },
  
  mounted() {
    console.log('🚨 CotizacionTable montado');
    console.log('🍞 Toast function disponible:', typeof window.mostrarToast);
    this.cargarDatos();
    this.cargarVendedores();
  },
  
  methods: {
    /**
     * CARGAR DATOS PRINCIPALES - CON FILTROS DE COLUMNAS
     */
    async cargarDatos() {
      this.cargando = true;
      
      try {
        const params = {
          page: this.paginacion.paginaActual,
          per_page: this.paginacion.porPagina,
          sort: this.ordenamiento.campo,
          direction: this.ordenamiento.direccion,
          ...this.filtros,
          // 🔥 NUEVO: Agregar filtros de columnas
          ...this.obtenerFiltrosColumnasActivos()
        };
        
        console.log('📤 Cargando cotizaciones con parámetros:', params);
        
        const response = await axios.get('/crm-bioscom/public/api/cotizaciones', {
          params,
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest' 
          }
        });
        
        if (response.data.success) {
          this.cotizaciones = response.data.data.data;
          this.paginacion = {
            ...this.paginacion,
            total: response.data.data.total,
            totalPaginas: response.data.data.last_page,
            desde: response.data.data.from || 0,
            hasta: response.data.data.to || 0
          };
          
          this.estadisticas = response.data.estadisticas || this.estadisticas;
          
          console.log('✅ Cotizaciones cargadas:', this.cotizaciones.length);
          
          // 🔥 NUEVO: Actualizar opciones de filtros después de cargar datos
          this.actualizarOpcionesFiltrosDespuesDeCarga();
        }
        
      } catch (error) {
        console.error('❌ Error al cargar cotizaciones:', error);
        if (window.mostrarToast) {
          window.mostrarToast('error', 'Error al cargar cotizaciones');
        } else {
          console.error('Toast function not available');
        }
      } finally {
        this.cargando = false;
      }
    },
    
    /**
     * CARGAR VENDEDORES
     */
    async cargarVendedores() {
      try {
        const response = await axios.get('/crm-bioscom/public/api/seguimiento/vendedores');
        this.vendedoresDisponibles = response.data || [];
        console.log('✅ Vendedores cargados:', this.vendedoresDisponibles.length);
      } catch (error) {
        console.error('❌ Error al cargar vendedores:', error);
      }
    },
    
    /**
     * FILTROS Y BÚSQUEDA
     */
    buscarConRetraso() {
      clearTimeout(this.busquedaTimeout);
      this.busquedaTimeout = setTimeout(() => {
        this.paginacion.paginaActual = 1;
        this.cargarDatos();
      }, 500);
    },
    
    aplicarFiltros() {
      this.paginacion.paginaActual = 1;
      this.cargarDatos();
    },
    
    filtroRapido(tipo) {
      if (this.filtros.rapido === tipo) {
        this.filtros.rapido = '';
      } else {
        this.filtros.rapido = tipo;
        
        // Configurar filtros específicos
        if (tipo === 'vencidas') {
          this.filtros.estado = '';
        } else if (tipo === 'mes') {
          const inicioMes = new Date();
          inicioMes.setDate(1);
          this.filtros.fecha_desde = inicioMes.toISOString().split('T')[0];
        }
      }
      this.aplicarFiltros();
    },
    
    limpiarFiltros() {
      this.filtros = {
        busqueda: '',
        estado: '',
        vendedor: '',
        fecha_desde: '',
        fecha_hasta: '',
        rapido: ''
      };
      this.aplicarFiltros();
    },
    
    /**
     * ORDENAMIENTO
     */
    ordenarPor(campo) {
      if (this.ordenamiento.campo === campo) {
        this.ordenamiento.direccion = this.ordenamiento.direccion === 'asc' ? 'desc' : 'asc';
      } else {
        this.ordenamiento.campo = campo;
        this.ordenamiento.direccion = 'asc';
      }
      this.cargarDatos();
    },
    
    /**
     * PAGINACIÓN
     */
    irAPagina(pagina) {
      if (pagina >= 1 && pagina <= this.paginacion.totalPaginas) {
        this.paginacion.paginaActual = pagina;
        this.cargarDatos();
      }
    },
    
    cambiarTamanoPagina() {
      this.paginacion.paginaActual = 1;
      this.cargarDatos();
    },
    
    /**
     * SELECCIÓN
     */
    toggleTodasSeleccionadas() {
      if (this.todasSeleccionadas) {
        this.cotizacionesSeleccionadas = [];
      } else {
        this.cotizacionesSeleccionadas = this.cotizaciones.map(c => c.id);
      }
    },
    
    /**
     * EDICIÓN MASIVA
     */
    abrirEdicionMasiva() {
      this.mostrarModalEdicionMasiva = true;
      this.edicionMasiva = {
        nuevoEstado: 'Perdida', // Estado por defecto más común
        motivo: '',
        procesando: false
      };
    },
    
    cerrarEdicionMasiva() {
      this.mostrarModalEdicionMasiva = false;
      this.edicionMasiva = {
        nuevoEstado: '',
        motivo: '',
        procesando: false
      };
    },
    
    async ejecutarEdicionMasiva() {
      if (!this.edicionMasiva.nuevoEstado) return;
      
      this.edicionMasiva.procesando = true;
      
      try {
        const response = await axios.post('/crm-bioscom/public/api/cotizaciones/update-masivo', {
          cotizacion_ids: this.cotizacionesSeleccionadas,
          nuevo_estado: this.edicionMasiva.nuevoEstado,
          motivo: this.edicionMasiva.motivo
        }, {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        
        if (response.data.success) {
          // 🔧 CORREGIDO: Llamada a mostrarToast para éxito
          if (window.mostrarToast) {
            window.mostrarToast('success', response.data.message);
          }
          this.cotizacionesSeleccionadas = [];
          this.cerrarEdicionMasiva();
          this.cargarDatos(); // Recargar datos
        } else {
          // 🔧 CORREGIDO: Llamada a mostrarToast para error
          if (window.mostrarToast) {
            window.mostrarToast('error', response.data.message || 'Error en la actualización masiva');
          }
        }
        
      } catch (error) {
        console.error('❌ Error en edición masiva:', error);
        // 🔧 CORREGIDO: Llamada a mostrarToast para excepción
        if (window.mostrarToast) {
          window.mostrarToast('error', 'Error al actualizar las cotizaciones');
        }
      } finally {
        this.edicionMasiva.procesando = false;
      }
    },
    
    /**
     * UTILIDADES
     */
    getEstadoColor(estado) {
      const colores = {
        'Pendiente': 'bg-yellow-100 text-yellow-800',
        'Enviada': 'bg-blue-100 text-blue-800',
        'En Revisión': 'bg-purple-100 text-purple-800',
        'Ganada': 'bg-green-100 text-green-800',
        'Perdida': 'bg-red-100 text-red-800',
        'Vencida': 'bg-gray-100 text-gray-800'
      };
      return colores[estado] || 'bg-gray-100 text-gray-800';
    },

    /**
     * 🔥 FILTROS ESTILO EXCEL - NUEVOS MÉTODOS
     */
    
    // Abrir/cerrar filtro de columna
    toggleFiltroColumna(columna) {
      // Cerrar otros filtros abiertos
      Object.keys(this.filtrosColumna).forEach(key => {
        if (key !== columna) {
          this.filtrosColumna[key].abierto = false;
        }
      });
      
      // Toggle el filtro actual
      this.filtrosColumna[columna].abierto = !this.filtrosColumna[columna].abierto;
      
      // Si se abre, generar opciones
      if (this.filtrosColumna[columna].abierto) {
        this.generarOpcionesFiltro(columna);
      }
    },
    
    // Cerrar filtro específico
    cerrarFiltroColumna(columna) {
      this.filtrosColumna[columna].abierto = false;
    },
    
    // Generar opciones únicas para el filtro
    generarOpcionesFiltro(columna) {
      const valores = new Map();
      
      this.cotizaciones.forEach(cotizacion => {
        let valor = '';
        let texto = '';
        
        switch (columna) {
          case 'codigo':
            valor = cotizacion.codigo || 'Sin código';
            texto = valor;
            break;
          case 'nombre':
            valor = cotizacion.nombre_cotizacion;
            texto = valor;
            break;
          case 'cliente':
            valor = cotizacion.cliente;
            texto = valor;
            break;
          case 'estado':
            valor = cotizacion.estado;
            texto = valor;
            break;
          case 'vendedor':
            valor = cotizacion.vendedor;
            texto = valor;
            break;
          case 'validez':
            valor = cotizacion.validez_oferta || 'Sin fecha';
            texto = valor;
            break;
        }
        
        if (valores.has(valor)) {
          valores.set(valor, valores.get(valor) + 1);
        } else {
          valores.set(valor, 1);
        }
      });
      
      // Convertir a array y ordenar
      const opciones = Array.from(valores.entries())
        .map(([valor, count]) => ({
          valor,
          texto: valor,
          count
        }))
        .sort((a, b) => a.texto.localeCompare(b.texto));
      
      this.filtrosColumna[columna].opciones = opciones;
      this.filtrosColumna[columna].opcionesFiltradas = [...opciones];
      
      // Si no hay filtro activo, seleccionar todos
      if (!this.filtrosColumna[columna].activo) {
        this.filtrosColumna[columna].seleccionados = opciones.map(o => o.valor);
      }
    },
    
    // Filtrar opciones dentro del dropdown
    filtrarOpcionesColumna(columna) {
      const busqueda = this.filtrosColumna[columna].busqueda.toLowerCase();
      this.filtrosColumna[columna].opcionesFiltradas = this.filtrosColumna[columna].opciones.filter(
        opcion => opcion.texto.toLowerCase().includes(busqueda)
      );
    },
    
    // Toggle opción individual
    toggleOpcionFiltro(columna, valor) {
      const seleccionados = this.filtrosColumna[columna].seleccionados;
      const index = seleccionados.indexOf(valor);
      
      if (index > -1) {
        seleccionados.splice(index, 1);
      } else {
        seleccionados.push(valor);
      }
    },
    
    // Seleccionar todos en filtro
    seleccionarTodosEnFiltro(columna) {
      this.filtrosColumna[columna].seleccionados = this.filtrosColumna[columna].opcionesFiltradas.map(o => o.valor);
    },
    
    // Deseleccionar todos en filtro
    deseleccionarTodosEnFiltro(columna) {
      this.filtrosColumna[columna].seleccionados = [];
    },
    
    // Aplicar filtro de columna
    aplicarFiltroColumna(columna) {
      const seleccionados = this.filtrosColumna[columna].seleccionados;
      const totalOpciones = this.filtrosColumna[columna].opciones.length;
      
      // Marcar como activo si no están todos seleccionados
      this.filtrosColumna[columna].activo = seleccionados.length < totalOpciones;
      
      // Cerrar dropdown
      this.filtrosColumna[columna].abierto = false;
      
      // Recargar datos con filtros
      this.paginacion.paginaActual = 1;
      this.cargarDatos();
      
      console.log(`🔍 Filtro aplicado en ${columna}:`, seleccionados);
    },
    
    // Limpiar filtro de columna
    limpiarFiltroColumna(columna) {
      this.filtrosColumna[columna].seleccionados = this.filtrosColumna[columna].opciones.map(o => o.valor);
      this.filtrosColumna[columna].activo = false;
      this.filtrosColumna[columna].abierto = false;
      this.filtrosColumna[columna].busqueda = '';
      
      // Recargar datos
      this.paginacion.paginaActual = 1;
      this.cargarDatos();
    },
    
    // Obtener filtros activos para enviar al backend
    obtenerFiltrosColumnasActivos() {
      const filtrosActivos = {};
      
      Object.keys(this.filtrosColumna).forEach(columna => {
        if (this.filtrosColumna[columna].activo && this.filtrosColumna[columna].seleccionados.length > 0) {
          filtrosActivos[`filtro_${columna}`] = this.filtrosColumna[columna].seleccionados;
        }
      });
      
      return filtrosActivos;
    },
    
    // Actualizar opciones de filtros después de cargar datos
    actualizarOpcionesFiltrosDespuesDeCarga() {
      Object.keys(this.filtrosColumna).forEach(columna => {
        if (this.filtrosColumna[columna].abierto) {
          this.generarOpcionesFiltro(columna);
        }
      });
    },
  }
}
</script>