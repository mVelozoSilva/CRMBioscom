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
        { id: 'nombre', tipo: 'texto', label: 'Nombre', requerido: true },
        { id: 'descripcion', tipo: 'textarea', label: 'Descripción' },
        { id: 'fecha_inicio', tipo: 'date', label: 'Fecha de Inicio', requerido: true },
        { id: 'fecha_fin', tipo: 'date', label: 'Fecha de Fin', requerido: true }
      ]
    }
  }
}
</script>