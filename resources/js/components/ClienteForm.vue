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
          <h3 class="text-lg font-medium text-bioscom-text-primary mb-4">Información del Cliente</h3>

          <FormularioInteligente
            :estructura="estructuraFormulario"
            :valores-iniciales="cliente"
            :errores="errores"
            @update:valores="actualizarValores"
          />
        </div>

        <!-- BOTÓN DE ENVÍO -->
        <div class="flex justify-end pt-6">
          <button type="submit" class="btn-bioscom-primary">
            {{ modoEdicion ? 'Actualizar' : 'Guardar' }}
          </button>
        </div>
      </div>
      <p v-if="mensajeExito" class="text-green-600 text-sm font-medium bg-green-100 border border-green-300 p-3 rounded">
      {{ mensajeExito }}
    </p>

    </form>
  </div>
</template>

<script>
import FormularioInteligente from './FormularioInteligente.vue'

export default {
  name: 'ClienteForm',
  components: { FormularioInteligente },
  props: {
    initialCliente: {
      type: Object,
      default: () => ({})
    },
    modoEdicion: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      cliente: { ...this.initialCliente },
      errores: {},
      estructuraFormulario: [
        { id: 'nombre_institucion', tipo: 'texto', label: 'Nombre Institución', requerido: true },
        { id: 'rut', tipo: 'texto', label: 'RUT', requerido: false },
        { id: 'tipo_cliente', tipo: 'select', label: 'Tipo Cliente', requerido: true,
          opciones: ['Cliente Público', 'Cliente Privado', 'Revendedor'] },
        { id: 'nombre_contacto', tipo: 'texto', label: 'Nombre del Contacto', requerido: false },
        { id: 'email', tipo: 'email', label: 'Correo Electrónico', requerido: true },
        { id: 'telefono', tipo: 'telefono', label: 'Teléfono de contacto' },
        { id: 'direccion', tipo: 'texto', label: 'Dirección' },
        { id: 'informacion_adicional', tipo: 'textarea', label: 'Información Adicional' }
      ],
      mensajeExito: 'Información del cliente guardada correctamente.',
    }
  },
  methods: {
    actualizarValores(nuevosValores) {
      this.cliente = { ...nuevosValores }
    },
    enviarFormulario() {
  axios.post('/crm-bioscom/public/clientes', this.cliente)

    .then(() => {
      alert('Cliente guardado correctamente')
      window.location.href = '/crm-bioscom/public/clientes'
    })
    .catch(error => {
      console.error('Error al guardar:', error)
      if (error.response?.data?.errors) {
        this.errores = error.response.data.errors
      }
    })
},

    redireccionarDespuesDeGuardar() {
  // Puedes redirigir a la vista de detalle o a la lista
  this.$router.push({ name: 'clientes.index' }) // Asegúrate que esta ruta exista
  }

  }
}
</script>

<style scoped>
</style>
