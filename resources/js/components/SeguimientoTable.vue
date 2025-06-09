<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-3xl font-bold text-gray-900 flex items-center">
          <i class="fas fa-tasks text-blue-600 mr-3"></i>
          Módulo de Seguimiento
        </h1>
        <div class="flex space-x-3">
          <button 
            @click="abrirModalNuevo" 
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors"
          >
            <i class="fas fa-plus mr-2"></i> Nuevo Seguimiento
          </button>
          <button 
            @click="abrirModalImportar" 
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors"
          >
            <i class="fas fa-file-excel mr-2"></i> Importar Excel
          </button>
        </div>
      </div>

      <!-- Estadísticas -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-6 rounded-lg shadow border border-red-200">
          <div class="text-center">
            <h3 class="text-lg font-semibold text-red-600 mb-2">Atrasados</h3>
            <p class="text-3xl font-bold text-red-600">{{ estadisticas.atrasados }}</p>
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow border border-yellow-200">
          <div class="text-center">
            <h3 class="text-lg font-semibold text-yellow-600 mb-2">Próximos 7 días</h3>
            <p class="text-3xl font-bold text-yellow-600">{{ estadisticas.proximos }}</p>
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow border border-green-200">
          <div class="text-center">
            <h3 class="text-lg font-semibold text-green-600 mb-2">Completados Hoy</h3>
            <p class="text-3xl font-bold text-green-600">{{ estadisticas.completados_hoy }}</p>
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow border border-blue-200">
          <div class="text-center">
            <h3 class="text-lg font-semibold text-blue-600 mb-2">Total</h3>
            <p class="text-3xl font-bold text-blue-600">{{ estadisticas.total }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros y selección múltiple -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div class="space-y-4">
        <!-- Filtros rápidos -->
        <div class="flex flex-wrap gap-2">
          <button 
            @click="aplicarFiltro('atrasados')"
            :class="filtroActivo === 'atrasados' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-600 hover:bg-red-200'"
            class="px-4 py-2 rounded-lg flex items-center transition-colors"
          >
            <i class="fas fa-exclamation-triangle mr-2"></i> Atrasados
          </button>
          <button 
            @click="aplicarFiltro('proximos')"
            :class="filtroActivo === 'proximos' ? 'bg-yellow-600 text-white' : 'bg-yellow-100 text-yellow-600 hover:bg-yellow-200'"
            class="px-4 py-2 rounded-lg flex items-center transition-colors"
          >
            <i class="fas fa-clock mr-2"></i> Próximos 7 días
          </button>
          <button 
            @click="aplicarFiltro('todos')"
            :class="filtroActivo === 'todos' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-600 hover:bg-blue-200'"
            class="px-4 py-2 rounded-lg flex items-center transition-colors"
          >
            <i class="fas fa-list mr-2"></i> Todos
          </button>
        </div>

        <!-- Filtros adicionales -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div class="col-span-1">
            <label class="block text-sm font-medium text-gray-700">Vendedor</label>
            <select v-model="filtros.vendedor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" @change="cargarSeguimientos">
              <option value="">Todos los vendedores</option>
              <option v-for="vendedor in vendedores" :key="vendedor.id" :value="vendedor.id">
                {{ vendedor.name }}
              </option>
            </select>
          </div>

          <div class="col-span-1">
            <label class="block text-sm font-medium text-gray-700">Estado</label>
            <select v-model="filtros.estado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" @change="cargarSeguimientos">
              <option value="">Todos</option>
              <option value="pendiente">Pendiente</option>
              <option value="en_proceso">En Proceso</option>
              <option value="completado">Completado</option>
              <option value="vencido">Vencido</option>
              <option value="reprogramado">Reprogramado</option>
            </select>
          </div>

          <div class="col-span-1">
            <label class="block text-sm font-medium text-gray-700">Prioridad</label>
            <select v-model="filtros.prioridad" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" @change="cargarSeguimientos">
              <option value="">Todas</option>
              <option value="baja">Baja</option>
              <option value="media">Media</option>
              <option value="alta">Alta</option>
              <option value="urgente">Urgente</option>
            </select>
          </div>

          <div class="col-span-1">
            <label class="block text-sm font-medium text-gray-700">Buscar Cliente</label>
            <input 
              type="text" 
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" 
              v-model="filtros.buscar_cliente" 
              @input="buscarClienteConRetraso"
              placeholder="Nombre o RUT del cliente"
            >
          </div>

          <div class="col-span-1 flex items-end">
            <button @click="limpiarFiltros" class="w-full bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors">
              <i class="fas fa-broom mr-2"></i> Limpiar
            </button>
          </div>
        </div>

        <!-- Selección múltiple y acciones -->
        <div v-if="seguimientosSeleccionados.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
          <div class="flex justify-between items-center">
            <span class="text-blue-700 flex items-center">
              <i class="fas fa-check-square mr-2"></i>
              {{ seguimientosSeleccionados.length }} seguimiento(s) seleccionado(s)
            </span>
            <div class="flex space-x-2">
              <button @click="abrirModalUpdateMasivo" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-sm transition-colors">
                <i class="fas fa-edit mr-1"></i> Actualizar Seleccionados
              </button>
              <button @click="deseleccionarTodos" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm transition-colors">
                <i class="fas fa-times mr-1"></i> Deseleccionar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="cargando" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      <p class="mt-4 text-gray-600">Cargando seguimientos...</p>
    </div>

    <!-- Tabla con edición en línea -->
    <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
          <i class="fas fa-table mr-2"></i> 
          Lista de Seguimientos ({{ paginacion.total }} registros)
        </h2>
        <button @click="cargarSeguimientos" class="text-blue-600 hover:text-blue-700 flex items-center transition-colors">
          <i class="fas fa-refresh mr-1"></i> Actualizar
        </button>
      </div>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                <input 
                  type="checkbox" 
                  class="form-check-input"
                  :checked="todosMarcados"
                  @change="toggleTodos"
                >
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cotización</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioridad</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Gestión</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Próxima Gestión</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendedor</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr 
              v-for="seguimiento in seguimientos" 
              :key="seguimiento.id"
              :class="obtenerClaseFilaSeguimiento(seguimiento)"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-3 py-4">
                <input 
                  type="checkbox" 
                  class="form-check-input"
                  :value="seguimiento.id"
                  v-model="seguimientosSeleccionados"
                >
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ seguimiento.cliente?.nombre_institucion || seguimiento.cliente?.nombre || 'Cliente N/A' }}</div>
                <div class="text-sm text-gray-500">{{ seguimiento.cliente?.rut || '' }}</div>
                <div v-if="seguimiento.cliente?.nombre_contacto" class="text-xs text-gray-400">
                  Contacto: {{ seguimiento.cliente.nombre_contacto }}
                </div>
              </td>
              <td class="px-6 py-4">
                <!-- Edición en línea de estado -->
                <select 
                  v-model="seguimiento.estado" 
                  @change="actualizarCampo(seguimiento, 'estado', $event.target.value)"
                  :class="getEstadoSelectClass(seguimiento.estado)"
                  class="text-sm border rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                >
                  <option value="pendiente">Pendiente</option>
                  <option value="en_proceso">En Proceso</option>
                  <option value="completado">Completado</option>
                  <option value="vencido">Vencido</option>
                  <option value="reprogramado">Reprogramado</option>
                </select>
              </td>
              <td class="px-6 py-4">
                <!-- Edición en línea de prioridad -->
                <select 
                  v-model="seguimiento.prioridad" 
                  @change="actualizarCampo(seguimiento, 'prioridad', $event.target.value)"
                  :class="getPrioridadSelectClass(seguimiento.prioridad)"
                  class="text-sm border rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                >
                  <option value="baja">Baja</option>
                  <option value="media">Media</option>
                  <option value="alta">Alta</option>
                  <option value="urgente">Urgente</option>
                </select>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                <span v-if="seguimiento.ultima_gestion">
                  {{ formatearFecha(seguimiento.ultima_gestion) }}
                </span>
                <span v-else class="text-gray-400 italic">Pendiente</span>
              </td>
              <td class="px-6 py-4">
                <!-- Edición en línea de fecha -->
                <input 
                  type="date" 
                  v-model="seguimiento.proxima_gestion"
                  @change="actualizarCampo(seguimiento, 'proxima_gestion', $event.target.value)"
                  class="text-sm border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                >
                <div class="text-xs text-gray-500 mt-1">{{ getDiasRestantes(seguimiento.proxima_gestion) }}</div>
              </td>
              <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                  {{ seguimiento.vendedor?.name || 'N/A' }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex space-x-1">
                  <button 
                    @click="verDetalles(seguimiento)"
                    class="text-blue-600 hover:text-blue-700 p-1 rounded hover:bg-blue-50 transition-colors"
                    title="Ver detalles"
                  >
                    <i class="fas fa-eye"></i>
                  </button>
                  <button 
                    @click="editarSeguimiento(seguimiento)"
                    class="text-green-600 hover:text-green-700 p-1 rounded hover:bg-green-50 transition-colors"
                    title="Editar completo"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                  <button 
                    @click="eliminarSeguimiento(seguimiento)"
                    class="text-red-600 hover:text-red-700 p-1 rounded hover:bg-red-50 transition-colors"
                    title="Eliminar"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <p v-if="!cargando && seguimientos.length === 0" class="text-center text-muted p-4">
          No se encontraron seguimientos con los filtros aplicados.
        </p>
      </div>

      <!-- Paginación -->
      <div class="card-footer" v-if="paginacion.last_page > 1">
        <nav>
          <ul class="pagination pagination-sm justify-content-center mb-0">
            <li class="page-item" :class="{ disabled: paginacion.current_page <= 1 }">
              <button class="page-link" @click="cambiarPagina(paginacion.current_page - 1)">
                Anterior
              </button>
            </li>
            <li 
              v-for="pagina in paginas" 
              :key="pagina" 
              class="page-item"
              :class="{ active: pagina === paginacion.current_page }"
            >
              <button class="page-link" @click="cambiarPagina(pagina)">
                {{ pagina }}
              </button>
            </li>
            <li class="page-item" :class="{ disabled: paginacion.current_page >= paginacion.last_page }">
              <button class="page-link" @click="cambiarPagina(paginacion.current_page + 1)">
                Siguiente
              </button>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- Modal para actualización masiva -->
    <div class="modal fade" id="modalUpdateMasivo" tabindex="-1" aria-labelledby="modalUpdateMasivoLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalUpdateMasivoLabel">Actualización Masiva</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="alert alert-info">
              <i class="fas fa-info-circle"></i>
              Se actualizarán {{ seguimientosSeleccionados.length }} seguimiento(s) seleccionado(s)
            </p>
            
            <div class="mb-3">
              <label class="form-label">Estado</label>
              <select v-model="updateMasivo.estado" class="form-select">
                <option value="">No cambiar</option>
                <option value="pendiente">Pendiente</option>
                <option value="en_proceso">En Proceso</option>
                <option value="completado">Completado</option>
                <option value="vencido">Vencido</option>
                <option value="reprogramado">Reprogramado</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Prioridad</label>
              <select v-model="updateMasivo.prioridad" class="form-select">
                <option value="">No cambiar</option>
                <option value="baja">Baja</option>
                <option value="media">Media</option>
                <option value="alta">Alta</option>
                <option value="urgente">Urgente</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Próxima Gestión</label>
              <input type="date" v-model="updateMasivo.proxima_gestion" class="form-control">
            </div>

            <div class="mb-3">
              <label class="form-label">Vendedor</label>
              <select v-model="updateMasivo.vendedor_id" class="form-select">
                <option value="">No cambiar</option>
                <option v-for="vendedor in vendedores" :key="vendedor.id" :value="vendedor.id">
                  {{ vendedor.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              Cancelar
            </button>
            <button type="button" class="btn btn-warning" @click="ejecutarUpdateMasivo">
              <i class="fas fa-save"></i> Actualizar Registros
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para nuevo seguimiento -->
    <div class="modal fade" id="modalNuevoSeguimiento" tabindex="-1" aria-labelledby="modalNuevoSeguimientoLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalNuevoSeguimientoLabel">Nuevo Seguimiento</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="guardarNuevoSeguimiento">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Cliente *</label>
                    <select v-model="nuevoSeguimiento.cliente_id" class="form-select" required>
                      <option value="">Seleccionar cliente</option>
                      <!-- Aquí implementar búsqueda de clientes -->
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Vendedor *</label>
                    <select v-model="nuevoSeguimiento.vendedor_id" class="form-select" required>
                      <option value="">Seleccionar vendedor</option>
                      <option v-for="vendedor in vendedores" :key="vendedor.id" :value="vendedor.id">
                        {{ vendedor.name }}
                      </option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">Estado</label>
                    <select v-model="nuevoSeguimiento.estado" class="form-select">
                      <option value="pendiente">Pendiente</option>
                      <option value="en_proceso">En Proceso</option>
                      <option value="completado">Completado</option>
                      <option value="vencido">Vencido</option>
                      <option value="reprogramado">Reprogramado</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">Prioridad</label>
                    <select v-model="nuevoSeguimiento.prioridad" class="form-select">
                      <option value="baja">Baja</option>
                      <option value="media">Media</option>
                      <option value="alta">Alta</option>
                      <option value="urgente">Urgente</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">Próxima Gestión *</label>
                    <input type="date" v-model="nuevoSeguimiento.proxima_gestion" class="form-control" required>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Notas</label>
                <textarea v-model="nuevoSeguimiento.notas" class="form-control" rows="3"></textarea>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              Cancelar
            </button>
            <button type="button" class="btn btn-primary" @click="guardarNuevoSeguimiento">
              <i class="fas fa-save"></i> Guardar Seguimiento
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para importar Excel -->
    <div class="modal fade" id="modalImportar" tabindex="-1" aria-labelledby="modalImportarLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalImportarLabel">Importar desde Excel</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Archivo Excel</label>
              <input 
                type="file" 
                class="form-control" 
                accept=".xlsx,.xls,.csv"
                @change="seleccionarArchivo"
              >
              <div class="form-text">
                Formatos soportados: .xlsx, .xls, .csv (máximo 2MB)
              </div>
            </div>
            <div class="alert alert-info">
              <strong>Formato requerido:</strong>
              <ul class="mb-0">
                <li>Cliente (nombre o RUT)</li>
                <li>Vendedor (nombre o email)</li>
                <li>Próxima Gestión (YYYY-MM-DD)</li>
                <li>Estado (opcional)</li>
                <li>Prioridad (opcional)</li>
              </ul>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              Cancelar
            </button>
            <button 
              type="button" 
              class="btn btn-success" 
              @click="importarArchivo"
              :disabled="!archivoSeleccionado"
            >
              <i class="fas fa-upload"></i> Importar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
// Importa los modales de Bootstrap de la forma correcta
// No se importa { Modal } directamente aquí
// Accedemos a window.bootstrap.Modal después de que bootstrap.js lo haya cargado

export default {
  name: 'SeguimientoTable',
  props: {
    vendedores: {
      type: Array,
      default: () => []
    },
    csrfToken: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      seguimientos: [],
      seguimientosSeleccionados: [],
      cargando: false,
      filtroActivo: 'todos',
      filtros: {
        vendedor_id: '',
        estado: '',
        prioridad: '',
        buscar_cliente: '',
        fecha_desde: '',
        fecha_hasta: ''
      },
      paginacion: {
        current_page: 1,
        last_page: 1,
        total: 0,
        // Asegúrate de que estas propiedades estén inicializadas
        from: 0, 
        to: 0,
        prev_page_url: null,
        next_page_url: null,
      },
      updateMasivo: {
        estado: '',
        prioridad: '',
        proxima_gestion: '',
        vendedor_id: ''
      },
      nuevoSeguimiento: {
        cliente_id: '',
        vendedor_id: '',
        estado: 'pendiente',
        prioridad: 'media',
        proxima_gestion: '',
        notas: ''
      },
      archivoSeleccionado: null,
      buscarClienteTimeout: null,
      estadisticas: {
        total: 0,
        atrasados: 0,
        proximos: 0,
        completados_hoy: 0
      },
      mostrarModalNuevo: false, // Controla la visibilidad del modal Nuevo
      mostrarModalImportar: false, // Controla la visibilidad del modal Importar
      mostrarModalUpdateMasivo: false, // Controla la visibilidad del modal Actualización Masiva
      mostrarToast: false, // Controla la visibilidad del toast
      mensajeToast: '',    // Contenido del mensaje del toast
    }
  },
  computed: {
    todosMarcados() {
      return this.seguimientos.length > 0 && 
             this.seguimientosSeleccionados.length === this.seguimientos.length;
    },
    paginas() {
      const paginas = [];
      const actual = this.paginacion.current_page;
      const total = this.paginacion.last_page;
      
      // Lógica para mostrar solo algunas páginas (ej. 2 antes, 2 después de la actual)
      let startPage = Math.max(1, actual - 2);
      let endPage = Math.min(total, actual + 2);

      // Ajustar el rango si está cerca del principio o del final
      if (endPage - startPage + 1 < 5) { // Si hay menos de 5 páginas en el rango, expandir
        if (actual - 1 < 2) { // Cerca del principio
          endPage = Math.min(total, 5);
        } else if (total - actual < 2) { // Cerca del final
          startPage = Math.max(1, total - 4);
        }
      }

      for (let i = startPage; i <= endPage; i++) {
        paginas.push(i);
      }
      
      return paginas;
    }
  },
  mounted() {
    console.log('✅ SeguimientoTable con edición en línea montado');
    this.cargarSeguimientos();
    this.configurarFechaMinima();
    
    // Exponer métodos al objeto global para interacción desde Blade (si es necesario)
    window.seguimientoApp = {
      abrirModalNuevo: this.abrirModalNuevo,
      abrirModalImportar: this.abrirModalImportar,
      abrirModalUpdateMasivo: this.abrirModalUpdateMasivo, // Exponer también este
    };
  },
  methods: {
    async cargarSeguimientos() {
      this.cargando = true;
      try {
        const params = new URLSearchParams({
          filtro: this.filtroActivo,
          page: this.paginacion.current_page,
          ...this.filtros
        });

        const response = await axios.get(`/api/seguimiento?${params.toString()}`);
        const data = response.data;

        if (data.success) {
          this.seguimientos = data.data.data || [];
          this.paginacion = {
            current_page: data.data.current_page,
            last_page: data.data.last_page,
            total: data.data.total,
            from: data.data.from || 0,
            to: data.data.to || 0,
            prev_page_url: data.data.prev_page_url,
            next_page_url: data.data.next_page_url,
          };
          this.estadisticas = data.stats || {};
          console.log('✅ Seguimientos cargados:', this.seguimientos.length);
        } else {
          console.warn('⚠️ API sin datos:', data.message);
          this.seguimientos = [];
        }
      } catch (error) {
        console.error('❌ Error al cargar seguimientos:', error);
        this.seguimientos = [];
        this.mostrarToastError('Error de conexión al cargar seguimientos.');
      } finally {
        this.cargando = false;
      }
    },

    aplicarFiltro(filtro) {
      this.filtroActivo = filtro;
      this.paginacion.current_page = 1;
      this.seguimientosSeleccionados = []; // Limpiar selección
      this.cargarSeguimientos();
    },

    limpiarFiltros() {
      this.filtros = {
        vendedor_id: '',
        estado: '',
        prioridad: '',
        buscar_cliente: '',
        fecha_desde: '',
        fecha_hasta: ''
      };
      this.filtroActivo = 'todos';
      this.paginacion.current_page = 1;
      this.cargarSeguimientos();
    },

    buscarClienteConRetraso() {
      clearTimeout(this.buscarClienteTimeout);
      this.buscarClienteTimeout = setTimeout(() => {
        this.paginacion.current_page = 1; // Reiniciar página al buscar
        this.cargarSeguimientos();
      }, 500);
    },

    async actualizarCampo(seguimiento, campo, valor) {
      console.log(`Actualizando ${campo} a ${valor} para seguimiento ${seguimiento.id}`);
      
      try {
        const response = await axios.put(`/seguimiento/${seguimiento.id}`, { [campo]: valor });
        const data = response.data;
        
        if (data.success) {
          this.mostrarToastExito(`${campo} actualizado exitosamente`);
          // Actualizar el objeto seguimiento con los datos devueltos
          Object.assign(seguimiento, data.data);
          
          // Recargar estadísticas si la actualización individual afecta los contadores
          this.cargarSeguimientos(); 
        } else {
          this.mostrarToastError(data.message || 'Error al actualizar');
          // Revertir el cambio
          this.cargarSeguimientos();
        }
      } catch (error) {
        console.error('Error:', error);
        this.mostrarToastError('Error de conexión al actualizar.');
        this.cargarSeguimientos();
      }
    },

    toggleTodos() {
      if (this.todosMarcados) {
        this.seguimientosSeleccionados = [];
      } else {
        this.seguimientosSeleccionados = this.seguimientos.map(s => s.id);
      }
    },

    deseleccionarTodos() {
      this.seguimientosSeleccionados = [];
    },

    abrirModalUpdateMasivo() {
      this.updateMasivo = {
        estado: '',
        prioridad: '',
        proxima_gestion: '',
        vendedor_id: ''
      };
      // Usar window.bootstrap.Modal
      const modal = new window.bootstrap.Modal(document.getElementById('modalUpdateMasivo'));
      modal.show();
    },

    async ejecutarUpdateMasivo() {
      try {
        const response = await axios.post('/api/seguimiento/update-masivo', {
          ids: this.seguimientosSeleccionados,
          ...this.updateMasivo,
          _token: this.csrfToken 
        });

        const data = response.data;
        
        if (data.success) {
          this.mostrarToastExito(data.message);
          this.deseleccionarTodos();
          this.cargarSeguimientos();
          
          const modal = window.bootstrap.Modal.getInstance(document.getElementById('modalUpdateMasivo'));
          if (modal) modal.hide();
        } else {
          this.mostrarToastError(data.message || 'Error en la actualización masiva');
        }
      } catch (error) {
        console.error('Error:', error);
        this.mostrarToastError('Error de conexión en actualización masiva.');
      }
    },

    cambiarPagina(pagina) {
      if (pagina >= 1 && pagina <= this.paginacion.last_page) {
        this.paginacion.current_page = pagina;
        this.cargarSeguimientos();
      }
    },

    abrirModalNuevo() {
      this.nuevoSeguimiento = {
        cliente_id: '',
        vendedor_id: '',
        estado: 'pendiente',
        prioridad: 'media',
        proxima_gestion: '',
        notas: ''
      };
      
      const modal = new window.bootstrap.Modal(document.getElementById('modalNuevoSeguimiento'));
      modal.show();
    },

    async guardarNuevoSeguimiento() {
      try {
        const response = await axios.post('/seguimiento', this.nuevoSeguimiento, {
          headers: {
            'X-CSRF-TOKEN': this.csrfToken
          }
        });

        const data = response.data;
        
        if (data.success) {
          this.mostrarToastExito('Seguimiento creado exitosamente');
          this.cargarSeguimientos();
          
          const modal = window.bootstrap.Modal.getInstance(document.getElementById('modalNuevoSeguimiento'));
          if (modal) modal.hide();
        } else {
          this.mostrarToastError(data.message || 'Error al crear el seguimiento');
        }
      } catch (error) {
        console.error('Error:', error);
        this.mostrarToastError('Error de conexión');
      }
    },

    abrirModalImportar() {
      this.archivoSeleccionado = null;
      const modal = new window.bootstrap.Modal(document.getElementById('modalImportar'));
      modal.show();
    },

    seleccionarArchivo(event) {
      this.archivoSeleccionado = event.target.files[0];
    },

    async importarArchivo() {
      if (!this.archivoSeleccionado) return;

      const formData = new FormData();
      formData.append('archivo', this.archivoSeleccionado);
      formData.append('_token', this.csrfToken); 

      try {
        const response = await axios.post('/seguimiento/importar', formData, {
          headers: {
            'Content-Type': 'multipart/form-data', 
            'X-CSRF-TOKEN': this.csrfToken
          }
        });

        const data = response.data;
        
        if (data.success) {
          this.mostrarToastExito('Archivo importado exitosamente');
          this.cargarSeguimientos();
          
          const modal = window.bootstrap.Modal.getInstance(document.getElementById('modalImportar'));
          if (modal) modal.hide();
        } else {
          this.mostrarToastError(data.message || 'Error al importar el archivo');
        }
      } catch (error) {
        console.error('Error:', error);
        this.mostrarToastError('Error de conexión');
      }
    },

    async eliminarSeguimiento(seguimiento) {
      if (!confirm(`¿Está seguro de eliminar el seguimiento de ${seguimiento.cliente?.nombre_institucion || 'este cliente'}?`)) {
        return;
      }

      try {
        const response = await axios.delete(`/seguimiento/${seguimiento.id}`, {
          headers: {
            'X-CSRF-TOKEN': this.csrfToken
          }
        });

        const data = response.data;
        
        if (data.success) {
          this.mostrarToastExito('Seguimiento eliminado exitosamente');
          this.cargarSeguimientos();
        } else {
          this.mostrarToastError(data.message || 'Error al eliminar');
        }
      } catch (error) {
        console.error('Error:', error);
        this.mostrarToastError('Error de conexión');
      }
    },

    editarSeguimiento(seguimiento) {
      console.log('Editar seguimiento:', seguimiento);
      alert('Modal de edición completa en desarrollo...'); // Temporal
    },

    configurarFechaMinima() {
      const hoy = new Date().toISOString().split('T')[0];
      this.nuevoSeguimiento.proxima_gestion = hoy;
    },

    formatearFecha(fecha) {
      if (!fecha) return '';
      return new Date(fecha).toLocaleDateString('es-CL');
    },

    getDiasRestantes(fecha) {
      if (!fecha) return '';
      
      const hoy = new Date();
      const fechaObj = new Date(fecha);
      const diff = Math.ceil((fechaObj - hoy) / (1000 * 60 * 60 * 24));
      
      if (diff < 0) {
        return `${Math.abs(diff)} día${Math.abs(diff) !== 1 ? 's' : ''} atrasado`;
      } else if (diff === 0) {
        return 'HOY';
      } else if (diff === 1) {
        return 'Mañana';
      } else {
        return `En ${diff} días`;
      }
    },

    obtenerClaseFilaSeguimiento(seguimiento) {
      const hoy = new Date();
      const fecha = new Date(seguimiento.proxima_gestion);
      
      if (fecha < hoy && seguimiento.estado !== 'completado') {
        return 'bg-red-50 border-l-4 border-red-500'; // Atrasado
      }
      
      if (fecha.toDateString() === hoy.toDateString()) {
        return 'bg-yellow-50 border-l-4 border-yellow-500'; // Hoy
      }
      
      if (seguimiento.estado === 'completado') {
        return 'bg-green-50'; // Completado
      }
      
      return ''; // Normal
    },

    getEstadoSelectClass(estado) {
      const clases = {
        'pendiente': 'border-yellow-300 bg-yellow-50 text-yellow-800',
        'en_proceso': 'border-blue-300 bg-blue-50 text-blue-800',
        'completado': 'border-green-300 bg-green-50 text-green-800',
        'vencido': 'border-red-300 bg-red-50 text-red-800',
        'reprogramado': 'border-gray-300 bg-gray-50 text-gray-800'
      };
      return clases[estado] || 'border-gray-300 bg-gray-50 text-gray-800';
    },

    getPrioridadSelectClass(prioridad) {
      const clases = {
        'baja': 'border-gray-300 bg-gray-50 text-gray-700',
        'media': 'border-blue-300 bg-blue-50 text-blue-700',
        'alta': 'border-orange-300 bg-orange-50 text-orange-700',
        'urgente': 'border-red-300 bg-red-50 text-red-700'
      };
      return clases[prioridad] || 'border-gray-300 bg-gray-50 text-gray-700';
    },

    // Métodos para modales (ya definidos en la plantilla)
    // Se gestionan por propiedades mostrarModalNuevo, etc. y sus respectivos
    // cierres en los botones de cerrar modal y el footer.
    // Las funciones de abrir/cerrar solo cambian el estado de esas propiedades.

    // Métodos para mostrar Toast (ya definidos)
    // mostrarToastExito y mostrarToastError ya están implementados abajo
  }
}
</script>
<style scoped>
/* Estilos específicos del componente */

/* Puedes añadir estilos generales del contenedor aquí */
.seguimiento-container {
  /* Por ejemplo, para un contenedor más específico si es necesario */
}

/* Estilos para la tabla */
.min-w-full {
  min-width: 100%;
}

.divide-y > * + * {
  border-top-width: 1px;
}

.divide-gray-200 {
  border-color: #e5e7eb; /* gray-200 */
}

.bg-gray-50 {
  background-color: #f9fafb; /* gray-50 */
}

.px-6 {
  padding-left: 1.5rem;
  padding-right: 1.5rem;
}

.py-3 {
  padding-top: 0.75rem;
  padding-bottom: 0.75rem;
}

.px-3 {
  padding-left: 0.75rem;
  padding-right: 0.75rem;
}

.text-left {
  text-align: left;
}

.text-xs {
  font-size: 0.75rem;
}

.font-medium {
  font-weight: 500;
}

.text-gray-500 {
  color: #6b7280; /* gray-500 */
}

.uppercase {
  text-transform: uppercase;
}

.tracking-wider {
  letter-spacing: 0.05em;
}

.w-12 {
  width: 3rem;
}

.bg-white {
  background-color: #fff;
}

.shadow-sm {
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.border {
  border-width: 1px;
}

.border-gray-200 {
  border-color: #e5e7eb;
}

.rounded-lg {
  border-radius: 0.5rem;
}

.overflow-hidden {
  overflow: hidden;
}

.overflow-x-auto {
  overflow-x: auto;
}

/* Clases para estados de fila (Tailwind) */
.bg-red-50 { background-color: #fef2f2; }
.border-red-500 { border-color: #ef4444; } /* rojo */
.bg-yellow-50 { background-color: #fffbeb; }
.border-yellow-500 { border-color: #f59e0b; } /* amarillo */
.bg-green-50 { background-color: #ecfdf5; }
.border-green-500 { border-color: #10b981; } /* verde */
.bg-blue-50 { background-color: #eff6ff; }
.border-blue-500 { border-color: #3b82f6; } /* azul para en_proceso */
.bg-gray-50 { background-color: #f9fafb; }
.border-gray-500 { border-color: #6b7280; } /* gris para reprogramado/baja */


/* Estilos de botones (Tailwind) */
.bg-blue-600 { background-color: #2563eb; } /* Primario */
.hover\:bg-blue-700:hover { background-color: #1d4ed8; }
.text-white { color: #fff; }
.px-4 { padding-left: 1rem; padding-right: 1rem; }
.py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
.rounded-lg { border-radius: 0.5rem; }
.flex { display: flex; }
.items-center { align-items: center; }
.mr-2 { margin-right: 0.5rem; }
.space-x-3 > * + * { margin-left: 0.75rem; }
.text-red-600 { color: #dc2626; }
.border-red-200 { border-color: #fecaca; }
.bg-red-100 { background-color: #fee2e2; }
.hover\:bg-red-200:hover { background-color: #fecaca; }

/* Más estilos Tailwind */
.text-gray-900 { color: #111827; }
.font-bold { font-weight: 700; }
.text-3xl { font-size: 1.875rem; }
.gap-4 { gap: 1rem; }
.grid { display: grid; }
.grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
.md\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
.p-6 { padding: 1.5rem; }
.shadow { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); }
.text-lg { font-size: 1.125rem; }
.font-semibold { font-weight: 600; }
.mb-2 { margin-bottom: 0.5rem; }
.text-yellow-600 { color: #d97706; }
.border-yellow-200 { border-color: #fde68a; }
.text-green-600 { color: #059669; }
.border-green-200 { border-color: #a7f3d0; }
.text-blue-600 { color: #2563eb; }
.border-blue-200 { border-color: #bfdbfe; }

/* Alertas */
.bg-blue-50 { background-color: #eff6ff; }
.border-blue-200 { border-color: #bfdbfe; }
.text-blue-700 { color: #1d4ed8; }
.p-4 { padding: 1rem; }
.mt-4 { margin-top: 1rem; }
.space-x-2 > * + * { margin-left: 0.75rem; }
.px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
.py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
.text-sm { font-size: 0.875rem; }

/* Loading spinner */
.animate-spin { animation: spin 1s linear infinite; }
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.h-12 { height: 3rem; }
.w-12 { width: 3rem; }
.border-b-2 { border-bottom-width: 2px; }

/* Text muted for no data message */
.text-muted { color: #6c757d; }

/* Headers de tabla sticky */
.table-light { background-color: #f8f9fa; }

/* Table data cells */
.px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
.py-4 { padding-top: 1rem; padding-bottom: 1rem; }
.text-sm { font-size: 0.875rem; }
.font-medium { font-weight: 500; }
.text-gray-900 { color: #111827; }
.text-gray-500 { color: #6b7280; }
.text-xs { font-size: 0.75rem; }
.text-gray-400 { color: #9ca3af; }
.italic { font-style: italic; }
.mt-1 { margin-top: 0.25rem; }

/* Badges */
.bg-gray-100 { background-color: #f3f4f6; }
.text-gray-800 { color: #1f2937; }
.rounded-full { border-radius: 9999px; }
.px-2\.5 { padding-left: 0.625rem; padding-right: 0.625rem; }
.py-0\.5 { padding-top: 0.125rem; padding-bottom: 0.125rem; }

/* Buttons */
.bg-blue-600 { background-color: #2563eb; }
.hover\:bg-blue-700:hover { background-color: #1d4ed8; }
.text-white { color: #fff; }
.px-4 { padding-left: 1rem; padding-right: 1rem; }
.py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
.rounded-lg { border-radius: 0.5rem; }
.flex { display: flex; }
.items-center { align-items: center; }
.mr-2 { margin-right: 0.5rem; }
.space-x-3 > * + * { margin-left: 0.75rem; }
.text-red-600 { color: #dc2626; }
.border-red-200 { border-color: #fecaca; }
.bg-red-100 { background-color: #fee2e2; }
.hover\:bg-red-200:hover { background-color: #fecaca; }
</style>
