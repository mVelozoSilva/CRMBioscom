<template>
  <div class="cliente-form-container">
    <!-- ENCABEZADO DEL FORMULARIO -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900">
          {{ modoEdicion ? 'Editar Cliente' : 'Nuevo Cliente' }}
        </h2>
        <div class="text-sm text-gray-500">
          <i class="fas fa-info-circle mr-1"></i>
          Los campos marcados con * son obligatorios
        </div>
      </div>
    </div>

    <!-- FORMULARIO PRINCIPAL -->
    <form @submit.prevent="enviarFormulario" class="bg-white shadow-sm">
      <div class="px-6 py-6 space-y-6">
        
        <!-- INFORMACIÓN BÁSICA DE LA INSTITUCIÓN -->
        <div class="border-l-4 border-blue-500 pl-4">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-building mr-2 text-blue-500"></i>
            Información de la Institución
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nombre de la Institución -->
            <div class="md:col-span-2">
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
                :class="errores.nombre_institucion ? 'border-red-500 focus:ring-red-500' : ''"
                placeholder="Ej: Hospital Regional de Santiago"
              >
              <div v-if="errores.nombre_institucion" class="mt-1 text-sm text-red-600">
                {{ errores.nombre_institucion[0] }}
              </div>
            </div>

            <!-- RUT -->
            <div>
              <label for="rut" class="block text-sm font-medium text-gray-700 mb-2">
                RUT de la Institución
              </label>
              <input
                id="rut"
                v-model="formData.rut"
                type="text"
                maxlength="12"
                class="form-input w-full"
                :class="errores.rut ? 'border-red-500 focus:ring-red-500' : ''"
                placeholder="12.345.678-9"
                @input="formatearRut"
                @blur="validarRut"
              >
              <div v-if="errores.rut" class="mt-1 text-sm text-red-600">
                {{ errores.rut[0] }}
              </div>
              <div v-if="rutInvalido" class="mt-1 text-sm text-red-600">
                El RUT ingresado no es válido
              </div>
            </div>

            <!-- Tipo de Cliente -->
            <div>
              <label for="tipo_cliente" class="block text-sm font-medium text-gray-700 mb-2">
                Tipo de Cliente
              </label>
              <select
                id="tipo_cliente"
                v-model="formData.tipo_cliente"
                class="form-select w-full"
                :class="errores.tipo_cliente ? 'border-red-500 focus:ring-red-500' : ''"
              >
                <option value="">Seleccionar tipo</option>
                <option value="Cliente Público">Cliente Público</option>
                <option value="Cliente Privado">Cliente Privado</option>
                <option value="Revendedor">Revendedor</option>
              </select>
              <div v-if="errores.tipo_cliente" class="mt-1 text-sm text-red-600">
                {{ errores.tipo_cliente[0] }}
              </div>
            </div>

            <!-- Dirección -->
            <div class="md:col-span-2">
              <label for="direccion" class="block text-sm font-medium text-gray-700 mb-2">
                Dirección
              </label>
              <input
                id="direccion"
                v-model="formData.direccion"
                type="text"
                maxlength="255"
                class="form-input w-full"
                :class="errores.direccion ? 'border-red-500 focus:ring-red-500' : ''"
                placeholder="Ej: Av. Libertador Bernardo O'Higgins 1234, Santiago"
              >
              <div v-if="errores.direccion" class="mt-1 text-sm text-red-600">
                {{ errores.direccion[0] }}
              </div>
            </div>
          </div>
        </div>

        <!-- CONTACTO PRINCIPAL -->
        <div class="border-l-4 border-green-500 pl-4">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-user mr-2 text-green-500"></i>
            Contacto Principal
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nombre del Contacto -->
            <div>
              <label for="nombre_contacto" class="block text-sm font-medium text-gray-700 mb-2">
                Nombre del Contacto
              </label>
              <input
                id="nombre_contacto"
                v-model="formData.nombre_contacto"
                type="text"
                maxlength="255"
                class="form-input w-full"
                :class="errores.nombre_contacto ? 'border-red-500 focus:ring-red-500' : ''"
                placeholder="Ej: Dr. Juan Pérez"
              >
              <div v-if="errores.nombre_contacto" class="mt-1 text-sm text-red-600">
                {{ errores.nombre_contacto[0] }}
              </div>
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Email *
              </label>
              <input
                id="email"
                v-model="formData.email"
                type="email"
                required
                maxlength="255"
                class="form-input w-full"
                :class="errores.email ? 'border-red-500 focus:ring-red-500' : ''"
                placeholder="contacto@hospital.cl"
              >
              <div v-if="errores.email" class="mt-1 text-sm text-red-600">
                {{ errores.email[0] }}
              </div>
            </div>

            <!-- Teléfono -->
            <div class="md:col-span-2">
              <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
                Teléfono
              </label>
              <input
                id="telefono"
                v-model="formData.telefono"
                type="tel"
                maxlength="20"
                class="form-input w-full"
                :class="errores.telefono ? 'border-red-500 focus:ring-red-500' : ''"
                placeholder="+56 9 1234 5678"
                @input="formatearTelefono"
              >
              <div v-if="errores.telefono" class="mt-1 text-sm text-red-600">
                {{ errores.telefono[0] }}
              </div>
            </div>
          </div>
        </div>

        <!-- VENDEDORES ASIGNADOS -->
        <div class="border-l-4 border-purple-500 pl-4" v-if="puedeAsignarVendedores">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-users mr-2 text-purple-500"></i>
            Vendedores Asignados
          </h3>
          
          <div class="space-y-3">
            <div 
              v-for="vendedor in vendedoresDisponibles" 
              :key="vendedor.id"
              class="flex items-center"
            >
              <input
                :id="`vendedor_${vendedor.id}`"
                v-model="formData.vendedores_a_cargo"
                :value="vendedor.id"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              >
              <label 
                :for="`vendedor_${vendedor.id}`" 
                class="ml-3 text-sm text-gray-700 cursor-pointer"
              >
                {{ vendedor.name }}
                <span class="text-gray-500">({{ vendedor.email }})</span>
              </label>
            </div>
            
            <div v-if="vendedoresDisponibles.length === 0" class="text-sm text-gray-500 italic">
              No hay vendedores disponibles para asignar
            </div>
          </div>
        </div>

        <!-- INFORMACIÓN ADICIONAL -->
        <div class="border-l-4 border-yellow-500 pl-4">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-sticky-note mr-2 text-yellow-500"></i>
            Información Adicional
          </h3>
          
          <div>
            <label for="informacion_adicional" class="block text-sm font-medium text-gray-700 mb-2">
              Notas y comentarios
            </label>
            <textarea
              id="informacion_adicional"
              v-model="formData.informacion_adicional"
              rows="4"
              maxlength="1000"
              class="form-textarea w-full"
              :class="errores.informacion_adicional ? 'border-red-500 focus:ring-red-500' : ''"
              placeholder="Información relevante sobre el cliente, historial, preferencias, etc."
            ></textarea>
            <div class="mt-1 text-xs text-gray-500">
              {{ (formData.informacion_adicional || '').length }}/1000 caracteres
            </div>
            <div v-if="errores.informacion_adicional" class="mt-1 text-sm text-red-600">
              {{ errores.informacion_adicional[0] }}
            </div>
          </div>
        </div>

        <!-- MENSAJE DE VALIDACIÓN ANTI-DUPLICADOS -->
        <div v-if="clienteDuplicado" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
          <div class="flex">
            <i class="fas fa-exclamation-triangle text-yellow-500 mr-3 mt-0.5"></i>
            <div>
              <h4 class="text-sm font-medium text-yellow-800">Posible cliente duplicado</h4>
              <p class="text-sm text-yellow-700 mt-1">
                {{ clienteDuplicado.mensaje }}
              </p>
              <div class="mt-2">
                <button
                  type="button"
                  @click="verClienteDuplicado"
                  class="text-sm text-yellow-800 underline hover:text-yellow-900"
                >
                  Ver cliente existente
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- BOTONES DE ACCIÓN -->
      <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-500">
            <i class="fas fa-save mr-1"></i>
            {{ modoEdicion ? 'Último guardado: ' + ultimoGuardado : 'Nuevo cliente' }}
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
              type="submit"
              class="btn-primary"
              :disabled="enviando || !formularioValido"
            >
              <i v-if="enviando" class="fas fa-spinner fa-spin mr-2"></i>
              <i v-else class="fas fa-check mr-2"></i>
              {{ modoEdicion ? 'Actualizar Cliente' : 'Crear Cliente' }}
            </button>
          </div>
        </div>
      </div>
    </form>

    <!-- MODAL DE CLIENTE DUPLICADO -->
    <div v-if="mostrarModalDuplicado" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-lg shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Cliente Existente</h3>
          
          <div v-if="clienteExistente" class="space-y-3">
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span class="font-medium text-gray-700">Institución:</span>
                <div>{{ clienteExistente.nombre_institucion }}</div>
              </div>
              <div>
                <span class="font-medium text-gray-700">RUT:</span>
                <div>{{ clienteExistente.rut }}</div>
              </div>
              <div>
                <span class="font-medium text-gray-700">Email:</span>
                <div>{{ clienteExistente.email }}</div>
              </div>
              <div>
                <span class="font-medium text-gray-700">Tipo:</span>
                <div>{{ clienteExistente.tipo_cliente }}</div>
              </div>
            </div>
            
            <div class="mt-4 p-3 bg-blue-50 rounded-lg">
              <p class="text-sm text-blue-800">
                <i class="fas fa-info-circle mr-1"></i>
                Este cliente ya existe en el sistema. ¿Deseas editarlo en lugar de crear uno nuevo?
              </p>
            </div>
          </div>
          
          <div class="flex gap-3 mt-6">
            <button
              @click="editarClienteExistente"
              class="btn-primary flex-1"
            >
              <i class="fas fa-edit mr-2"></i>
              Editar Existente
            </button>
            <button
              @click="continuarCreando"
              class="btn-outline flex-1"
            >
              <i class="fas fa-plus mr-2"></i>
              Crear Nuevo
            </button>
            <button
              @click="mostrarModalDuplicado = false"
              class="btn-secondary"
            >
              Cancelar
            </button>
          </div>
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
  name: 'ClienteForm',
  props: {
    initialCliente: {
      type: Object,
      default: () => null
    },
    vendedoresDisponibles: {
      type: Array,
      default: () => []
    },
    puedeAsignarVendedores: {
      type: Boolean,
      default: false
    }
  },
  
  data() {
    return {
      // Datos del formulario
      formData: {
        nombre_institucion: '',
        rut: '',
        tipo_cliente: '',
        vendedores_a_cargo: [],
        informacion_adicional: '',
        email: '',
        telefono: '',
        direccion: '',
        nombre_contacto: ''
      },
      
      // Estados del formulario
      enviando: false,
      modoEdicion: false,
      ultimoGuardado: null,
      
      // Validación
      errores: {},
      rutInvalido: false,
      clienteDuplicado: null,
      clienteExistente: null,
      mostrarModalDuplicado: false,
      
      // Notificaciones
      toast: {
        mostrar: false,
        tipo: '',
        mensaje: ''
      },
      
      // Timeouts para validación
      rutTimeout: null,
      emailTimeout: null
    }
  },
  
  computed: {
    formularioValido() {
      return this.formData.nombre_institucion.trim() !== '' &&
             this.formData.email.trim() !== '' &&
             !this.rutInvalido &&
             Object.keys(this.errores).length === 0;
    }
  },
  
  watch: {
    'formData.rut'() {
      this.rutInvalido = false;
      this.clienteDuplicado = null;
    },
    
    'formData.email'() {
      this.verificarEmailDuplicado();
    }
  },
  
  mounted() {
    this.initializeForm();
  },
  
  methods: {
    /**
     * INICIALIZAR FORMULARIO
     */
    initializeForm() {
      if (this.initialCliente) {
        this.modoEdicion = true;
        this.formData = { ...this.formData, ...this.initialCliente };
        this.ultimoGuardado = this.initialCliente.updated_at 
          ? new Date(this.initialCliente.updated_at).toLocaleString()
          : 'No disponible';
      }
      
      // Si es vendedor y no está en modo edición, auto-asignarse
      if (!this.modoEdicion && !this.puedeAsignarVendedores) {
        // Se asigna automáticamente en el backend
      }
    },
    
    /**
     * FORMATEO DE RUT
     */
    formatearRut() {
      let rut = this.formData.rut.replace(/[^0-9kK]/g, '');
      
      if (rut.length > 1) {
        const cuerpo = rut.slice(0, -1);
        const dv = rut.slice(-1);
        
        // Agregar puntos cada 3 dígitos
        const cuerpoFormateado = cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        this.formData.rut = `${cuerpoFormateado}-${dv}`;
      }
    },
    
    /**
     * VALIDAR RUT CHILENO
     */
    validarRut() {
      if (!this.formData.rut) {
        this.rutInvalido = false;
        return;
      }
      
      const rut = this.formData.rut.replace(/[^0-9kK]/g, '');
      
      if (rut.length < 8) {
        this.rutInvalido = true;
        return;
      }
      
      const cuerpo = rut.slice(0, -1);
      const dv = rut.slice(-1).toLowerCase();
      
      // Algoritmo de validación chileno
      let suma = 0;
      let multiplicador = 2;
      
      for (let i = cuerpo.length - 1; i >= 0; i--) {
        suma += parseInt(cuerpo[i]) * multiplicador;
        multiplicador = multiplicador === 7 ? 2 : multiplicador + 1;
      }
      
      const dvCalculado = 11 - (suma % 11);
      let dvEsperado;
      
      if (dvCalculado === 11) {
        dvEsperado = '0';
      } else if (dvCalculado === 10) {
        dvEsperado = 'k';
      } else {
        dvEsperado = dvCalculado.toString();
      }
      
      this.rutInvalido = dv !== dvEsperado;
      
      if (!this.rutInvalido) {
        this.verificarRutDuplicado();
      }
    },
    
    /**
     * FORMATEAR TELÉFONO
     */
    formatearTelefono() {
      let telefono = this.formData.telefono.replace(/[^0-9+]/g, '');
      
      // Formato básico para números chilenos
      if (telefono.startsWith('56') && telefono.length === 11) {
        this.formData.telefono = `+${telefono.slice(0, 2)} ${telefono.slice(2, 3)} ${telefono.slice(3, 7)} ${telefono.slice(7)}`;
      } else if (telefono.startsWith('9') && telefono.length === 9) {
        this.formData.telefono = `+56 ${telefono.slice(0, 1)} ${telefono.slice(1, 5)} ${telefono.slice(5)}`;
      }
    },
    
    /**
     * VERIFICAR RUT DUPLICADO
     */
    async verificarRutDuplicado() {
      if (!this.formData.rut || this.rutInvalido) return;
      
      try {
        const response = await fetch(`/api/clientes/verificar-rut?rut=${encodeURIComponent(this.formData.rut)}&exclude_id=${this.initialCliente?.id || ''}`);
        const data = await response.json();
        
        if (data.existe) {
          this.clienteDuplicado = {
            mensaje: `Ya existe un cliente con el RUT ${this.formData.rut}`,
            cliente: data.cliente
          };
          this.clienteExistente = data.cliente;
        }
        
      } catch (error) {
        console.error('Error al verificar RUT:', error);
      }
    },
    
    /**
     * VERIFICAR EMAIL DUPLICADO
     */
    verificarEmailDuplicado() {
      clearTimeout(this.emailTimeout);
      this.emailTimeout = setTimeout(async () => {
        if (!this.formData.email || !this.formData.email.includes('@')) return;
        
        try {
          const response = await fetch(`/api/clientes/verificar-email?email=${encodeURIComponent(this.formData.email)}&exclude_id=${this.initialCliente?.id || ''}`);
          const data = await response.json();
          
          if (data.existe && !this.clienteDuplicado) {
            this.clienteDuplicado = {
              mensaje: `Ya existe un cliente con el email ${this.formData.email}`,
              cliente: data.cliente
            };
            this.clienteExistente = data.cliente;
          }
          
        } catch (error) {
          console.error('Error al verificar email:', error);
        }
      }, 1000);
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
          ? `/clientes/${this.initialCliente.id}`
          : '/clientes';
          
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
          
          // Emitir evento de éxito
          this.$emit('cliente-guardado', data.data);
          
          // Redireccionar si no es modal
          if (!this.esModal) {
            setTimeout(() => {
              window.location.href = data.data.redirect_url || '/clientes';
            }, 1500);
          }
          
        } else {
          if (data.errors) {
            this.errores = data.errors;
          }
          this.mostrarToast('error', data.message || 'Error al guardar cliente');
        }
        
      } catch (error) {
        console.error('Error al enviar formulario:', error);
        this.mostrarToast('error', 'Error de conexión al guardar cliente');
      } finally {
        this.enviando = false;
      }
    },
    
    /**
     * GUARDAR BORRADOR
     */
    async guardarBorrador() {
      const borrador = {
        ...this.formData,
        es_borrador: true,
        timestamp: new Date().toISOString()
      };
      
      localStorage.setItem('cliente_borrador', JSON.stringify(borrador));
      this.mostrarToast('success', 'Borrador guardado localmente');
    },
    
    /**
     * CARGAR BORRADOR
     */
    cargarBorrador() {
      const borrador = localStorage.getItem('cliente_borrador');
      if (borrador) {
        const data = JSON.parse(borrador);
        
        if (confirm('¿Deseas cargar el borrador guardado anteriormente?')) {
          this.formData = { ...this.formData, ...data };
          delete this.formData.es_borrador;
          delete this.formData.timestamp;
          
          localStorage.removeItem('cliente_borrador');
          this.mostrarToast('success', 'Borrador cargado');
        }
      }
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
      
      if (!this.esModal) {
        window.history.back();
      }
    },
    
    /**
     * VERIFICAR SI EL FORMULARIO TIENE CAMBIOS
     */
    formularioTieneCambios() {
      if (!this.initialCliente) {
        return Object.values(this.formData).some(value => 
          value && (Array.isArray(value) ? value.length > 0 : value.toString().trim() !== '')
        );
      }
      
      return Object.keys(this.formData).some(key => {
        const valorActual = this.formData[key];
        const valorInicial = this.initialCliente[key];
        
        if (Array.isArray(valorActual) && Array.isArray(valorInicial)) {
          return JSON.stringify(valorActual.sort()) !== JSON.stringify(valorInicial.sort());
        }
        
        return valorActual !== valorInicial;
      });
    },
    
    /**
     * ACCIONES DEL MODAL DUPLICADO
     */
    verClienteDuplicado() {
      this.mostrarModalDuplicado = true;
    },
    
    editarClienteExistente() {
      this.$emit('editar-cliente', this.clienteExistente);
      this.mostrarModalDuplicado = false;
    },
    
    continuarCreando() {
      this.clienteDuplicado = null;
      this.mostrarModalDuplicado = false;
      this.mostrarToast('warning', 'Asegúrate de que realmente necesitas crear un cliente nuevo');
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
    // Cargar borrador si existe
    if (!this.modoEdicion) {
      this.cargarBorrador();
    }
  }
}
</script>

<style scoped>
/* Clases CSS personalizadas para Bioscom */
.cliente-form-container {
  @apply max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden;
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

/* Validación visual */
.form-input:invalid {
  @apply border-red-500 focus:ring-red-500;
}

.form-input:valid {
  @apply border-green-500 focus:ring-green-500;
}

/* Mejoras de UX */
.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  @apply transform scale-[1.01];
}

/* Responsive */
@media (max-width: 768px) {
  .cliente-form-container {
    @apply mx-4;
  }
  
  .grid.grid-cols-2 {
    @apply grid-cols-1;
  }
}
</style>