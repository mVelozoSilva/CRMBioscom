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
        { id: 'estado', tipo: 'select', label: 'Estado', opciones: ['pendiente', 'en curso', 'finalizado'], requerido: true },
        { id: 'descripcion', tipo: 'textarea', label: 'Descripción' }
      ]
    }
  }
}
</script>