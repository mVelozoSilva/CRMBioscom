<template>
  <div v-if="visible" class="modal-overlay">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="heading-bioscom-3">Programar Gestión</h3>
        <button class="btn-close" @click="$emit('cerrar')">✖</button>
      </div>

      <!-- 🧠 Acciones rápidas -->
      <div class="grid grid-cols-3 gap-3 mb-4">
        <button class="btn-bioscom-blue" @click="seleccionarAccion('Seguimiento')">📍 Seguimiento</button>
        <button class="btn-bioscom-purple" @click="seleccionarAccion('Documento')">📎 Documento</button>
        <button class="btn-bioscom-orange" @click="seleccionarAccion('Factura')">🧾 Factura</button>
      </div>

      <form @submit.prevent="guardar">
        <div class="form-group mb-3">
          <label class="form-label-bioscom">Fecha de gestión *</label>
          <input type="date" v-model="tarea.fecha" class="input-bioscom" required />
        </div>

        <div class="form-group mb-3">
          <label class="form-label-bioscom">Medio de contacto *</label>
          <select v-model="tarea.medio" class="input-bioscom" required>
            <option disabled value="">Seleccione una opción</option>
            <option value="email">Correo</option>
            <option value="teléfono">Llamada</option>
            <option value="whatsapp">WhatsApp</option>
          </select>
        </div>

        <div class="form-group mb-4">
          <label class="form-label-bioscom">Notas</label>
          <textarea v-model="tarea.notas" rows="3" class="input-bioscom"></textarea>
        </div>

        <div class="text-right">
          <button class="btn-bioscom-primary" type="submit">💾 Guardar Tarea</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TareaProgramadaModal',
  props: {
    visible: Boolean,
    cobranza: Object
  },
  data() {
    return {
      tarea: {
        fecha: '',
        medio: 'email',
        notas: ''
      }
    };
  },
  methods: {
    seleccionarAccion(accion) {
      const nombre = this.cobranza?.cliente?.nombre_institucion || 'cliente';
      this.tarea.notas = `${accion} a ${nombre}`;
    },
    guardar() {
      this.$emit('guardar-tarea', {
        cobranza_id: this.cobranza?.id,
        ...this.tarea
      });
      this.$emit('cerrar');
      window.mostrarToast('success', 'Tarea programada ✅');
    }
  },
  watch: {
    visible(nueva) {
      if (nueva) {
        this.tarea = { fecha: '', medio: 'email', notas: '' };
      }
    }
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
.modal-content {
  background: white;
  padding: 2rem;
  width: 100%;
  max-width: 500px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}
.btn-bioscom-blue {
  @apply bg-blue-100 text-blue-800 font-medium px-3 py-2 rounded hover:bg-blue-200;
}
.btn-bioscom-purple {
  @apply bg-purple-100 text-purple-800 font-medium px-3 py-2 rounded hover:bg-purple-200;
}
.btn-bioscom-orange {
  @apply bg-orange-100 text-orange-800 font-medium px-3 py-2 rounded hover:bg-orange-200;
}
</style>
