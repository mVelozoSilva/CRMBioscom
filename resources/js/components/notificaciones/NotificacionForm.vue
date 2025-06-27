<template>
  <FormularioInteligente :estructura="estructura" :valores-iniciales="valores" />
</template>

<script>
import FormularioInteligente from '../FormularioInteligente.vue'

export default {
  components: { FormularioInteligente },
  data() {
    return {
      valores: {},
      estructura: [
        { id: 'titulo', tipo: 'texto', label: 'Título', requerido: true },
        { id: 'mensaje', tipo: 'textarea', label: 'Mensaje', requerido: true },
        { id: 'tipo', tipo: 'texto', label: 'Tipo de Alerta' },
        { id: 'urgente', tipo: 'checkbox', label: '¿Es urgente?' }
      ]
    }
  }
}
</script>