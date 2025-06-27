// 📁 UserDisplayPreferences.vue — Preferencias visuales del usuario
<template>
  <div class="card-bioscom p-6 max-w-xl mx-auto">
    <h2 class="heading-bioscom-2 mb-4">Preferencias de Accesibilidad</h2>

    <div class="form-group mb-4">
      <label class="form-label-bioscom">
        <input type="checkbox" v-model="prefs.altoContraste" /> Alto Contraste
      </label>
    </div>

    <div class="form-group mb-4">
      <label class="form-label-bioscom">
        <input type="checkbox" v-model="prefs.reducirAnimaciones" /> Reducir Animaciones
      </label>
    </div>

    <div class="form-group mb-4">
      <label class="form-label-bioscom">
        <input type="checkbox" v-model="prefs.tipoDislexia" /> Fuente para Dislexia (OpenDyslexic)
      </label>
    </div>

    <div class="form-group mb-4">
      <label class="form-label-bioscom">
        <input type="checkbox" v-model="prefs.textoGrande" /> Aumentar tamaño de texto
      </label>
    </div>

    <div class="form-group mb-4">
      <label class="form-label-bioscom">
        <input type="checkbox" v-model="prefs.modoOscuro" /> Modo Oscuro Accesible
      </label>
    </div>

    <button class="btn-bioscom-primary" @click="guardarPreferencias">Guardar preferencias</button>
  </div>
</template>

<script>
export default {
  name: 'UserDisplayPreferences',
  data() {
    return {
      prefs: {
        altoContraste: false,
        reducirAnimaciones: false,
        tipoDislexia: false,
        textoGrande: false,
        modoOscuro: false
      }
    };
  },
  mounted() {
    const saved = localStorage.getItem('bioscom_accesibilidad');
    if (saved) {
      this.prefs = JSON.parse(saved);
      this.aplicarPreferencias();
    }
  },
  methods: {
    guardarPreferencias() {
      localStorage.setItem('bioscom_accesibilidad', JSON.stringify(this.prefs));
      this.aplicarPreferencias();
      window.mostrarToast('success', 'Preferencias aplicadas ✅');
    },
    aplicarPreferencias() {
      const root = document.documentElement;
      const app = document.getElementById('app');
        ['modo-contraste', 'sin-animaciones', 'fuente-dislexia', 'texto-grande', 'modo-oscuro'].forEach(clase => {
        root.classList.remove(clase);
        app?.classList.remove(clase);
        });

        if (this.prefs.altoContraste) root.classList.add('modo-contraste');
        if (this.prefs.reducirAnimaciones) root.classList.add('sin-animaciones');
        if (this.prefs.tipoDislexia) root.classList.add('fuente-dislexia');
        if (this.prefs.textoGrande) root.classList.add('texto-grande');
        if (this.prefs.modoOscuro) root.classList.add('modo-oscuro');

      root.classList.toggle('modo-contraste', this.prefs.altoContraste);
      root.classList.toggle('sin-animaciones', this.prefs.reducirAnimaciones);
      root.classList.toggle('fuente-dislexia', this.prefs.tipoDislexia);
      root.classList.toggle('texto-grande', this.prefs.textoGrande);
      root.classList.toggle('modo-oscuro', this.prefs.modoOscuro);
    }
  }
};
</script>

<style scoped>
.card-bioscom input[type='checkbox'] {
  margin-right: 8px;
}
</style>
