<template>
  <div class="card-bioscom max-w-4xl mx-auto p-[var(--bioscom-space-lg)]">
    <!-- ENCABEZADO DEL FORMULARIO -->
    <div class="bg-white shadow-sm border-b border-bioscom-gray-200 px-6 py-4">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-bioscom-text-primary">
          {{ modoEdicion ? 'Editar Cliente' : 'Nuevo Cliente' }}
        </h2>
        <div class="text-sm text-bioscom-gray-500">
          <i class="fas fa-info-circle mr-[var(--bioscom-space-xs)]"></i>
          Los campos marcados con * son obligatorios
        </div>
      </div>
    </div>

    <!-- FORMULARIO PRINCIPAL -->
    <form @submit.prevent="enviarFormulario" class="bg-white">
      <div class="px-6 py-6 space-y-[var(--bioscom-space-lg)]">
        
        <!-- INFORMACIÓN BÁSICA DE LA INSTITUCIÓN -->
        <div class="border-l-4 border-bioscom-primary pl-[var(--bioscom-space-md)]">
          <h3 class="text-lg font-medium text-bioscom-text-primary mb-[var(--bioscom-space-md)]">
            <i class="fas fa-building mr-[var(--bioscom-space-xs)] text-bioscom-primary"></i>
            Información de la Institución
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-[var(--bioscom-space-lg)]">
            <!-- Nombre de la Institución -->
            <div class="md:col-span-2">
              <label for="nombre_institucion" class="block text-sm font-medium text-bioscom-gray-700 mb-[var(--bioscom-space-xs)]">
                Nombre de la Institución *
              </label>
              <input
                id="nombre_institucion"
                v-model="formData.nombre_institucion"
                type="text"
                required
                maxlength="255"
                class="input-bioscom w-full"
                :class="errores.nombre_institucion ? 'border-bioscom-error focus:ring-bioscom-error' : ''"
                placeholder="Ej: Hospital Regional de Santiago"
              >
              <div v-if="errores.nombre_institucion" class="mt-[var(--bioscom-space-xs)] text-sm text-bioscom-error">
                {{ errores.nombre_institucion[0] }}
              </div>
            </div>

            <!-- RUT -->
            <div>
              <label for="rut" class="block text-sm font-medium text-bioscom-gray-700 mb-[var(--bioscom-space-xs)]">
                RUT de la Institución
              </label>
              <input
                id="rut"
                v-model="formData.rut"
                type="text"
                maxlength="12"
                class="input-bioscom w-full"
                :class="errores.rut ? 'border-bioscom-error focus:ring-bioscom-error' : ''"
                placeholder="12.345.678-9"
                @input="formatearRut"
                @blur="validarRut"
              >
              <div v-if="errores.rut" class="mt-[var(--bioscom-space-xs)] text-sm text-bioscom-error">
                {{ errores.rut[0] }}
              </div>
              <div v-if="rutInvalido" class="mt-[var(--bioscom-space-xs)] text-sm text-bioscom-error">
                El RUT ingresado no es válido
              </div>
            </div>

            <!-- Tipo de Cliente -->
            <div>
              <label for="tipo_cliente" class="block text-sm font-medium text-bioscom-gray-700 mb-[var(--bioscom-space-xs)]">
                Tipo de Cliente
              </label>
              <select
                id="tipo_cliente"
                v-model="formData.tipo_cliente"
                class="input-bioscom w-full"
                :class="errores.tipo_cliente ? 'border-bioscom-error focus:ring-bioscom-error' : ''"
              >
                <option value="">Seleccionar tipo</option>
                <option value="Cliente Público">Cliente Público</option>
                <option value="Cliente Privado">Cliente Privado</option>
                <option value="Revendedor">Revendedor</option>
              </select>
              <div v-if="errores.tipo_cliente" class="mt-[var(--bioscom-space-xs)] text-sm text-bioscom-error">
                {{ errores.tipo_cliente[0] }}
              </div>
            </div>

            <!-- Dirección -->
            <div class="md:col-span-2">
              <label for="direccion" class="block text-sm font-medium text-bioscom-gray-700 mb-[var(--bioscom-space-xs)]">
                Dirección
              </label>
              <input
                id="direccion"
                v-model="formData.direccion"
                type="text"
                maxlength="255"
                class="input-bioscom w-full"
                :class="errores.direccion ? 'border-bioscom-error focus:ring-bioscom-error' : ''"
                placeholder="Ej: Av. Libertador Bernardo O'Higgins 1234, Santiago"
              >
              <div v-if="errores.direccion" class="mt-[var(--bioscom-space-xs)] text-sm text-bioscom-error">
                {{ errores.direccion[0] }}
              </div>
            </div>
          </div>
        </div>

        <!-- CONTACTO PRINCIPAL -->
        <div class="border-l-4 border-bioscom-success pl-[var(--bioscom-space-md)]">
          <h3 class="text-lg font-medium text-bioscom-text-primary mb-[var(--bioscom-space-md)]">
            <i class="fas fa-user mr-[var(--bioscom-space-xs)] text-bioscom-success"></i>
            Contacto Principal
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-[var(--bioscom-space-lg)]">
            <!-- Nombre del Contacto -->
            <div>
              <label for="nombre_contacto" class="block text-sm font-medium text-bioscom-gray-700 mb-[var(--bioscom-space-xs)]">
                Nombre del Contacto
              </label>
              <input
                id="nombre_contacto"
                v-model="formData.nombre_contacto"
                type="text"
                maxlength="255"
                class="input-bioscom w-full"
                :class="errores.nombre_contacto ? 'border-bioscom-error focus:ring-bioscom-error' : ''"
                placeholder="Ej: Dr. Juan Pérez"
              >
              <div v-if="errores.nombre_contacto" class="mt-[var(--bioscom-space-xs)] text-sm text-bioscom-error">
                {{ errores.nombre_contacto[0] }}
              </div>
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="block text-sm font-medium text-bioscom-gray-700 mb-[var(--bioscom-space-xs)]">
                Email *
              </label>
              <input
                id="email"
                v-model="formData.email"
                type="email"
                required
                maxlength="255"
                class="input-bioscom w-full"
                :class="errores.email ? 'border-bioscom-error focus:ring-bioscom-error' : ''"
                placeholder="contacto@hospital.cl"
                @blur="verificarEmailDuplicado"
              >
              <div v-if="errores.email" class="mt-[var(--bioscom-space-xs)] text-sm text-bioscom-error">
                {{ errores.email[0] }}
              </div>
            </div>

            <!-- Teléfono -->
            <div class="md:col-span-2">
              <label for="telefono" class="block text-sm font-medium text-bioscom-gray-700 mb-[var(--bioscom-space-xs)]">
                Teléfono
              </label>
              <input
                id="telefono"
                v-model="formData.telefono"
                type="tel"
                maxlength="20"
                class="input-bioscom w-full"
                :class="errores.telefono ? 'border-bioscom-error focus:ring-bioscom-error' : ''"
                placeholder="+56 9 1234 5678"
                @input="formatearTelefono"
              >
              <div v-if="errores.telefono" class="mt-[var(--bioscom-space-xs)] text-sm text-bioscom-error">
                {{ errores.telefono[0] }}
              </div>
            </div>
          </div>
        </div>

        <!-- INFORMACIÓN ADICIONAL -->
        <div class="border-l-4 border-bioscom-warning pl-[var(--bioscom-space-md)]">
          <h3 class="text-lg font-medium text-bioscom-text-primary mb-[var(--bioscom-space-md)]">
            <i class="fas fa-sticky-note mr-[var(--bioscom-space-xs)] text-bioscom-warning"></i>
            Información Adicional
          </h3>
          
          <div>
            <label for="informacion_adicional" class="block text-sm font-medium text-bioscom-gray-700 mb-[var(--bioscom-space-xs)]">
              Notas y comentarios
            </label>
            <textarea
              id="informacion_adicional"
              v-model="formData.informacion_adicional"
              rows="4"
              maxlength="1000"
              class="input-bioscom w-full"
              :class="errores.informacion_adicional ? 'border-bioscom-error focus:ring-bioscom-error' : ''"
              placeholder="Información relevante sobre el cliente, historial, preferencias, etc."
            ></textarea>
            <div class="mt-[var(--bioscom-space-xs)] text-xs text-bioscom-gray-500">
              {{ (formData.informacion_adicional || '').length }}/1000 caracteres
            </div>
            <div v-if="errores.informacion_adicional" class="mt-[var(--bioscom-space-xs)] text-sm text-bioscom-error">
              {{ errores.informacion_adicional[0] }}
            </div>
          </div>
        </div>

        <!-- MENSAJE DE VALIDACIÓN ANTI-DUPLICADOS -->
        <div v-if="clienteDuplicado" class="bg-bioscom-warning/10 border border-bioscom-warning/20 rounded-bioscom p-[var(--bioscom-space-md)]">
          <div class="flex">
            <i class="fas fa-exclamation-triangle text-bioscom-warning mr-[var(--bioscom-space-md)] mt-0.5"></i>
            <div>
              <h4 class="text-sm font-medium text-bioscom-warning">Posible cliente duplicado</h4>
              <p class="text-sm text-bioscom-warning mt-[var(--bioscom-space-xs)]">
                {{ clienteDuplicado.mensaje || 'Se encontró un cliente con datos similares' }}
              </p>
              <div class="mt-[var(--bioscom-space-sm)]">
                <button
                  type="button"
                  @click="editarClienteExistente"
                  class="text-sm text-bioscom-warning underline hover:text-bioscom-warning/80"
                >
                  Ver cliente existente
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- BOTONES DE ACCIÓN -->
      <div class="bg-bioscom-gray-50 px-6 py-4 border-t border-bioscom-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-bioscom-gray-500">
            <i class="fas fa-save mr-[var(--bioscom-space-xs)]"></i>
            {{ modoEdicion ? 'Último guardado: ' + ultimoGuardado : 'Nuevo cliente' }}
          </div>
          
          <div class="flex gap-[var(--bioscom-space-sm)]">
            <button
              type="button"
              @click="cancelar"
              class="btn-bioscom-outline"
              :disabled="enviando"
            >
              <i class="fas fa-times mr-[var(--bioscom-space-xs)]"></i>
              Cancelar
            </button>
            
            <button
              type="button"
              @click="guardarBorrador"
              class="btn-bioscom-outline"
              :disabled="enviando"
              v-if="!modoEdicion"
            >
              <i class="fas fa-save mr-[var(--bioscom-space-xs)]"></i>
              Guardar Borrador
            </button>
            
            <button
              type="submit"
              class="btn-bioscom-primary"
              :disabled="enviando || !formularioValido"
            >
              <i v-if="enviando" class="fas fa-spinner fa-spin mr-[var(--bioscom-space-xs)]"></i>
              <i v-else class="fas fa-check mr-[var(--bioscom-space-xs)]"></i>
              {{ modoEdicion ? 'Actualizar Cliente' : 'Crear Cliente' }}
            </button>
          </div>
        </div>
      </div>
    </form>

    <!-- TOAST NOTIFICATIONS -->
    <div 
      v-if="toast.mostrar"
      :class="[
        'fixed bottom-4 right-4 p-4 rounded-lg shadow-lg z-50 transition-all duration-300',
        toast.tipo === 'success' ? 'bg-bioscom-success/90 text-white' : '',
        toast.tipo === 'error' ? 'bg-bioscom-error/90 text-white' : '',
        toast.tipo === 'warning' ? 'bg-bioscom-warning/90 text-white' : ''
      ]"
    >
      <div class="flex items-center">
        <i :class="[
          'mr-[var(--bioscom-space-xs)]',
          toast.tipo === 'success' ? 'fas fa-check-circle' : '',
          toast.tipo === 'error' ? 'fas fa-exclamation-circle' : '',
          toast.tipo === 'warning' ? 'fas fa-exclamation-triangle' : ''
        ]"></i>
        <strong class="flex-grow">{{ toast.mensaje }}</strong>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

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
    console.log('🔍 Validando formulario...', {
      nombre: this.formData.nombre_institucion.trim() !== '',
      email: this.formData.email.trim() !== '',
      rutInvalido: this.rutInvalido,
      errores: Object.keys(this.errores).length
    });
    
    const valido = this.formData.nombre_institucion.trim() !== '' &&
                   this.formData.email.trim() !== '' &&
                   !this.rutInvalido &&
                   Object.keys(this.errores).length === 0;
    
    console.log('📝 Formulario válido:', valido);
    return valido;
  }
},
  
  watch: {
    'formData.rut'() {
      this.rutInvalido = false;
      this.clienteDuplicado = null;
      clearTimeout(this.rutTimeout);
      this.rutTimeout = setTimeout(() => {
        this.validarRut();
      }, 500);
    }
  },
  
  mounted() {
    console.log('🔥 ClienteForm montado correctamente');
    console.log('🔥 Métodos disponibles:', Object.keys(this.$options.methods || {}));
    
    if (this.enviarFormulario) {
      console.log('✅ Método enviarFormulario encontrado');
    } else {
      console.error('❌ Método enviarFormulario NO encontrado');
    }
    
    console.log('🔥 FormData inicial:', this.formData);
    
    this.initializeForm();
    if (!this.modoEdicion) {
      this.cargarBorrador();
    }
  },
  
  methods: {
    /**
     * INICIALIZAR FORMULARIO
     */
    initializeForm() {
      if (this.initialCliente) {
        this.modoEdicion = true;
        for (const key in this.formData) {
          if (Object.prototype.hasOwnProperty.call(this.initialCliente, key)) {
            this.formData[key] = this.initialCliente[key];
          }
        }
        
        if (typeof this.formData.vendedores_a_cargo === 'string') {
          try {
            this.formData.vendedores_a_cargo = JSON.parse(this.formData.vendedores_a_cargo);
          } catch (e) {
            console.error("Error al parsear vendedores_a_cargo:", e);
            this.formData.vendedores_a_cargo = [];
          }
        }
        this.formData.vendedores_a_cargo = this.formData.vendedores_a_cargo || [];

        this.ultimoGuardado = this.initialCliente.updated_at 
          ? new Date(this.initialCliente.updated_at).toLocaleString()
          : 'No disponible';
      }
    },
    
    /**
     * FORMATEAR RUT MIENTRAS SE ESCRIBE
     */
    formatearRut() {
      let rut = this.formData.rut.replace(/[^0-9kK]/g, '');
      
      if (rut.length > 1) {
        const cuerpo = rut.slice(0, -1);
        const dv = rut.slice(-1).toUpperCase();
        
        let cuerpoFormateado = '';
        for (let i = cuerpo.length; i > 0; i -= 3) {
          const inicio = Math.max(0, i - 3);
          const grupo = cuerpo.slice(inicio, i);
          cuerpoFormateado = grupo + (cuerpoFormateado ? '.' + cuerpoFormateado : '');
        }
        
        this.formData.rut = cuerpoFormateado + '-' + dv;
      } else {
        this.formData.rut = rut;
      }
      
      this.validarRut();
    },

    /**
 * VALIDAR RUT CHILENO - VERSIÓN CORREGIDA
 */
validarRut() {
  const rut = this.formData.rut.trim();
  
  console.log('🔍 Validando RUT:', rut); // Debug
  
  if (!rut) {
    this.rutInvalido = false;
    console.log('✅ RUT vacío - válido');
    return; // ← NO verificar duplicados si no hay RUT
  }
  
  const rutLimpio = rut.replace(/[^0-9kK]/g, '');
  console.log('🔧 RUT limpio:', rutLimpio);
  
  if (rutLimpio.length < 2) {
    this.rutInvalido = true;
    console.log('❌ RUT muy corto');
    return;
  }
  
  const cuerpo = rutLimpio.slice(0, -1);
  const dv = rutLimpio.slice(-1).toUpperCase();
  
  console.log('🔍 Cuerpo:', cuerpo, 'DV:', dv);
  
  if (!/^\d+$/.test(cuerpo)) {
    this.rutInvalido = true;
    console.log('❌ Cuerpo del RUT no son solo números');
    return;
  }
  
  // Calcular dígito verificador
  let suma = 0;
  let multiplicador = 2;
  
  for (let i = cuerpo.length - 1; i >= 0; i--) {
    suma += parseInt(cuerpo[i]) * multiplicador;
    multiplicador = multiplicador === 7 ? 2 : multiplicador + 1;
  }
  
  const dvCalculado = 11 - (suma % 11);
  const dvEsperado = dvCalculado === 11 ? '0' : dvCalculado === 10 ? 'K' : dvCalculado.toString();
  
  console.log('🧮 DV calculado:', dvEsperado, 'DV ingresado:', dv);
  
  this.rutInvalido = dv !== dvEsperado;
  
  if (this.rutInvalido) {
    console.log('❌ RUT inválido - DV no coincide');
  } else {
    console.log('✅ RUT válido - verificando duplicados...');
    this.verificarRutDuplicado();
  }
},

    /**
     * VERIFICAR RUT DUPLICADO
     */
    async verificarRutDuplicado() {
      if (!this.formData.rut || this.rutInvalido) return;
      
      try {
        const response = await axios.get('/crm-bioscom/public/api/clientes/verificar-rut', {
          params: { 
            rut: this.formData.rut,
            exclude_id: this.modoEdicion ? this.initialCliente?.id : null
          }
        });
        
        if (response.data.existe) {
          this.clienteDuplicado = { mensaje: 'Ya existe un cliente con este RUT' };
          this.clienteExistente = response.data.cliente;
          this.mostrarModalDuplicado = true;
        } else {
          this.clienteDuplicado = null;
          this.clienteExistente = null;
        }
      } catch (error) {
        console.error('Error al verificar RUT:', error);
      }
    },
    
    /**
     * FORMATEAR TELÉFONO
     */
    formatearTelefono() {
      let telefono = this.formData.telefono.replace(/[^0-9+]/g, '');
      
      if (telefono.startsWith('56') && telefono.length === 11) {
        this.formData.telefono = `+${telefono.slice(0, 2)} ${telefono.slice(2, 3)} ${telefono.slice(3, 7)} ${telefono.slice(7)}`;
      } else if (telefono.startsWith('9') && telefono.length === 9) {
        this.formData.telefono = `+56 ${telefono.slice(0, 1)} ${telefono.slice(1, 5)} ${telefono.slice(5)}`;
      }
    },
    
    /**
     * VERIFICAR EMAIL DUPLICADO
     */
    async verificarEmailDuplicado() {
      if (!this.formData.email || !this.formData.email.includes('@')) return;
      
      try {
        const response = await axios.get('/crm-bioscom/public/api/clientes/verificar-email', {
          params: { 
            email: this.formData.email,
            exclude_id: this.modoEdicion ? this.initialCliente?.id : null
          }
        });
        
        if (response.data.existe) {
          this.clienteDuplicado = { mensaje: `Ya existe un cliente con el email ${this.formData.email}` };
          this.clienteExistente = response.data.cliente;
          this.mostrarModalDuplicado = true;
        } else if (!response.data.existe && this.clienteDuplicado?.mensaje?.includes(this.formData.email)) {
          this.clienteDuplicado = null;
          this.clienteExistente = null;
          this.mostrarModalDuplicado = false;
        }
      } catch (error) {
        console.error('Error al verificar email:', error);
      }
    },
    
    /**
     * ENVIAR FORMULARIO
     */
    async enviarFormulario() {
      console.log('🔥 Método enviarFormulario ejecutado');
      
      if (!this.formularioValido) {
        this.mostrarToast('warning', 'Por favor completa todos los campos obligatorios.');
        return;
      }
      
      this.enviando = true;
      this.errores = {};
      
      try {
        const url = '/crm-bioscom/public/clientes';
        console.log('🚀 Enviando a:', url);
        
        const formData = {
          nombre_institucion: this.formData.nombre_institucion,
          rut: this.formData.rut,
          tipo_cliente: this.formData.tipo_cliente,
          email: this.formData.email,
          telefono: this.formData.telefono || '',
          direccion: this.formData.direccion || '',
          nombre_contacto: this.formData.nombre_contacto || '',
          informacion_adicional: this.formData.informacion_adicional || ''
        };
        
        console.log('📦 Datos a enviar:', formData);
        
        const response = await axios.post(url, formData, {
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        
        console.log('✅ Respuesta recibida:', response.data);
        
        if (response.data.success) {
          this.mostrarToast('success', response.data.message || 'Cliente guardado exitosamente');
          
          setTimeout(() => {
            window.location.href = '/crm-bioscom/public/clientes';
          }, 1500);
        } else {
          this.mostrarToast('error', response.data.message || 'Error al guardar cliente');
        }
        
      } catch (error) {
        console.error('❌ Error:', error);
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
        this.mostrarConfirmacionBorrador(JSON.parse(borrador));
      }
    },
    
    mostrarConfirmacionBorrador(data) {
      if (confirm('¿Deseas cargar el borrador guardado anteriormente?')) {
        this.formData = { ...this.formData, ...data };
        delete this.formData.es_borrador;
        delete this.formData.timestamp;
        localStorage.removeItem('cliente_borrador');
        this.mostrarToast('success', 'Borrador cargado');
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
     * EDITAR CLIENTE EXISTENTE
     */
    editarClienteExistente() {
      if (this.clienteExistente && this.clienteExistente.id) {
        window.location.href = `/clientes/${this.clienteExistente.id}/edit`;
      } else {
        this.mostrarToast('error', 'No se encontró un cliente existente para editar.');
      }
      this.mostrarModalDuplicado = false;
    },
    
    /**
     * CONTINUAR CREANDO
     */
    continuarCreando() {
      this.clienteDuplicado = null;
      this.clienteExistente = null;
      this.mostrarModalDuplicado = false;
      this.mostrarToast('warning', 'Asegúrate de que realmente necesitas crear un cliente nuevo.');
    },
    
    /**
     * NOTIFICACIONES TOAST
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
  }
}
</script>