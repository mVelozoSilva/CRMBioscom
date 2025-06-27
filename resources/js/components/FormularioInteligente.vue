<template>
  <form @submit.prevent>
    <div v-for="(campo, index) in estructura" :key="index" class="mb-4">
      <component
        :is="getTipoComponente(campo.tipo)"
        v-model="valores[campo.id]"
        :campo="campo"
        :error="errores[campo.id]"
        :disabled="disabled || campo.disabled"
        @input="emitirCambio(campo.id, $event)"
      />
    </div>
  </form>
</template>

<script>
import FormText from './campos/FormText.vue'
import FormTextarea from './campos/FormTextarea.vue'
import FormSelect from './campos/FormSelect.vue'
import FormCheckbox from './campos/FormCheckbox.vue'
import FormDate from './campos/FormDate.vue'
import FormEmail from './campos/FormEmail.vue'
import FormTelefono from './campos/FormTelefono.vue'

export default {
  name: 'FormularioInteligente',
  props: {
    estructura: { type: Array, required: true },
    valoresIniciales: { type: Object, default: () => ({}) },
    errores: { type: Object, default: () => ({}) },
    disabled: { type: Boolean, default: false }
  },
  emits: ['update:valores'],
  data() {
    return { valores: {} }
  },
  mounted() {
    this.inicializarValores()
  },
  methods: {
    inicializarValores() {
      this.estructura.forEach(campo => {
        this.valores[campo.id] = this.valoresIniciales[campo.id] ?? ''
      })
      this.$emit('update:valores', this.valores)
    },
    getTipoComponente(tipo) {
      switch (tipo) {
        case 'texto': return FormText
        case 'textarea': return FormTextarea
        case 'select': return FormSelect
        case 'checkbox': return FormCheckbox
        case 'fecha': return FormDate
        case 'email': return FormEmail
        case 'telefono': return FormTelefono
        default: return FormText
      }
    },
    emitirCambio(id, valor) {
      this.valores[id] = valor
      this.$emit('update:valores', { ...this.valores })
    }
  }
}
</script>
