<template>
  <div class="cotizacion-form-container">
    <!-- ENCABEZADO DEL FORMULARIO -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900">
          {{ modoEdicion ? 'Editar Cotización' : 'Nueva Cotización' }}
          <span v-if="formData.codigo" class="text-sm text-gray-500 ml-2">{{ formData.codigo }}</span>
        </h2>
        
        <div class="flex items-center gap-4">
          <!-- Auto-guardado -->
          <div v-if="autoGuardado.activo" class="text-sm text-gray-500">
            <i class="fas fa-save mr-1"></i>
            {{ autoGuardado.mensaje }}
          </div>
          
          <!-- Calculadora de totales -->
          <div class="text-right">
            <div class="text-xs text-gray-500">Total</div>
            <div class="text-lg font-semibold text-blue-600">
              {{ formatearMoneda(formData.total_con_iva) }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- FORMULARIO PRINCIPAL -->
    <form @submit.prevent="enviarFormulario" class="bg-white">
      <div class="px-6 py-6 space-y-8">
        
        <!-- INFORMACIÓN BÁSICA DE LA COTIZACIÓN -->
        <div class="border-l-4 border-blue-500 pl-4">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-file-invoice mr-2 text-blue-500"></i>
            Información General
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Nombre de la Cotización -->
            <div class="md:col-span-2">
              <label for="nombre_cotizacion" class="block text-sm font-medium text-gray-700 mb-2">
                Nombre de la Cotización *
              </label>
              <input
                id="nombre_cotizacion"
                v-model="formData.nombre_cotizacion"
                type="text"
                required
                maxlength="255"
                class="form-input w-full"
                :class="errores.nombre_cotizacion ? 'border-red-500' : ''"
                placeholder="Ej: Equipos de diagnóstico para Cardiología"
              >
              <div v-if="errores.nombre_cotizacion" class="mt-1 text-sm text-red-600">
                {{ errores.nombre_cotizacion[0] }}
              </div>
            </div>

            <!-- Código (opcional) -->
            <div>
              <label for="codigo" class="block text-sm font-medium text-gray-700 mb-2">
                Código
                <span class="text-xs text-gray-500">(se genera automático)</span>
              </label>
              <input
                id="codigo"
                v-model="formData.codigo"
                type="text"
                maxlength="50"
                class="form-input w-full bg-gray-50"
                :class="errores.codigo ? 'border-red-500' : ''"
                placeholder="COT-202406-0001"
                :readonly="!modoEdicion"
              >
              <div v-if="errores.codigo" class="mt-1 text-sm text-red-600">
                {{ errores.codigo[0] }}
              </div>
            </div>

            <!-- Validez de la Oferta -->
            <div>
              <label for="validez_oferta" class="block text-sm font-medium text-gray-700 mb-2">
                Válida hasta *
              </label>
              <input
                id="validez_oferta"
                v-model="formData.validez_oferta"
                type="date"
                required
                class="form-input w-full"
                :class="errores.validez_oferta ? 'border-red-500' : ''"
                :min="fechaMinima"
              >
              <div v-if="diasVencimiento >= 0" class="mt-1 text-xs" :class="colorVencimiento">
                {{ diasVencimiento === 0 ? 'Vence hoy' : `${diasVencimiento} días restantes` }}
              </div>
              <div v-if="errores.validez_oferta" class="mt-1 text-sm text-red-600">
                {{ errores.validez_oferta[0] }}
              </div>
            </div>

            <!-- Estado -->
            <div>
              <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                Estado *
              </label>
              <select
                id="estado"
                v-model="formData.estado"
                required
                class="form-select w-full"
                :class="errores.estado ? 'border-red-500' : ''"
              >
                <option value="Pendiente">Pendiente</option>
                <option value="Enviada">Enviada</option>
                <option value="En Revisión">En Revisión</option>
                <option value="Ganada">Ganada</option>
                <option value="Perdida">Perdida</option>
                <option value="Vencida">Vencida</option>
              </select>
              <div v-if="errores.estado" class="mt-1 text-sm text-red-600">
                {{ errores.estado[0] }}
              </div>
            </div>

            <!-- Vendedor -->
            <div>
              <label for="vendedor_id" class="block text-sm font-medium text-gray-700 mb-2">
                Vendedor Asignado
              </label>
              <select
                id="vendedor_id"
                v-model="formData.vendedor_id"
                class="form-select w-full"
                :class="errores.vendedor_id ? 'border-red-500' : ''"
                :disabled="!puedeAsignarVendedor"
              >
                <option value="">Seleccionar vendedor</option>
                <option 
                  v-for="vendedor in vendedoresDisponibles" 
                  :key="vendedor.id" 
                  :value="vendedor.id"
                >
                  {{ vendedor.name }}
                </option>
              </select>
              <div v-if="errores.vendedor_id" class="mt-1 text-sm text-red-600">
                {{ errores.vendedor_id[0] }}
              </div>
            </div>
          </div>
        </div>

        <!-- INFORMACIÓN DEL CLIENTE -->
        <div class="border-l-4 border-green-500 pl-4">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-user-tie mr-2 text-green-500"></i>
            Cliente y Contacto
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Selector de Cliente -->
            <div class="md:col-span-2">
              <label for="cliente_search" class="block text-sm font-medium text-gray-700 mb-2">
                Cliente *
              </label>
              <div class="relative">
                <input
                  id="cliente_search"
                  v-model="busquedaCliente"
                  type="text"
                  required
                  class="form-input w-full pr-10"
                  :class="errores.cliente_id ? 'border-red-500' : ''"
                  placeholder="Buscar cliente por nombre o RUT..."
                  @input="buscarClientes"
                  @focus="mostrarSugerenciasCliente = true"
                  autocomplete="off"
                >
                <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                
                <!-- Sugerencias de clientes -->
                <div 
                  v-if="mostrarSugerenciasCliente && clientesEncontrados.length > 0"
                  class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                >
                  <div
                    v-for="cliente in clientesEncontrados"
                    :key="cliente.id"
                    @click="seleccionarCliente(cliente)"
                    class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                  >
                    <div class="font-medium text-gray-900">{{ cliente.nombre_institucion }}</div>
                    <div class="text-sm text-gray-500">{{ cliente.rut }} • {{ cliente.email }}</div>
                  </div>
                </div>
                
                <!-- Cliente seleccionado -->
                <div v-if="clienteSeleccionado" class="mt-2 p-3 bg-blue-50 rounded-lg">
                  <div class="flex items-center justify-between">
                    <div>
                      <div class="font-medium text-blue-900">{{ clienteSeleccionado.nombre_institucion }}</div>
                      <div class="text-sm text-blue-700">{{ clienteSeleccionado.rut }} • {{ clienteSeleccionado.tipo_cliente }}</div>
                    </div>
                    <button
                      type="button"
                      @click="limpiarCliente"
                      class="text-blue-600 hover:text-blue-800"
                    >
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div v-if="errores.cliente_id" class="mt-1 text-sm text-red-600">
                {{ errores.cliente_id[0] }}
              </div>
            </div>

            <!-- Información manual del cliente (si no se selecciona uno existente) -->
            <div v-if="!clienteSeleccionado">
              <label for="nombre_institucion" class="block text-sm font-medium text-gray-700 mb-2">
                Nombre de la Institución *
              </label>
              <input
                id="nombre_institucion"
                v-model="formData.nombre_institucion"
                type="text"
                required
                maxlength="255"
                class="form-input w-full"
                :class="errores.nombre_institucion ? 'border-red-500' : ''"
                placeholder="Nombre de la institución"
              >
              <div v-if="errores.nombre_institucion" class="mt-1 text-sm text-red-600">
                {{ errores.nombre_institucion[0] }}
              </div>
            </div>

            <!-- Nombre del Contacto -->
            <div :class="!clienteSeleccionado ? '' : 'md:col-span-2'">
              <label for="nombre_contacto" class="block text-sm font-medium text-gray-700 mb-2">
                Nombre del Contacto *
              </label>
              <input
                id="nombre_contacto"
                v-model="formData.nombre_contacto"
                type="text"
                required
                maxlength="255"
                class="form-input w-full"
                :class="errores.nombre_contacto ? 'border-red-500' : ''"
                placeholder="Nombre del contacto"
              >
              <div v-if="errores.nombre_contacto" class="mt-1 text-sm text-red-600">
                {{ errores.nombre_contacto[0] }}
              </div>
            </div>

            <!-- Información de contacto del vendedor -->
            <div class="md:col-span-2">
              <label for="info_contacto_vendedor" class="block text-sm font-medium text-gray-700 mb-2">
                Información de Contacto del Vendedor
              </label>
              <input
                id="info_contacto_vendedor"
                v-model="formData.info_contacto_vendedor"
                type="text"
                maxlength="255"
                class="form-input w-full"
                placeholder="Email o teléfono del vendedor para la cotización"
              >
            </div>
          </div>
        </div>

        <!-- PRODUCTOS COTIZADOS -->
        <div class="border-l-4 border-yellow-500 pl-4">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-box mr-2 text-yellow-500"></i>
            Productos Cotizados
          </h3>
          
          <!-- Buscador de productos -->
          <div class="mb-4">
            <div class="relative">
              <input
                v-model="busquedaProducto"
                type="text"
                class="form-input w-full pr-10"
                placeholder="Buscar productos por nombre o código..."
                @input="buscarProductos"
                @focus="mostrarSugerenciasProducto = true"
              >
              <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
              
              <!-- Sugerencias de productos -->
              <div 
                v-if="mostrarSugerenciasProducto && productosEncontrados.length > 0"
                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
              >
                <div
                  v-for="producto in productosEncontrados"
                  :key="producto.id"
                  @click="agregarProducto(producto)"
                  class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                >
                  <div class="flex justify-between items-start">
                    <div>
                      <div class="font-medium text-gray-900">{{ producto.nombre }}</div>
                      <div class="text-sm text-gray-500">{{ producto.categoria }}</div>
                    </div>
                    <div class="text-right">
                      <div class="font-medium text-blue-600">{{ formatearMoneda(producto.precio_neto) }}</div>
                      <div class="text-xs text-gray-500">+ IVA</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Lista de productos agregados -->
          <div class="space-y-3">
            <div
              v-for="(producto, index) in formData.productos_cotizados"
              :key="`producto-${index}`"
              class="border border-gray-200 rounded-lg p-4 bg-gray-50"
            >
              <div class="grid grid-cols-1 md:grid-cols-7 gap-4 items-center">
                <!-- Nombre del producto -->
                <div class="md:col-span-2">
                  <label class="block text-xs font-medium text-gray-700 mb-1">Producto</label>
                  <input
                    v-model="producto.nombre"
                    type="text"
                    required
                    class="form-input w-full text-sm"
                    placeholder="Nombre del producto"
                  >
                </div>

                <!-- Cantidad -->
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Cantidad</label>
                  <input
                    v-model.number="producto.cantidad"
                    type="number"
                    min="1"
                    max="9999"
                    required
                    class="form-input w-full text-sm"
                    @input="calcularTotales"
                  >
                </div>

                <!-- Precio Unitario -->
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Precio Unit.</label>
                  <input
                    v-model.number="producto.precio_unitario"
                    type="number"
                    min="0"
                    step="0.01"
                    required
                    class="form-input w-full text-sm"
                    @input="calcularTotales"
                  >
                </div>

                <!-- Descuento -->
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Desc. %</label>
                  <input
                    v-model.number="producto.descuento"
                    type="number"
                    min="0"
                    max="100"
                    step="0.1"
                    class="form-input w-full text-sm"
                    @input="calcularTotales"
                  >
                </div>

                <!-- Subtotal -->
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Subtotal</label>
                  <div class="text-sm font-medium text-gray-900 py-2">
                    {{ formatearMoneda(calcularSubtotalProducto(producto)) }}
                  </div>
                </div>

                <!-- Acciones -->
                <div class="flex justify-end">
                  <button
                    type="button"
                    @click="eliminarProducto(index)"
                    class="text-red-600 hover:text-red-800 p-1"
                    title="Eliminar producto"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- Botón agregar producto manual -->
            <button
              type="button"
              @click="agregarProductoManual"
              class="w-full border-2 border-dashed border-gray-300 rounded-lg p-4 hover:border-gray-400 transition-colors"
            >
              <i class="fas fa-plus text-gray-400 mr-2"></i>
              <span class="text-gray-600">Agregar producto manualmente</span>
            </button>

            <!-- Sin productos -->
            <div v-if="formData.productos_cotizados.length === 0" class="text-center py-8 text-gray-500">
              <i class="fas fa-box-open text-4xl mb-4"></i>
              <p>No hay productos agregados a la cotización</p>
              <p class="text-sm">Busca productos arriba o agrégalos manualmente</p>
            </div>
          </div>
        </div>

        <!-- TOTALES -->
        <div class="border-l-4 border-purple-500 pl-4" v-if="formData.productos_cotizados.length > 0">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-calculator mr-2 text-purple-500"></i>
            Resumen de Totales
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="text-sm text-gray-600">Subtotal Neto</div>
              <div class="text-xl font-semibold text-gray-900">{{ formatearMoneda(formData.total_neto) }}</div>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="text-sm text-gray-600">IVA (19%)</div>
              <div class="text-xl font-semibold text-gray-900">{{ formatearMoneda(formData.iva) }}</div>
            </div>
            
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
              <div class="text-sm text-blue-600">Total con IVA</div>
              <div class="text-2xl font-bold text-blue-800">{{ formatearMoneda(formData.total_con_iva) }}</div>
            </div>
          </div>
        </div>

        <!-- CONDICIONES COMERCIALES -->
        <div class="border-l-4 border-red-500 pl-4">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-handshake mr-2 text-red-500"></i>
            Condiciones Comerciales
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Forma de Pago -->
            <div>
              <label for="forma_pago" class="block text-sm font-medium text-gray-700 mb-2">
                Forma de Pago
              </label>
              <select
                id="forma_pago"
                v-model="formData.forma_pago"
                class="form-select w-full"
              >
                <option value="">Seleccionar forma de pago</option>
                <option value="Contado contra entrega">Contado contra entrega</option>
                <option value="30 días fecha factura">30 días fecha factura</option>
                <option value="60 días fecha factura">60 días fecha factura</option>
                <option value="90 días fecha factura">90 días fecha factura</option>
                <option value="Leasing financiero">Leasing financiero</option>
                <option value="Programa gobierno">Programa gobierno</option>
                <option value="Licitación pública">Licitación pública</option>
              </select>
            </div>

            <!-- Plazo de Entrega -->
            <div>
              <label for="plazo_entrega" class="block text-sm font-medium text-gray-700 mb-2">
                Plazo de Entrega
              </label>
              <input
                id="plazo_entrega"
                v-model="formData.plazo_entrega"
                type="text"
                maxlength="255"
                class="form-input w-full"
                placeholder="Ej: 15 días hábiles"
              >
            </div>

            <!-- Garantía Técnica -->
            <div class="md:col-span-2">
              <label for="garantia_tecnica" class="block text-sm font-medium text-gray-700 mb-2">
                Garantía Técnica
              </label>
              <textarea
                id="garantia_tecnica"
                v-model="formData.garantia_tecnica"
                rows="3"
                maxlength="1000"
                class="form-textarea w-full"
                placeholder="Describir condiciones de garantía técnica..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- INFORMACIÓN ADICIONAL -->
        <div class="border-l-4 border-gray-500 pl-4">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-info-circle mr-2 text-gray-500"></i>
            Información Adicional
          </h3>
          
          <div class="space-y-4">
            <!-- Información Adicional -->
            <div>
              <label for="informacion_adicional" class="block text-sm font-medium text-gray-700 mb-2">
                Información Adicional
              </label>
              <textarea
                id="informacion_adicional"
                v-model="formData.informacion_adicional"
                rows="3"
                maxlength="1000"
                class="form-textarea w-full"
                placeholder="Información adicional relevante para la cotización..."
              ></textarea>
            </div>

            <!-- Descripción de Opcionales -->
            <div>
              <label for="descripcion_opcionales" class="block text-sm font-medium text-gray-700 mb-2">
                Productos y Servicios Opcionales
              </label>
              <textarea
                id="descripcion_opcionales"
                v-model="formData.descripcion_opcionales"
                rows="3"
                maxlength="1000"
                class="form-textarea w-full"
                placeholder="Describir productos o servicios opcionales..."
              ></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- BOTONES DE ACCIÓN -->
      <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-500">
            <i class="fas fa-info-circle mr-1"></i>
            {{ modoEdicion ? 'Editando cotización existente' : 'Creando nueva cotización' }}
          </div>
          
          <div class="flex gap-3">
            <button
              type="button"
              @click="cancelar"
              class="btn-secondary"
              :disabled="enviando"
            >
              <i class="fas fa-times mr-2"></i>
              Cancelar
            </button>
            
            <button
              type="button"
              @click="guardarBorrador"
              class="btn-outline"
              :disabled="enviando"
              v-if="!modoEdicion"
            >
              <i class="fas fa-save mr-2"></i>
              Guardar Borrador
            </button>
            
            <button
              type="button"
              @click="previsualizarPDF"
              class="btn-outline"
              :disabled="enviando || formData.productos_cotizados.length === 0"
            >
              <i class="fas fa-eye mr-2"></i>
              Previsualizar
            </button>
            
            <button
              type="submit"
              class="btn-primary"
              :disabled="enviando || !formularioValido"
            >
              <i v-if="enviando" class="fas fa-spinner fa-spin mr-2"></i>
              <i v-else class="fas fa-check mr-2"></i>
              {{ modoEdicion ? 'Actualizar Cotización' : 'Crear Cotización' }}
            </button>
          </div>
        </div>
      </div>
    </form>

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
  name: 'CotizacionForm',
  props: {
    initialCotizacion: {
      type: Object,
      default: () => null
    },
    vendedoresDisponibles: {
      type: Array,
      default: () => []
    },
    puedeAsignarVendedor: {
      type: Boolean,
      default: false
    },
    clientePreseleccionado: {
      type: Object,
      default: () => null
    }
  },
  
  data() {
    return {
      // Datos del formulario
      formData: {
        nombre_cotizacion: '',
        codigo: '',
        nombre_institucion: '',
        nombre_contacto: '',
        info_contacto_vendedor: '',
        validez_oferta: '',
        forma_pago: '',
        plazo_entrega: '',
        garantia_tecnica: '',
        informacion_adicional: '',
        descripcion_opcionales: '',
        cliente_id: null,
        vendedor_id: null,
        productos_cotizados: [],
        total_neto: 0,
        iva: 0,
        total_con_iva: 0,
        estado: 'Pendiente'
      },
      
      // Estados del formulario
      enviando: false,
      modoEdicion: false,
      
      // Validación
      errores: {},
      
      // Búsqueda de clientes
      busquedaCliente: '',
      clientesEncontrados: [],
      mostrarSugerenciasCliente: false,
      clienteSeleccionado: null,
      busquedaClienteTimeout: null,
      
      // Búsqueda de productos
      busquedaProducto: '',
      productosEncontrados: [],
      mostrarSugerenciasProducto: false,
      busquedaProductoTimeout: null,
      
      // Auto-guardado
      autoGuardado: {
        activo: false,
        mensaje: 'Guardado automático activado',
        intervalo: null
      },
      
      // Notificaciones
      toast: {
        mostrar: false,
        tipo: '',
        mensaje: ''
      }
    }
  },
  
  computed: {
    formularioValido() {
      return this.formData.nombre_cotizacion.trim() !== '' &&
             this.formData.nombre_contacto.trim() !== '' &&
             this.formData.validez_oferta !== '' &&
             (this.clienteSeleccionado || this.formData.nombre_institucion.trim() !== '') &&
             this.formData.productos_cotizados.length > 0 &&
             Object.keys(this.errores).length === 0;
    },
    
    fechaMinima() {
      return new Date().toISOString().split('T')[0];
    },
    
    diasVencimiento() {
      if (!this.formData.validez_oferta) return null;
      
      const fechaVencimiento = new Date(this.formData.validez_oferta);
      const hoy = new Date();
      const diferencia = Math.ceil((fechaVencimiento - hoy) / (1000 * 60 * 60 * 24));
      
      return diferencia;
    },
    
    colorVencimiento() {
      if (this.diasVencimiento === null) return '';
      if (this.diasVencimiento < 0) return 'text-red-600';
      if (this.diasVencimiento <= 3) return 'text-red-600';
      if (this.diasVencimiento <= 7) return 'text-yellow-600';
      return 'text-green-600';
    }
  },
  
  watch: {
    'formData.productos_cotizados': {
      handler() {
        this.calcularTotales();
      },
      deep: true
    }
  },
  
  mounted() {
    this.initializeForm();
    this.configurarAutoGuardado();
    this.configurarClickAfuera();
    
    // Establecer validez por defecto (30 días)
    if (!this.formData.validez_oferta) {
      const fechaFutura = new Date();
      fechaFutura.setDate(fechaFutura.getDate() + 30);
      this.formData.validez_oferta = fechaFutura.toISOString().split('T')[0];
    }
  },
  
  beforeDestroy() {
    if (this.autoGuardado.intervalo) {
      clearInterval(this.autoGuardado.intervalo);
    }
    
    document.removeEventListener('click', this.handleClickAfuera);
  },
  
  methods: {
    /**
     * INICIALIZAR FORMULARIO
     */
    initializeForm() {
      if (this.initialCotizacion) {
        this.modoEdicion = true;
        this.formData = { ...this.formData, ...this.initialCotizacion };
        
        // Cargar cliente si existe
        if (this.initialCotizacion.cliente) {
          this.clienteSeleccionado = this.initialCotizacion.cliente;
          this.busquedaCliente = this.clienteSeleccionado.nombre_institucion;
        }
        
        // Asegurar que productos_cotizados sea un array
        if (!Array.isArray(this.formData.productos_cotizados)) {
          this.formData.productos_cotizados = [];
        }
      }
      
      // Cliente preseleccionado
      if (this.clientePreseleccionado) {
        this.seleccionarCliente(this.clientePreseleccionado);
      }
      
      // Auto-asignar vendedor actual si no tiene permisos para cambiar
      if (!this.puedeAsignarVendedor && !this.formData.vendedor_id) {
        // Se asigna automáticamente en el backend
      }
    },
    
    /**
     * BÚSQUEDA DE CLIENTES
     */
    buscarClientes() {
      clearTimeout(this.busquedaClienteTimeout);
      
      if (this.busquedaCliente.length < 2) {
        this.clientesEncontrados = [];
        return;
      }
      
      this.busquedaClienteTimeout = setTimeout(async () => {
        try {
          const response = await fetch(`/api/buscar-clientes?q=${encodeURIComponent(this.busquedaCliente)}`);
          const data = await response.json();
          
          this.clientesEncontrados = data || [];
          
        } catch (error) {
          console.error('Error al buscar clientes:', error);
          this.clientesEncontrados = [];
        }
      }, 300);
    },
    
    seleccionarCliente(cliente) {
      this.clienteSeleccionado = cliente;
      this.busquedaCliente = cliente.nombre_institucion;
      this.mostrarSugerenciasCliente = false;
      
      // Llenar datos del formulario
      this.formData.cliente_id = cliente.id;
      this.formData.nombre_institucion = cliente.nombre_institucion;
      this.formData.nombre_contacto = cliente.nombre_contacto || '';
      
      this.clientesEncontrados = [];
    },
    
    limpiarCliente() {
      this.clienteSeleccionado = null;
      this.busquedaCliente = '';
      this.formData.cliente_id = null;
      this.formData.nombre_institucion = '';
      this.formData.nombre_contacto = '';
      this.clientesEncontrados = [];
    },
    
    /**
     * BÚSQUEDA DE PRODUCTOS
     */
    buscarProductos() {
      clearTimeout(this.busquedaProductoTimeout);
      
      if (this.busquedaProducto.length < 2) {
        this.productosEncontrados = [];
        return;
      }
      
      this.busquedaProductoTimeout = setTimeout(async () => {
        try {
          const response = await fetch(`/api/buscar-productos?q=${encodeURIComponent(this.busquedaProducto)}`);
          const data = await response.json();
          
          this.productosEncontrados = data || [];
          
        } catch (error) {
          console.error('Error al buscar productos:', error);
          this.productosEncontrados = [];
        }
      }, 300);
    },
    
    agregarProducto(producto) {
      const productoExistente = this.formData.productos_cotizados.find(p => p.producto_id === producto.id);
      
      if (productoExistente) {
        productoExistente.cantidad += 1;
      } else {
        this.formData.productos_cotizados.push({
          producto_id: producto.id,
          nombre: producto.nombre,
          cantidad: 1,
          precio_unitario: producto.precio_neto,
          descuento: 0
        });
      }
      
      this.busquedaProducto = '';
      this.productosEncontrados = [];
      this.mostrarSugerenciasProducto = false;
      
      this.calcularTotales();
    },
    
    agregarProductoManual() {
      this.formData.productos_cotizados.push({
        producto_id: null,
        nombre: '',
        cantidad: 1,
        precio_unitario: 0,
        descuento: 0
      });
    },
    
    eliminarProducto(index) {
      this.formData.productos_cotizados.splice(index, 1);
      this.calcularTotales();
    },
    
    /**
     * CÁLCULOS
     */
    calcularSubtotalProducto(producto) {
      const cantidad = producto.cantidad || 0;
      const precio = producto.precio_unitario || 0;
      const descuento = producto.descuento || 0;
      
      const subtotal = cantidad * precio;
      return subtotal - (subtotal * descuento / 100);
    },
    
    calcularTotales() {
      let totalNeto = 0;
      
      this.formData.productos_cotizados.forEach(producto => {
        totalNeto += this.calcularSubtotalProducto(producto);
      });
      
      this.formData.total_neto = totalNeto;
      this.formData.iva = totalNeto * 0.19;
      this.formData.total_con_iva = totalNeto + this.formData.iva;
    },
    
    formatearMoneda(valor) {
      return new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP'
      }).format(valor || 0);
    },
    
    /**
     * ENVIAR FORMULARIO
     */
    async enviarFormulario() {
      if (!this.formularioValido) {
        this.mostrarToast('warning', 'Por favor completa todos los campos obligatorios');
        return;
      }
      
      this.enviando = true;
      this.errores = {};
      
      try {
        const url = this.modoEdicion 
          ? `/cotizaciones/${this.initialCotizacion.id}`
          : '/cotizaciones';
          
        const method = this.modoEdicion ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(this.formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.mostrarToast('success', data.message);
          
          // Limpiar borrador
          localStorage.removeItem('cotizacion_borrador');
          
          // Emitir evento de éxito
          this.$emit('cotizacion-guardada', data.data);
          
          // Redireccionar
          setTimeout(() => {
            window.location.href = data.data.redirect_url || '/cotizaciones';
          }, 1500);
          
        } else {
          if (data.errors) {
            this.errores = data.errors;
          }
          this.mostrarToast('error', data.message || 'Error al guardar cotización');
        }
        
      } catch (error) {
        console.error('Error al enviar formulario:', error);
        this.mostrarToast('error', 'Error de conexión al guardar cotización');
      } finally {
        this.enviando = false;
      }
    },
    
    /**
     * GUARDAR BORRADOR
     */
    guardarBorrador() {
      const borrador = {
        ...this.formData,
        cliente_seleccionado: this.clienteSeleccionado,
        timestamp: new Date().toISOString()
      };
      
      localStorage.setItem('cotizacion_borrador', JSON.stringify(borrador));
      this.mostrarToast('success', 'Borrador guardado localmente');
    },
    
    /**
     * CARGAR BORRADOR
     */
    cargarBorrador() {
      const borrador = localStorage.getItem('cotizacion_borrador');
      if (borrador) {
        const data = JSON.parse(borrador);
        
        if (confirm('¿Deseas cargar el borrador guardado anteriormente?')) {
          this.formData = { ...this.formData, ...data };
          
          if (data.cliente_seleccionado) {
            this.seleccionarCliente(data.cliente_seleccionado);
          }
          
          delete this.formData.timestamp;
          localStorage.removeItem('cotizacion_borrador');
          
          this.calcularTotales();
          this.mostrarToast('success', 'Borrador cargado');
        }
      }
    },
    
    /**
     * PREVISUALIZAR PDF
     */
    previsualizarPDF() {
      if (this.formData.productos_cotizados.length === 0) {
        this.mostrarToast('warning', 'Agrega al menos un producto para previsualizar');
        return;
      }
      
      // TODO: Implementar previsualización PDF
      this.mostrarToast('warning', 'Funcionalidad de previsualización en desarrollo');
    },
    
    /**
     * CANCELAR FORMULARIO
     */
    cancelar() {
      if (this.formularioTieneCambios()) {
        if (!confirm('¿Estás seguro de que deseas cancelar? Los cambios no guardados se perderán.')) {
          return;
        }
      }
      
      this.$emit('formulario-cancelado');
      window.history.back();
    },
    
    /**
     * AUTO-GUARDADO
     */
    configurarAutoGuardado() {
      if (!this.modoEdicion) return;
      
      this.autoGuardado.intervalo = setInterval(() => {
        if (this.formularioTieneCambios()) {
          this.guardarBorrador();
          this.autoGuardado.activo = true;
        }
      }, 60000); // Cada minuto
    },
    
    /**
     * CLICK AFUERA PARA CERRAR SUGERENCIAS
     */
    configurarClickAfuera() {
      document.addEventListener('click', this.handleClickAfuera);
    },
    
    handleClickAfuera(event) {
      if (!event.target.closest('.relative')) {
        this.mostrarSugerenciasCliente = false;
        this.mostrarSugerenciasProducto = false;
      }
    },
    
    /**
     * VERIFICAR CAMBIOS
     */
    formularioTieneCambios() {
      if (!this.initialCotizacion) {
        return Object.values(this.formData).some(value => {
          if (Array.isArray(value)) return value.length > 0;
          return value && value.toString().trim() !== '';
        });
      }
      
      // Comparar con datos iniciales
      return JSON.stringify(this.formData) !== JSON.stringify(this.initialCotizacion);
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
  
  created() {
    // Cargar borrador si existe y no está en modo edición
    if (!this.modoEdicion) {
      this.cargarBorrador();
    }
  }
}
</script>

<style scoped>
/* Clases CSS personalizadas para Bioscom */
.cotizacion-form-container {
  @apply max-w-6xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden;
}

.form-input {
  @apply border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200;
}

.form-select {
  @apply border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200;
}

.form-textarea {
  @apply border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none transition-colors duration-200;
}

.btn-primary {
  @apply bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200;
}

.btn-secondary {
  @apply bg-gray-600 hover:bg-gray-700 disabled:bg-gray-300 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200;
}

.btn-outline {
  @apply border border-gray-300 hover:bg-gray-50 disabled:bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors duration-200;
}

/* Animaciones suaves */
.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  @apply transform scale-[1.01];
}

/* Responsive */
@media (max-width: 768px) {
  .cotizacion-form-container {
    @apply mx-4;
  }
  
  .grid.grid-cols-7 {
    @apply grid-cols-1;
  }
  
  .grid.grid-cols-3 {
    @apply grid-cols-1;
  }
}
</style>