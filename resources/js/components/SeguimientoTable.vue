<template>
  <div class="seguimiento-container">
    <!-- BARRA DE HERRAMIENTAS MEJORADA -->
    <div class="bg-white shadow-sm border-b border-gray-200 p-6">
      <div class="flex flex-col space-y-6">
        
        <!-- FILTROS RÁPIDOS (CRÍTICOS) - MEJORADOS -->
        <div>
          <h3 class="text-sm font-medium text-gray-700 mb-3">Filtros Rápidos</h3>
          <div class="flex flex-wrap gap-3">
            <button
              @click="aplicarFiltroRapido('atrasados')"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 border shadow-sm',
                filtroActivo === 'atrasados' 
                  ? 'bg-red-100 text-red-700 border-red-300 shadow-red-100' 
                  : 'bg-white text-gray-700 border-gray-300 hover:bg-red-50 hover:border-red-200'
              ]"
            >
              <i class="fas fa-exclamation-triangle mr-2"></i>
              Atrasados
              <span class="ml-2 px-2 py-1 bg-red-500 text-white text-xs rounded-full">
                {{ estadisticas.atrasados || 0 }}
              </span>
            </button>
            
            <button
              @click="aplicarFiltroRapido('proximos')"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 border shadow-sm',
                filtroActivo === 'proximos' 
                  ? 'bg-yellow-100 text-yellow-700 border-yellow-300 shadow-yellow-100' 
                  : 'bg-white text-gray-700 border-gray-300 hover:bg-yellow-50 hover:border-yellow-200'
              ]"
            >
              <i class="fas fa-clock mr-2"></i>
              Próximos 7 días
              <span class="ml-2 px-2 py-1 bg-yellow-500 text-white text-xs rounded-full">
                {{ estadisticas.proximos_7_dias || 0 }}
              </span>
            </button>
            
            <button
              @click="aplicarFiltroRapido('hoy')"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 border shadow-sm',
                filtroActivo === 'hoy' 
                  ? 'bg-blue-100 text-blue-700 border-blue-300 shadow-blue-100' 
                  : 'bg-white text-gray-700 border-gray-300 hover:bg-blue-50 hover:border-blue-200'
              ]"
            >
              <i class="fas fa-calendar-day mr-2"></i>
              Hoy
              <span class="ml-2 px-2 py-1 bg-blue-500 text-white text-xs rounded-full">
                {{ estadisticas.hoy || 0 }}
              </span>
            </button>
            
            <button
              @click="aplicarFiltroRapido('todos')"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 border shadow-sm',
                filtroActivo === 'todos' 
                  ? 'bg-green-100 text-green-700 border-green-300 shadow-green-100' 
                  : 'bg-white text-gray-700 border-gray-300 hover:bg-green-50 hover:border-green-200'
              ]"
            >
              <i class="fas fa-list mr-2"></i>
              Todos
              <span class="ml-2 px-2 py-1 bg-green-500 text-white text-xs rounded-full">
                {{ estadisticas.total_activos || 0 }}
              </span>
            </button>

            <button
              @click="aplicarFiltroRapido('completados_hoy')"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 border shadow-sm',
                filtroActivo === 'completados_hoy' 
                  ? 'bg-purple-100 text-purple-700 border-purple-300 shadow-purple-100' 
                  : 'bg-white text-gray-700 border-gray-300 hover:bg-purple-50 hover:border-purple-200'
              ]"
            >
              <i class="fas fa-check-circle mr-2"></i>
              Completados Hoy
              <span class="ml-2 px-2 py-1 bg-purple-500 text-white text-xs rounded-full">
                {{ estadisticas.completados_hoy || 0 }}
              </span>
            </button>
          </div>
        </div>

        <!-- ACCIONES PRINCIPALES - MEJORADAS -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div class="flex gap-3">
            <button
              @click="mostrarModalCrear = true"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm border border-blue-600"
            >
              <i class="fas fa-plus mr-2"></i>
              Nuevo Seguimiento
            </button>
            
            <button
              @click="actualizarMasivo"
              :disabled="seguimientosSeleccionados.length === 0"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm border',
                seguimientosSeleccionados.length === 0 
                  ? 'bg-gray-100 text-gray-400 border-gray-300 cursor-not-allowed' 
                  : 'bg-green-600 hover:bg-green-700 text-white border-green-600'
              ]"
            >
              <i class="fas fa-edit mr-2"></i>
              Actualizar Seleccionados
              <span v-if="seguimientosSeleccionados.length > 0" class="ml-2 px-2 py-1 bg-green-800 text-white text-xs rounded-full">
                {{ seguimientosSeleccionados.length }}
              </span>
            </button>
            
            <button
              @click="mostrarModalImportar = true"
              v-if="puedeImportar"
              class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm border border-purple-600"
            >
              <i class="fas fa-upload mr-2"></i>
              Importar Excel
            </button>

            <!-- CONFIGURAR COLUMNAS -->
            <button
              @click="mostrarModalColumnas = true"
              class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm border border-gray-600"
              title="Configurar columnas visibles"
            >
              <i class="fas fa-columns mr-2"></i>
              Columnas
            </button>
          </div>

          <!-- INDICADOR DE FILTROS ACTIVOS -->
          <div class="flex items-center gap-4">
            <div v-if="tieneFiltrosColumnasActivos" class="flex items-center gap-2">
              <span class="text-sm text-blue-600">
                <i class="fas fa-filter mr-1"></i>
                {{ contadorFiltrosActivos }} filtro(s) activo(s)
              </span>
              <button 
                @click="limpiarTodosFiltrosColumnas"
                class="text-xs text-red-600 hover:text-red-800 underline"
              >
                Limpiar todos
              </button>
            </div>
            
            <!-- Indicador de columnas ocultas -->
            <div v-if="tieneColumnasOcultas" class="flex items-center gap-2">
              <span class="text-sm text-amber-600">
                <i class="fas fa-eye-slash mr-1"></i>
                {{ contadorColumnasOcultas }} columna(s) oculta(s)
              </span>
            </div>
          </div>
        </div>

        <!-- FILTROS AVANZADOS - MEJORADOS -->
        <div>
          <h3 class="text-sm font-medium text-gray-700 mb-3">Búsqueda y Filtros</h3>
          <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
            <!-- Búsqueda rápida -->
            <div class="lg:col-span-2">
              <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input
                  v-model="filtros.busqueda"
                  @input="buscarConRetraso"
                  type="text"
                  placeholder="Buscar por cliente, RUT o cotización..."
                  class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                >
              </div>
            </div>

            <!-- Filtros en selectores -->
            <div>
              <select v-model="filtros.vendedor" @change="aplicarFiltros" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm text-sm">
                <option value="">Vendedores</option>
                <option 
                  v-for="vendedor in vendedores" 
                  :key="vendedor.id" 
                  :value="vendedor.id"
                >
                  {{ vendedor.name }}
                </option>
              </select>
            </div>

            <div>
              <select v-model="filtros.estado" @change="aplicarFiltros" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm text-sm">
                <option value="">Estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="en_proceso">En Proceso</option>
                <option value="completado">Completado</option>
                <option value="vencido">Vencido</option>
                <option value="reprogramado">Reprogramado</option>
              </select>
            </div>

            <div class="flex gap-2">
              <select v-model="filtros.prioridad" @change="aplicarFiltros" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm text-sm">
                <option value="">Prioridades</option>
                <option value="baja">Baja</option>
                <option value="media">Media</option>
                <option value="alta">Alta</option>
                <option value="urgente">Urgente</option>
              </select>

              <button 
                @click="limpiarFiltros" 
                class="px-3 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors shadow-sm"
                title="Limpiar filtros"
              >
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- TOAST NOTIFICATIONS -->
    <div 
      v-if="toast.mostrar"
      :class="[
        'fixed top-4 right-4 p-4 rounded-lg shadow-lg transition-all duration-300',
        'z-[9999]',
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

    <!-- TABLA PRINCIPAL - TIPO EXCEL EDITABLE CON FILTROS -->
    <div class="bg-white shadow overflow-hidden relative">
      <!-- Loading Overlay -->
      <div v-if="cargando" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10">
        <div class="flex items-center">
          <i class="fas fa-spinner fa-spin text-blue-500 mr-2"></i>
          Cargando seguimientos...
        </div>
      </div>

      <!-- ENCABEZADOS DE TABLA CON FILTROS TIPO EXCEL -->
      <div class="bg-gray-50 border-b border-gray-200 relative">
        <div :class="gridClasses + ' gap-2 p-3 text-xs font-medium text-gray-500 uppercase tracking-wider'">
          
          <!-- Checkbox -->
          <div v-if="columnasVisibles.checkbox" class="col-span-1">
            <input 
              type="checkbox" 
              v-model="todosMarcados" 
              @change="toggleTodos"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            >
          </div>

          <!-- Cliente con Filtro -->
          <div v-if="columnasVisibles.cliente" class="col-span-2 relative">
            <div class="flex items-center justify-between cursor-pointer hover:text-gray-700" @click="ordenar('cliente')">
              <span class="flex items-center">
                Cliente 
                <i :class="getIconoOrden('cliente')" class="ml-1"></i>
              </span>
              <button 
                @click.stop="toggleFiltroColumna('cliente')"
                :class="[
                  'ml-2 p-1 rounded hover:bg-gray-200',
                  filtrosColumnas.cliente && filtrosColumnas.cliente.length > 0 ? 'text-blue-600' : 'text-gray-400'
                ]"
                title="Filtrar por cliente"
              >
                <i class="fas fa-filter text-xs"></i>
              </button>
            </div>
            
            <!-- Dropdown de filtro -->
            <div v-if="filtroColumnaActivo === 'cliente'" 
                 class="fixed bg-white border border-gray-200 rounded-lg shadow-xl w-64 p-3"
                 :style="getDropdownPosition($event)"
                 style="z-index: 99999"
                 v-click-outside="cerrarFiltrosColumna">
              <!-- Búsqueda dentro del filtro -->
              <input 
                v-model="busquedaFiltroColumna"
                type="text"
                placeholder="Buscar cliente..."
                class="w-full px-2 py-1 text-xs border border-gray-300 rounded mb-2 focus:ring-1 focus:ring-blue-500"
              >
              
              <!-- Seleccionar todo / Deseleccionar todo -->
              <div class="flex justify-between mb-2 text-xs">
                <button @click="seleccionarTodosFiltro('cliente')" class="text-blue-600 hover:text-blue-800">
                  Seleccionar todo
                </button>
                <button @click="deseleccionarTodosFiltro('cliente')" class="text-red-600 hover:text-red-800">
                  Deseleccionar todo
                </button>
              </div>
              
              <!-- Lista de valores únicos -->
              <div class="max-h-40 overflow-y-auto">
                <label 
                  v-for="valor in valoresFiltradosColumna('cliente')" 
                  :key="valor"
                  class="flex items-center py-1 text-xs cursor-pointer hover:bg-gray-50"
                >
                  <input 
                    type="checkbox"
                    :value="valor"
                    v-model="filtrosColumnas.cliente"
                    class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  >
                  <span class="truncate">{{ valor || '(Vacío)' }}</span>
                </label>
              </div>
              
              <!-- Botones de acción -->
              <div class="flex justify-end gap-2 mt-3 pt-2 border-t">
                <button 
                  @click="aplicarFiltroColumna('cliente')"
                  class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700"
                >
                  Aplicar
                </button>
                <button 
                  @click="cerrarFiltrosColumna"
                  class="px-3 py-1 border border-gray-300 text-gray-700 text-xs rounded hover:bg-gray-50"
                >
                  Cancelar
                </button>
              </div>
            </div>
          </div>

          <!-- Cotización con Filtro -->
          <div v-if="columnasVisibles.cotizacion" class="col-span-1 relative">
            <div class="flex items-center justify-between cursor-pointer hover:text-gray-700" @click="ordenar('cotizacion')">
              <span class="flex items-center">
                Cotización
                <i :class="getIconoOrden('cotizacion')" class="ml-1"></i>
              </span>
              <button 
                @click.stop="toggleFiltroColumna('cotizacion')"
                :class="[
                  'ml-2 p-1 rounded hover:bg-gray-200',
                  filtrosColumnas.cotizacion && filtrosColumnas.cotizacion.length > 0 ? 'text-blue-600' : 'text-gray-400'
                ]"
                title="Filtrar por cotización"
              >
                <i class="fas fa-filter text-xs"></i>
              </button>
            </div>
            
            <!-- Dropdown simplificado para otros filtros -->
            <div v-if="filtroColumnaActivo === 'cotizacion'" 
                 class="fixed bg-white border border-gray-200 rounded-lg shadow-xl w-64 p-3"
                 style="z-index: 99999; top: 50%; left: 50%; transform: translate(-50%, -50%)"
                 v-click-outside="cerrarFiltrosColumna">
              <input 
                v-model="busquedaFiltroColumna"
                type="text"
                placeholder="Buscar cotización..."
                class="w-full px-2 py-1 text-xs border border-gray-300 rounded mb-2 focus:ring-1 focus:ring-blue-500"
              >
              
              <div class="flex justify-between mb-2 text-xs">
                <button @click="seleccionarTodosFiltro('cotizacion')" class="text-blue-600 hover:text-blue-800">
                  Seleccionar todo
                </button>
                <button @click="deseleccionarTodosFiltro('cotizacion')" class="text-red-600 hover:text-red-800">
                  Deseleccionar todo
                </button>
              </div>
              
              <div class="max-h-40 overflow-y-auto">
                <label 
                  v-for="valor in valoresFiltradosColumna('cotizacion')" 
                  :key="valor"
                  class="flex items-center py-1 text-xs cursor-pointer hover:bg-gray-50"
                >
                  <input 
                    type="checkbox"
                    :value="valor"
                    v-model="filtrosColumnas.cotizacion"
                    class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  >
                  <span class="truncate">{{ valor || 'Sin cotización' }}</span>
                </label>
              </div>
              
              <div class="flex justify-end gap-2 mt-3 pt-2 border-t">
                <button 
                  @click="aplicarFiltroColumna('cotizacion')"
                  class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700"
                >
                  Aplicar
                </button>
                <button 
                  @click="cerrarFiltrosColumna"
                  class="px-3 py-1 border border-gray-300 text-gray-700 text-xs rounded hover:bg-gray-50"
                >
                  Cancelar
                </button>
              </div>
            </div>
          </div>

          <!-- Vendedor -->
          <div v-if="columnasVisibles.vendedor" class="col-span-1 cursor-pointer hover:text-gray-700" @click="ordenar('vendedor')">
            Vendedor
            <i :class="getIconoOrden('vendedor')" class="ml-1"></i>
          </div>

          <!-- Estado -->
          <div v-if="columnasVisibles.estado" class="col-span-1 cursor-pointer hover:text-gray-700" @click="ordenar('estado')">
            Estado
            <i :class="getIconoOrden('estado')" class="ml-1"></i>
          </div>

          <!-- Prioridad -->
          <div v-if="columnasVisibles.prioridad" class="col-span-1 cursor-pointer hover:text-gray-700" @click="ordenar('prioridad')">
            Prioridad
            <i :class="getIconoOrden('prioridad')" class="ml-1"></i>
          </div>

          <!-- Últ. Gestión -->
          <div v-if="columnasVisibles.ultima_gestion" class="col-span-1 cursor-pointer hover:text-gray-700" @click="ordenar('ultima_gestion')">
            Últ. Gestión
            <i :class="getIconoOrden('ultima_gestion')" class="ml-1"></i>
          </div>
          
          <!-- Próx. Gestión -->
          <div v-if="columnasVisibles.proxima_gestion" class="col-span-1 cursor-pointer hover:text-gray-700" @click="ordenar('proxima_gestion')">
            Próx. Gestión
            <i :class="getIconoOrden('proxima_gestion')" class="ml-1"></i>
          </div>
          
          <!-- Notas -->
          <div v-if="columnasVisibles.notas" class="col-span-2">Notas</div>
          
          <!-- Acciones -->
          <div v-if="columnasVisibles.acciones" class="col-span-1">Acciones</div>
        </div>
      </div>

      <!-- Filas de datos -->
      <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
        <div 
          v-for="seguimiento in seguimientosFiltrados" 
          :key="seguimiento.id"
          :class="[
            gridClasses,
            'gap-2 p-3 hover:bg-gray-50 transition-colors bg-white'
          ]"
        >
          <!-- Checkbox -->
          <div v-if="columnasVisibles.checkbox" class="col-span-1 flex items-center">
            <input 
              type="checkbox" 
              :value="seguimiento.id" 
              v-model="seguimientosSeleccionados"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            >
          </div>

          <!-- Cliente -->
          <div v-if="columnasVisibles.cliente" class="col-span-2">
            <div class="text-sm font-medium text-gray-900">
              {{ seguimiento.cliente }}
            </div>
            <div class="text-xs text-gray-500" v-if="seguimiento.rut_cliente">
              {{ seguimiento.rut_cliente }}
            </div>
          </div>

          <!-- Cotización -->
          <div v-if="columnasVisibles.cotizacion" class="col-span-1">
            <span class="text-sm text-blue-600 hover:text-blue-800 cursor-pointer" 
                  @click="verCotizacion(seguimiento.cotizacion)">
              {{ seguimiento.cotizacion || 'Sin cotización' }}
            </span>
          </div>

          <!-- Vendedor -->
          <div v-if="columnasVisibles.vendedor" class="col-span-1">
            <div class="text-sm text-gray-900">{{ seguimiento.vendedor }}</div>
          </div>

          <!-- Estado (EDITABLE) - SOLO TEXTO CON COLOR -->
          <div v-if="columnasVisibles.estado" class="col-span-1">
            <select 
              :value="seguimiento.estado"
              @change="actualizarCampo(seguimiento.id, 'estado', $event.target.value)"
              class="text-xs px-2 py-1 rounded border border-gray-300 focus:ring-2 focus:ring-blue-500 bg-white"
            >
              <option value="pendiente">Pendiente</option>
              <option value="en_proceso">En Proceso</option>
              <option value="completado">Completado</option>
              <option value="vencido">Vencido</option>
              <option value="reprogramado">Reprogramado</option>
            </select>
            <!-- TEXTO DEL ESTADO CON COLOR -->
            <div class="mt-1">
              <span :class="getClaseEstado(seguimiento.estado)" class="text-xs font-medium">
                {{ getTextoEstado(seguimiento.estado) }}
              </span>
            </div>
          </div>

          <!-- Prioridad (EDITABLE) - SOLO TEXTO CON COLOR -->
          <div v-if="columnasVisibles.prioridad" class="col-span-1">
            <select 
              :value="seguimiento.prioridad"
              @change="actualizarCampo(seguimiento.id, 'prioridad', $event.target.value)"
              class="text-xs px-2 py-1 rounded border border-gray-300 focus:ring-2 focus:ring-blue-500 bg-white"
            >
              <option value="baja">Baja</option>
              <option value="media">Media</option>
              <option value="alta">Alta</option>
              <option value="urgente">Urgente</option>
            </select>
            <!-- TEXTO DE LA PRIORIDAD CON COLOR -->
            <div class="mt-1">
              <span :class="getClasePrioridad(seguimiento.prioridad)" class="text-xs font-medium">
                {{ getTextoPrioridad(seguimiento.prioridad) }}
              </span>
            </div>
          </div>

          <!-- Última Gestión -->
          <div v-if="columnasVisibles.ultima_gestion" class="col-span-1">
            <span class="text-xs text-gray-600">
              {{ seguimiento.ultima_gestion || 'Sin gestión' }}
            </span>
          </div>

          <!-- Próxima Gestión (EDITABLE) -->
          <div v-if="columnasVisibles.proxima_gestion" class="col-span-1">
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
          <div v-if="columnasVisibles.notas" class="col-span-2">
            <textarea 
              :value="seguimiento.notas"
              @blur="actualizarCampo(seguimiento.id, 'notas', $event.target.value)"
              placeholder="Agregar notas..."
              class="text-xs p-1 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 w-full resize-none"
              rows="2"
            ></textarea>
          </div>

          <!-- Acciones -->
          <div v-if="columnasVisibles.acciones" class="col-span-1 flex items-center gap-1">
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
      <div v-if="!cargando && seguimientosFiltrados.length === 0" class="text-center py-12">
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
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
          >
            Anterior
          </button>
          <button 
            @click="cambiarPagina(pagination.current_page + 1)"
            :disabled="pagination.current_page >= pagination.last_page"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
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
              <span class="font-medium">{{ seguimientosFiltrados.length }}</span>
              resultados
              <span v-if="seguimientosFiltrados.length !== pagination.total" class="text-blue-600">
                ({{ pagination.total }} total)
              </span>
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

    <!-- MODAL DE ACTUALIZACIÓN MASIVA -->
    <div v-if="mostrarModalMasivo" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[9999]">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <!-- Header del Modal -->
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Actualización Masiva</h3>
            <button @click="mostrarModalMasivo = false" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <!-- Información de selección -->
          <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded">
            <p class="text-sm text-blue-700">
              <i class="fas fa-info-circle mr-2"></i>
              Se actualizarán <strong>{{ seguimientosSeleccionados.length }}</strong> seguimientos seleccionados
            </p>
          </div>

          <!-- Formulario de actualización -->
          <div class="space-y-4">
            <!-- Estado -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
              <select v-model="actualizacionMasiva.estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Mantener actual</option>
                <option value="pendiente">Pendiente</option>
                <option value="en_proceso">En Proceso</option>
                <option value="completado">Completado</option>
                <option value="vencido">Vencido</option>
                <option value="reprogramado">Reprogramado</option>
              </select>
            </div>

            <!-- Prioridad -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Prioridad</label>
              <select v-model="actualizacionMasiva.prioridad" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Mantener actual</option>
                <option value="baja">Baja</option>
                <option value="media">Media</option>
                <option value="alta">Alta</option>
                <option value="urgente">Urgente</option>
              </select>
            </div>

            <!-- Próxima Gestión -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Próxima Gestión</label>
              <input 
                type="date" 
                v-model="actualizacionMasiva.proxima_gestion" 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
            </div>

            <!-- Vendedor -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Vendedor</label>
              <select v-model="actualizacionMasiva.vendedor_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Mantener actual</option>
                <option 
                  v-for="vendedor in vendedores" 
                  :key="vendedor.id" 
                  :value="vendedor.id"
                >
                  {{ vendedor.name }}
                </option>
              </select>
            </div>

            <!-- Notas -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Notas adicionales</label>
              <textarea 
                v-model="actualizacionMasiva.notas" 
                placeholder="Agregar notas (se añadirán a las existentes)"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                rows="3"
              ></textarea>
            </div>
          </div>

          <!-- Botones de acción -->
          <div class="flex justify-end gap-3 mt-6">
            <button 
              @click="mostrarModalMasivo = false"
              class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Cancelar
            </button>
            <button 
              @click="confirmarActualizacionMasiva"
              :disabled="procesandoMasivo"
              :class="[
                'px-4 py-2 rounded-lg text-white font-medium transition-colors',
                procesandoMasivo 
                  ? 'bg-gray-400 cursor-not-allowed' 
                  : 'bg-green-600 hover:bg-green-700'
              ]"
            >
              <i v-if="procesandoMasivo" class="fas fa-spinner fa-spin mr-2"></i>
              {{ procesandoMasivo ? 'Actualizando...' : 'Confirmar Actualización' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL DE CONFIGURAR COLUMNAS MEJORADO -->
    <div v-if="mostrarModalColumnas" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[9999]">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              <i class="fas fa-columns mr-2 text-gray-600"></i>
              Configurar Columnas Visibles
            </h3>
            <button @click="mostrarModalColumnas = false" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times"></i>
            </button>
          </div>
          
          <div class="grid grid-cols-2 gap-3">
            <label 
              v-for="(valor, columna) in columnasVisibles" 
              :key="columna" 
              class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors"
              :class="valor ? 'border-blue-300 bg-blue-50' : ''"
            >
              <input 
                type="checkbox" 
                v-model="columnasVisibles[columna]"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-3"
              >
              <div>
                <span class="text-sm font-medium text-gray-700 capitalize">
                  {{ getNombreColumna(columna) }}
                </span>
                <div class="text-xs text-gray-500">
                  {{ getDescripcionColumna(columna) }}
                </div>
              </div>
              <i v-if="valor" class="fas fa-check text-green-500 ml-auto"></i>
            </label>
          </div>
          
          <!-- Acciones rápidas -->
          <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
            <div class="flex gap-2">
              <button 
                @click="mostrarTodasColumnas"
                class="px-3 py-1 text-xs border border-green-300 text-green-700 rounded hover:bg-green-50"
              >
                <i class="fas fa-check-double mr-1"></i>
                Mostrar todas
              </button>
              <button 
                @click="ocultarTodasColumnas"
                class="px-3 py-1 text-xs border border-red-300 text-red-700 rounded hover:bg-red-50"
              >
                <i class="fas fa-eye-slash mr-1"></i>
                Ocultar todas
              </button>
              <button 
                @click="columnasEsenciales"
                class="px-3 py-1 text-xs border border-blue-300 text-blue-700 rounded hover:bg-blue-50"
              >
                <i class="fas fa-star mr-1"></i>
                Solo esenciales
              </button>
            </div>
            
            <button 
              @click="mostrarModalColumnas = false"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <i class="fas fa-save mr-2"></i>
              Guardar configuración
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Resto de modales igual... -->
    <!-- MODAL DE CREAR SEGUIMIENTO (Placeholder) -->
    <div v-if="mostrarModalCrear" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[9999]">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Crear Nuevo Seguimiento</h3>
            <button @click="mostrarModalCrear = false" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="text-center py-8">
            <i class="fas fa-plus-circle text-blue-500 text-4xl mb-4"></i>
            <p class="text-gray-600">Modal de creación en desarrollo</p>
            <button @click="mostrarModalCrear = false" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg">
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL DE IMPORTAR EXCEL (Placeholder) -->
    <div v-if="mostrarModalImportar" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[9999]">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Importar desde Excel</h3>
            <button @click="mostrarModalImportar = false" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="text-center py-8">
            <i class="fas fa-upload text-purple-500 text-4xl mb-4"></i>
            <p class="text-gray-600">Modal de importación en desarrollo</p>
            <button @click="mostrarModalImportar = false" class="mt-4 px-4 py-2 bg-purple-600 text-white rounded-lg">
              Cerrar
            </button>
          </div>
        </div>
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
      
      // FILTROS DE COLUMNAS TIPO EXCEL
      filtrosColumnas: {
        cliente: [],
        cotizacion: [],
        vendedor: [],
        estado: [],
        prioridad: []
      },
      filtroColumnaActivo: null,
      busquedaFiltroColumna: '',
      valoresUnicos: {
        cliente: [],
        cotizacion: [],
        vendedor: [],
        estado: [],
        prioridad: []
      },
      
      // Configuración de columnas visibles
      columnasVisibles: {
        checkbox: true,
        cliente: true,
        cotizacion: true,
        vendedor: true,
        estado: true,
        prioridad: true,
        ultima_gestion: false,
        proxima_gestion: true,
        notas: false,
        acciones: true
      },
      
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
      mostrarModalColumnas: false,
      
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
    // Classes CSS dinámicas para el grid
    gridClasses() {
      return 'grid grid-cols-12';
    },
    
    // Páginas para paginación
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
    },

    // SEGUIMIENTOS FILTRADOS POR COLUMNAS
    seguimientosFiltrados() {
      if (!this.seguimientos || !Array.isArray(this.seguimientos)) {
        return [];
      }
      
      let filtrados = [...this.seguimientos];

      // Aplicar filtros de columnas
      Object.keys(this.filtrosColumnas).forEach(columna => {
        const filtrosActivos = this.filtrosColumnas[columna];
        if (filtrosActivos && filtrosActivos.length > 0) {
          filtrados = filtrados.filter(seguimiento => {
            const valor = seguimiento[columna] || '';
            return filtrosActivos.includes(valor);
          });
        }
      });

      return filtrados;
    },

    // VERIFICAR SI HAY FILTROS ACTIVOS
    tieneFiltrosColumnasActivos() {
      return Object.values(this.filtrosColumnas).some(filtro => filtro && filtro.length > 0);
    },

    // CONTADOR DE FILTROS ACTIVOS
    contadorFiltrosActivos() {
      return Object.values(this.filtrosColumnas).filter(filtro => filtro && filtro.length > 0).length;
    },

    // VERIFICAR SI HAY COLUMNAS OCULTAS
    tieneColumnasOcultas() {
      return Object.values(this.columnasVisibles).some(visible => !visible);
    },

    // CONTADOR DE COLUMNAS OCULTAS
    contadorColumnasOcultas() {
      return Object.values(this.columnasVisibles).filter(visible => !visible).length;
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
        
        // URL correcta con el prefijo del proyecto
        const response = await fetch(`/crm-bioscom/public/api/seguimiento/data?${params}`);
        const data = await response.json();
        
        if (data.success) {
          this.seguimientos = data.data.data || [];
          this.pagination = data.pagination || this.pagination;
          this.estadisticas = data.estadisticas || {};
          
          // EXTRAER VALORES ÚNICOS PARA FILTROS
          this.extraerValoresUnicos();
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

    // EXTRAER VALORES ÚNICOS PARA FILTROS
    extraerValoresUnicos() {
      if (!this.seguimientos || !Array.isArray(this.seguimientos)) {
        return;
      }
      
      const columnas = ['cliente', 'cotizacion', 'vendedor', 'estado', 'prioridad'];
      
      columnas.forEach(columna => {
        const valores = [...new Set(this.seguimientos.map(s => s[columna] || ''))];
        this.valoresUnicos[columna] = valores.sort();
      });
    },

    // TOGGLE FILTRO DE COLUMNA
    toggleFiltroColumna(columna) {
      if (this.filtroColumnaActivo === columna) {
        this.cerrarFiltrosColumna();
      } else {
        this.filtroColumnaActivo = columna;
        this.busquedaFiltroColumna = '';
        
        // Inicializar filtros si están vacíos
        if (!this.filtrosColumnas[columna]) {
          this.filtrosColumnas[columna] = [...this.valoresUnicos[columna]];
        }
      }
    },

    // CERRAR FILTROS DE COLUMNA
    cerrarFiltrosColumna() {
      this.filtroColumnaActivo = null;
      this.busquedaFiltroColumna = '';
    },

    // VALORES FILTRADOS PARA EL DROPDOWN
    valoresFiltradosColumna(columna) {
      if (!this.busquedaFiltroColumna) {
        return this.valoresUnicos[columna] || [];
      }
      
      const busqueda = this.busquedaFiltroColumna.toLowerCase();
      return (this.valoresUnicos[columna] || []).filter(valor => 
        valor.toLowerCase().includes(busqueda)
      );
    },

    // SELECCIONAR TODOS LOS VALORES DEL FILTRO
    seleccionarTodosFiltro(columna) {
      this.filtrosColumnas[columna] = [...this.valoresFiltradosColumna(columna)];
    },

    // DESELECCIONAR TODOS LOS VALORES DEL FILTRO
    deseleccionarTodosFiltro(columna) {
      this.filtrosColumnas[columna] = [];
    },

    // APLICAR FILTRO DE COLUMNA
    aplicarFiltroColumna(columna) {
      this.cerrarFiltrosColumna();
      // Los filtros se aplican automáticamente a través del computed seguimientosFiltrados
    },

    // LIMPIAR TODOS LOS FILTROS DE COLUMNAS
    limpiarTodosFiltrosColumnas() {
      Object.keys(this.filtrosColumnas).forEach(columna => {
        this.filtrosColumnas[columna] = [];
      });
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
      this.limpiarTodosFiltrosColumnas();
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
        this.seguimientosSeleccionados = this.seguimientosFiltrados.map(s => s.id);
      } else {
        this.seguimientosSeleccionados = [];
      }
    },
    
    /**
     * ACTUALIZAR CAMPO INDIVIDUAL - EDICIÓN EN LÍNEA
     */
    async actualizarCampo(seguimientoId, campo, valor) {
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
          this.mostrarToast('error', 'Token CSRF no encontrado');
          return;
        }

        const response = await fetch(`/crm-bioscom/public/api/seguimiento/${seguimientoId}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content')
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
          
          // Reextraer valores únicos después de actualización
          this.extraerValoresUnicos();
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

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
          this.mostrarToast('error', 'Token CSRF no encontrado');
          return;
        }
        
        console.log('🚀 Enviando actualización masiva:', {
          seguimiento_ids: this.seguimientosSeleccionados,
          datos: datos
        });
        
        // URL correcta para la actualización masiva
        const response = await fetch('/crm-bioscom/public/api/seguimiento/update-masivo', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content')
          },
          body: JSON.stringify({
            seguimiento_ids: this.seguimientosSeleccionados,
            datos: datos
          })
        });
        
        console.log('📥 Response status:', response.status);
        const data = await response.json();
        console.log('📦 Response data:', data);
        
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
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
          this.mostrarToast('error', 'Token CSRF no encontrado');
          return;
        }

        const response = await fetch(`/crm-bioscom/public/api/seguimiento/${seguimientoId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content')
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
     * 🎨 ESTILOS Y CLASES - SOLO TEXTO CON COLOR, FONDO BLANCO
     */
    getClaseEstado(estado) {
      const clases = {
        'pendiente': 'text-blue-600',
        'en_proceso': 'text-yellow-600',
        'completado': 'text-green-600',
        'vencido': 'text-red-600',
        'reprogramado': 'text-purple-600'
      };
      
      return clases[estado] || 'text-gray-600';
    },
    
    getClasePrioridad(prioridad) {
      const clases = {
        'baja': 'text-gray-600',
        'media': 'text-blue-600',
        'alta': 'text-orange-600',
        'urgente': 'text-red-600'
      };
      
      return clases[prioridad] || 'text-gray-600';
    },

    getTextoEstado(estado) {
      const textos = {
        'pendiente': 'Pendiente',
        'en_proceso': 'En Proceso',
        'completado': 'Completado',
        'vencido': 'Vencido',
        'reprogramado': 'Reprogramado'
      };
      
      return textos[estado] || estado;
    },

    getTextoPrioridad(prioridad) {
      const textos = {
        'baja': 'Baja',
        'media': 'Media',
        'alta': 'Alta',
        'urgente': 'Urgente'
      };
      
      return textos[prioridad] || prioridad;
    },

    /**
     * 📋 CONFIGURACIÓN DE COLUMNAS - FUNCIONES MEJORADAS
     */
    getNombreColumna(columna) {
      const nombres = {
        'checkbox': 'Selección',
        'cliente': 'Cliente',
        'cotizacion': 'Cotización',
        'vendedor': 'Vendedor',
        'estado': 'Estado',
        'prioridad': 'Prioridad',
        'ultima_gestion': 'Última Gestión',
        'proxima_gestion': 'Próxima Gestión',
        'notas': 'Notas',
        'acciones': 'Acciones'
      };
      
      return nombres[columna] || columna;
    },

    getDescripcionColumna(columna) {
      const descripciones = {
        'checkbox': 'Casillas de selección',
        'cliente': 'Nombre y RUT del cliente',
        'cotizacion': 'Código de cotización',
        'vendedor': 'Vendedor asignado',
        'estado': 'Estado del seguimiento',
        'prioridad': 'Nivel de prioridad',
        'ultima_gestion': 'Fecha última gestión',
        'proxima_gestion': 'Fecha próxima gestión',
        'notas': 'Notas del seguimiento',
        'acciones': 'Botones de acción'
      };
      
      return descripciones[columna] || '';
    },

    mostrarTodasColumnas() {
      Object.keys(this.columnasVisibles).forEach(columna => {
        this.columnasVisibles[columna] = true;
      });
    },

    ocultarTodasColumnas() {
      Object.keys(this.columnasVisibles).forEach(columna => {
        this.columnasVisibles[columna] = false;
      });
      // Mantener checkbox siempre visible
      this.columnasVisibles.checkbox = true;
    },

    columnasEsenciales() {
      // Solo mostrar las columnas más importantes
      this.columnasVisibles = {
        checkbox: true,
        cliente: true,
        cotizacion: false,
        vendedor: false,
        estado: true,
        prioridad: false,
        ultima_gestion: false,
        proxima_gestion: true,
        notas: false,
        acciones: true
      };
    },

    // Posición del dropdown (simplificado)
    getDropdownPosition(event) {
      return {
        top: '50%',
        left: '50%',
        transform: 'translate(-50%, -50%)'
      };
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

  // DIRECTIVA PARA CERRAR DROPDOWNS AL HACER CLIC FUERA
  directives: {
    'click-outside': {
      beforeMount(el, binding) {
        el.clickOutsideEvent = event => {
          if (!(el === event.target || el.contains(event.target))) {
            binding.value();
          }
        };
        document.addEventListener('click', el.clickOutsideEvent);
      },
      unmounted(el) {
        document.removeEventListener('click', el.clickOutsideEvent);
      }
    }
  },
  
  watch: {
    seguimientosSeleccionados() {
      this.todosMarcados = this.seguimientosFiltrados.length > 0 && 
                          this.seguimientosSeleccionados.length === this.seguimientosFiltrados.length;
    }
  }
}
</script>