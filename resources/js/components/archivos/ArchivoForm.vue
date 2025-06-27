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
        { id: 'archivo', tipo: 'file', label: 'Seleccionar archivo', requerido: true }
      ]
    }
  }
}
</script>