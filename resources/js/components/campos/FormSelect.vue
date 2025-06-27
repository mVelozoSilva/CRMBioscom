<template>
  <div>
    <label :for="campo.id" class="block font-medium text-sm text-gray-700">{{ campo.label }}</label>
    <select
      :id="campo.id"
      v-model="valor"
      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"
      :disabled="disabled"
      @change="$emit('input', valor)"
    >
      <option value="">Seleccione...</option>
      <option v-for="opcion in campo.opciones" :key="opcion" :value="opcion">
        {{ opcion }}
      </option>
    </select>
    <p v-if="error" class="text-red-600 text-sm mt-1">{{ error }}</p>
  </div>
</template>

<script>
export default {
  props: {
    campo: Object,
    error: String,
    disabled: Boolean,
    modelValue: String
  },
  emits: ['input'],
  data() {
    return {
      valor: this.modelValue
    }
  },
  watch: {
    modelValue(val) {
      this.valor = val
    }
  }
}
</script>
