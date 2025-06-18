<template>
  <div class="cliente-table-container">
    <!-- HEADER CON CONTROLES -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-900">
            <i class="fas fa-users mr-2 text-bioscom-primary"></i>
            Gestión de Clientes
          </h2>
          <p class="text-sm text-gray-600 mt-1">
            {{ estadisticas.total }} clientes registrados
            <span v-if="clientesSeleccionados.length > 0" class="ml-2 text-bioscom-primary font-medium">
              • {{ clientesSeleccionados.length }} seleccionados
            </span>
          </p>
        </div>
        
        <!-- ACCIONES PRINCIPALES -->
        <div class="flex items-center gap-3">
          <!-- Eliminar múltiples -->
          <button
            v-if="clientesSeleccionados.length > 0"
            @click="abrirEliminacionMasiva"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm bg-red-600 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
          >
            <i class="fas fa-trash mr-2"></i>
            Eliminar {{ clientesSeleccionados.length }} clientes
          </button>
          
          <!-- Nuevo cliente -->
          <a href="/crm-bioscom/public/clientes/create" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm bg-green-600 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Nuevo Cliente
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
            placeholder="Buscar por nombre, RUT, email..."
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-bioscom-primary focus:border-bioscom-primary"
          >
        </div>
        
        <!-- Filtro por tipo -->
        <div>
          <select v-model="filtros.tipo_cliente" @change="aplicarFiltros" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-bioscom-primary focus:border-bioscom-primary">
            <option value="">Todos los tipos</option>
            <option value="Cliente Público">Cliente Público</option>
            <option value="Cliente Privado">Cliente Privado</option>
            <option value="Revendedor">Revendedor</option>
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
        
        <!-- Filtros rápidos -->
        <div class="flex gap-2">
          <button
            @click="filtroRapido('recientes')"
            class="px-3 py-2 text-xs font-medium rounded-lg border transition-colors"
            :class="filtros.rapido === 'recientes' ? 'bg-blue-100 text-blue-800 border-blue-300' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
          >
            Recientes
          </button>
          <button
            @click="filtroRapido('activos')"
            class="px-3 py-2 text-xs font-medium rounded-lg border transition-colors"
            :class="filtros.rapido === 'activos' ? 'bg-green-100 text-green-800 border-green-300' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
          >
            Activos
          </button>
        </div>
        
        <!-- Configurar columnas -->
        <div>
          <button
            @click="mostrarConfigColumnas = !mostrarConfigColumnas"
            class="w-full px-3 py-2 text-xs font-medium rounded-lg border bg-white text-gray-700 border-gray-300 hover:bg-gray-50"
          >
            <i class="fas fa-cog mr-1"></i>
            Columnas
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
            <span class="font-medium text-green-600">{{ estadisticas.publicos }}</span> Públicos
          </span>
          <span class="text-gray-600">
            <span class="font-medium text-blue-600">{{ estadisticas.privados }}</span> Privados
          </span>
          <span class="text-gray-600">
            <span class="font-medium text-purple-600">{{ estadisticas.revendedores }}</span> Revendedores
          </span>
        </div>
        
        <div class="text-gray-600">
          Mostrando {{ clientes.length }} de {{ estadisticas.total }}
        </div>
      </div>
    </div>

    <!-- INDICADOR DE CARGA -->
    <div v-if="cargando" class="bg-white p-8 text-center">
      <i class="fas fa-spinner fa-spin text-2xl text-bioscom-primary mb-2"></i>
      <p class="text-gray-600">Cargando clientes...</p>
    </div>

    <!-- TABLA CON FILTROS EXCEL -->
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
              v-for="cliente in clientes"
              :key="cliente.id"
              class="hover:bg-gray-50 transition-colors"
              :class="{ 'bg-blue-50': clientesSeleccionados.includes(cliente.id) }"
            >
              <!-- Checkbox individual -->
              <td class="px-6 py-4">
                <input
                  type="checkbox"
                  :value="cliente.id"
                  v-model="clientesSeleccionados"
                  class="rounded border-gray-300 text-bioscom-primary focus:border-bioscom-primary focus:ring-bioscom-primary"
                >
              </td>
              
              <!-- Nombre Institución -->
              <td v-if="columnasVisibles.includes('nombre')" class="px-6 py-4">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-8 w-8">
                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                      <i class="fas fa-building text-blue-600 text-xs"></i>
                    </div>
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">{{ cliente.nombre_institucion }}</div>
                    <div class="text-sm text-gray-500">{{ cliente.direccion || 'Sin dirección' }}</div>
                  </div>
                </div>
              </td>
              
              <!-- RUT -->
              <td v-if="columnasVisibles.includes('rut')" class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ cliente.rut || 'Sin RUT' }}</div>
              </td>
              
              <!-- Tipo Cliente -->
              <td v-if="columnasVisibles.includes('tipo')" class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="getTipoColor(cliente.tipo_cliente)"
                >
                  {{ cliente.tipo_cliente }}
                </span>
              </td>
              
              <!-- Contacto -->
              <td v-if="columnasVisibles.includes('contacto')" class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ cliente.nombre_contacto || 'Sin contacto' }}</div>
              </td>
              
              <!-- Email -->
              <td v-if="columnasVisibles.includes('email')" class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ cliente.email }}</div>
              </td>
              
              <!-- Teléfono -->
              <td v-if="columnasVisibles.includes('telefono')" class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ cliente.telefono || 'Sin teléfono' }}</div>
              </td>
              
              <!-- Fecha creación -->
              <td v-if="columnasVisibles.includes('created_at')" class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ cliente.created_at }}</div>
              </td>
              
              <!-- Acciones -->
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end gap-2">
                  <a
                    :href="`/crm-bioscom/public/clientes/${cliente.id}`"
                    class="text-bioscom-primary hover:text-bioscom-secondary"
                    title="Ver detalles"
                  >
                    <i class="fas fa-eye"></i>
                  </a>
                  <a
                    :href="`/crm-bioscom/public/clientes/${cliente.id}/edit`"
                    class="text-gray-600 hover:text-gray-900"
                    title="Editar"
                  >
                    <i class="fas fa-edit"></i>
                  </a>
                  <button
                    @click="eliminarCliente(cliente.id)"
                    class="text-red-600 hover:text-red-900"
                    title="Eliminar"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
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
          Mostrando {{ paginacion.desde }} - {{ paginacion.hasta }} de {{ paginacion.total }} clientes
        </div>
        
        <div class="flex items-center gap-2">
          <!-- Tamaño de página -->
          <select v-model="paginacion.porPagina" @change="cambiarTamanoPagina" class="block px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-bioscom-primary focus:border-bioscom-primary text-sm">
            <option value="25">25 por página</option>
            <option value="50">50 por página</option>
            <option value="100">100 por página</option>
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

    <!-- MODAL DE ELIMINACIÓN MASIVA -->
    <div v-if="mostrarModalEliminacion" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">
            Eliminar {{ clientesSeleccionados.length }} clientes
          </h3>
        </div>
        
        <div class="px-6 py-4">
          <p class="text-sm text-gray-600">
            ¿Estás seguro de que deseas eliminar los clientes seleccionados? Esta acción no se puede deshacer.
          </p>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
          <button
            @click="cerrarEliminacion"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bioscom-primary transition-colors"
          >
            Cancelar
          </button>
          <button
            @click="ejecutarEliminacionMasiva"
            :disabled="eliminacion.procesando"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm bg-red-600 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <i v-if="eliminacion.procesando" class="fas fa-spinner fa-spin mr-2"></i>
            <i v-else class="fas fa-trash mr-2"></i>
            {{ eliminacion.procesando ? 'Eliminando...' : 'Eliminar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ClienteTable',
  
  data() {
    return {
      // Datos de clientes
      clientes: [],
      clientesSeleccionados: [],
      vendedoresDisponibles: [],
      
      // Filtros
      filtros: {
        busqueda: '',
        tipo_cliente: '',
        vendedor: '',
        rapido: ''
      },
      
      // Filtros estilo Excel por columna
      filtrosColumna: {
        nombre: { abierto: false, activo: false, seleccionados: [], opciones: [], opcionesFiltradas: [], busqueda: '' },
        rut: { abierto: false, activo: false, seleccionados: [], opciones: [], opcionesFiltradas: [], busqueda: '' },
        tipo: { abierto: false, activo: false, seleccionados: [], opciones: [], opcionesFiltradas: [], busqueda: '' },
        contacto: { abierto: false, activo: false, seleccionados: [], opciones: [], opcionesFiltradas: [], busqueda: '' },
        email: { abierto: false, activo: false, seleccionados: [], opciones: [], opcionesFiltradas: [], busqueda: '' },
        telefono: { abierto: false, activo: false, seleccionados: [], opciones: [], opcionesFiltradas: [], busqueda: '' }
      },
      
      // Ordenamiento
      ordenamiento: {
        campo: 'nombre_institucion',
        direccion: 'asc'
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
        { key: 'nombre', label: 'Institución' },
        { key: 'rut', label: 'RUT' },
        { key: 'tipo', label: 'Tipo' },
        { key: 'contacto', label: 'Contacto' },
        { key: 'email', label: 'Email' },
        { key: 'telefono', label: 'Teléfono' },
        { key: 'created_at', label: 'Fecha' }
      ],
      columnasVisibles: ['nombre', 'rut', 'tipo', 'contacto', 'email'],
      
      // Estadísticas
      estadisticas: {
        total: 0,
        publicos: 0,
        privados: 0,
        revendedores: 0
      },
      
      // Eliminación
      mostrarModalEliminacion: false,
      eliminacion: {
        procesando: false
      },
      
      // Control de búsqueda
      busquedaTimeout: null,
      cargando: false
    }
  },
  
  computed: {
    todasSeleccionadas() {
      return this.clientes.length > 0 && this.clientesSeleccionados.length === this.clientes.length;
    },
    
    tieneAltrosFiltrosActivos() {
      return this.filtros.busqueda || this.filtros.tipo_cliente || this.filtros.vendedor || this.filtros.rapido;
    },
    
    columnasMostradas() {
      return this.columnasDisponibles.filter(columna => this.columnasVisibles.includes(columna.key));
    }
  },
  
  mounted() {
    console.log('🚨 ClienteTable montado');
    console.log('🍞 Toast function disponible:', typeof window.mostrarToast);
    this.cargarDatos();
    this.cargarVendedores();
  },
  
  methods: {
    /**
     * CARGAR DATOS PRINCIPALES
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
          ...this.obtenerFiltrosColumnasActivos()
        };
        
        console.log('📤 Cargando clientes con parámetros:', params);
        
        const response = await axios.get('/crm-bioscom/public/api/clientes', {
          params,
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest' 
          }
        });
        
        if (response.data.success) {
          this.clientes = response.data.data.data;
          this.paginacion = {
            ...this.paginacion,
            total: response.data.data.total,
            totalPaginas: response.data.data.last_page,
            desde: response.data.data.from || 0,
            hasta: response.data.data.to || 0
          };
          
          this.estadisticas = response.data.estadisticas || this.estadisticas;
          
          console.log('✅ Clientes cargados:', this.clientes.length);
          this.actualizarOpcionesFiltrosDespuesDeCarga();
        }
        
      } catch (error) {
        console.error('❌ Error al cargar clientes:', error);
        if (window.mostrarToast) {
          window.mostrarToast('error', 'Error al cargar clientes');
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
      }
      this.aplicarFiltros();
    },
    
    limpiarFiltros() {
      this.filtros = {
        busqueda: '',
        tipo_cliente: '',
        vendedor: '',
        rapido: ''
      };
      this.aplicarFiltros();
    },
    
    /**
     * 🔥 FILTROS ESTILO EXCEL - MÉTODOS
     */
    
    // Abrir/cerrar filtro de columna
    toggleFiltroColumna(columna) {
      Object.keys(this.filtrosColumna).forEach(key => {
        if (key !== columna) {
          this.filtrosColumna[key].abierto = false;
        }
      });
      
      this.filtrosColumna[columna].abierto = !this.filtrosColumna[columna].abierto;
      
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
      
      this.clientes.forEach(cliente => {
        let valor = '';
        let texto = '';
        
        switch (columna) {
          case 'nombre':
            valor = cliente.nombre_institucion;
            texto = valor;
            break;
          case 'rut':
            valor = cliente.rut || 'Sin RUT';
            texto = valor;
            break;
          case 'tipo':
            valor = cliente.tipo_cliente;
            texto = valor;
            break;
          case 'contacto':
            valor = cliente.nombre_contacto || 'Sin contacto';
            texto = valor;
            break;
          case 'email':
            valor = cliente.email;
            texto = valor;
            break;
          case 'telefono':
            valor = cliente.telefono || 'Sin teléfono';
            texto = valor;
            break;
        }
        
        if (valores.has(valor)) {
          valores.set(valor, valores.get(valor) + 1);
        } else {
          valores.set(valor, 1);
        }
      });
      
      const opciones = Array.from(valores.entries())
        .map(([valor, count]) => ({
          valor,
          texto: valor,
          count
        }))
        .sort((a, b) => a.texto.localeCompare(b.texto));
      
      this.filtrosColumna[columna].opciones = opciones;
      this.filtrosColumna[columna].opcionesFiltradas = [...opciones];
      
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
      
      this.filtrosColumna[columna].activo = seleccionados.length < totalOpciones;
      this.filtrosColumna[columna].abierto = false;
      
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
        this.clientesSeleccionados = [];
      } else {
        this.clientesSeleccionados = this.clientes.map(c => c.id);
      }
    },
    
    /**
     * ELIMINACIÓN
     */
    abrirEliminacionMasiva() {
      this.mostrarModalEliminacion = true;
    },
    
    cerrarEliminacion() {
      this.mostrarModalEliminacion = false;
    },
    
    async eliminarCliente(id) {
      if (!confirm('¿Estás seguro de eliminar este cliente?')) return;
      
      try {
        const response = await axios.delete(`/crm-bioscom/public/clientes/${id}`, {
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        
        if (response.data.success) {
          if (window.mostrarToast) {
            window.mostrarToast('success', response.data.message);
          }
          this.cargarDatos();
        } else {
          if (window.mostrarToast) {
            window.mostrarToast('error', response.data.message);
          }
        }
      } catch (error) {
        console.error('❌ Error al eliminar cliente:', error);
        if (window.mostrarToast) {
          window.mostrarToast('error', 'Error al eliminar cliente');
        }
      }
    },
    
    async ejecutarEliminacionMasiva() {
      this.eliminacion.procesando = true;
      
      try {
        const promises = this.clientesSeleccionados.map(id =>
          axios.delete(`/crm-bioscom/public/api/clientes/${id}`, {
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
          })
        );
        
        await Promise.all(promises);
        
        if (window.mostrarToast) {
          window.mostrarToast('success', `${this.clientesSeleccionados.length} clientes eliminados`);
        }
        
        this.clientesSeleccionados = [];
        this.cerrarEliminacion();
        this.cargarDatos();
        
      } catch (error) {
        console.error('❌ Error en eliminación masiva:', error);
        if (window.mostrarToast) {
          window.mostrarToast('error', 'Error al eliminar clientes');
        }
      } finally {
        this.eliminacion.procesando = false;
      }
    },
    
    /**
     * UTILIDADES
     */
    getTipoColor(tipo) {
      const colores = {
        'Cliente Público': 'bg-green-100 text-green-800',
        'Cliente Privado': 'bg-blue-100 text-blue-800',
        'Revendedor': 'bg-purple-100 text-purple-800'
      };
      return colores[tipo] || 'bg-gray-100 text-gray-800';
    }
  }
}
</script>