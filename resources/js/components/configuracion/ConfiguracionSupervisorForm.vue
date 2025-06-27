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
        { id: 'modo_oscuro', tipo: 'checkbox', label: 'Modo Oscuro' },
        { id: 'contraste_alto', tipo: 'checkbox', label: 'Contraste Alto' },
        { id: 'tamano_fuente', tipo: 'select', label: 'Tamaño de Fuente', opciones: ['pequeña', 'mediana', 'grande'], requerido: true },
        { id: 'activar_alertas', tipo: 'checkbox', label: 'Activar Alertas' },
        { id: 'orden_prioridad', tipo: 'texto', label: 'Orden de Prioridad' }
      ]
    }
  }
}
</script>