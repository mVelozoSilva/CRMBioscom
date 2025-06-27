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
        { id: 'nombre', tipo: 'texto', label: 'Nombre del formulario', requerido: true },
        { id: 'descripcion', tipo: 'textarea', label: 'Descripción' }
      ]
    }
  }
}
</script>