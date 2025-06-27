<template>
  <div class="flex items-center">
    <input
      type="checkbox"
      :id="campo.id"
      v-model="valor"
      :disabled="disabled"
      class="rounded border-gray-300 text-bioscom-primary shadow-sm focus:ring focus:ring-blue-200"
      @change="$emit('input', valor)"
    />
    <label :for="campo.id" class="ml-2 text-sm text-gray-700">{{ campo.label }}</label>
  </div>
  <p v-if="error" class="text-red-600 text-sm mt-1">{{ error }}</p>
</template>

<script>
export default {
  props: {
    campo: Object,
    error: String,
    disabled: Boolean,
    modelValue: Boolean
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