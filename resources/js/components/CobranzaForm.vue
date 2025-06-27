// 📁 CobranzaForm.vue — Con validaciones visuales en tiempo real
<template>
  <transition name="slide">
    <div v-if="visible" class="panel-lateral">
      <div class="panel-header">
        <h3 class="heading-bioscom-3">{{ esEdicion ? 'Editar Cobranza' : 'Nueva Cobranza' }}</h3>
        <button class="btn-close" @click="$emit('cerrar')">✖</button>
      </div>

      <form @submit.prevent="guardar">
        <div class="form-group mb-3" :class="{ 'has-error': errores.id_factura }">
          <label class="form-label-bioscom">Factura *</label>
          <input type="text" v-model="form.id_factura" class="input-bioscom" />
          <div v-if="errores.id_factura" class="text-error">{{ errores.id_factura }}</div>
        </div>

        <div class="form-group mb-3" :class="{ 'has-error': errores.cliente_id }">
          <label class="form-label-bioscom">Cliente ID *</label>
          <input type="number" v-model.number="form.cliente_id" class="input-bioscom" />
          <div v-if="errores.cliente_id" class="text-error">{{ errores.cliente_id }}</div>
        </div>

        <div class="form-group mb-3" :class="{ 'has-error': errores.monto_adeudado }">
          <label class="form-label-bioscom">Monto Adeudado *</label>
          <input type="number" v-model.number="form.monto_adeudado" class="input-bioscom" />
          <div v-if="errores.monto_adeudado" class="text-error">{{ errores.monto_adeudado }}</div>
        </div>

        <div class="form-group mb-3" :class="{ 'has-error': errores.fecha_emision }">
          <label class="form-label-bioscom">Fecha Emisión *</label>
          <input type="date" v-model="form.fecha_emision" class="input-bioscom" />
          <div v-if="errores.fecha_emision" class="text-error">{{ errores.fecha_emision }}</div>
        </div>

        <div class="form-group mb-3" :class="{ 'has-error': errores.fecha_vencimiento }">
          <label class="form-label-bioscom">Fecha Vencimiento *</label>
          <input type="date" v-model="form.fecha_vencimiento" class="input-bioscom" />
          <div v-if="errores.fecha_vencimiento" class="text-error">{{ errores.fecha_vencimiento }}</div>
        </div>

        <div class="form-group mb-3">
          <label class="form-label-bioscom">Estado *</label>
          <select v-model="form.estado" class="input-bioscom">
            <option v-for="estado in estados" :key="estado" :value="estado">{{ estado }}</option>
          </select>
        </div>

        <div class="form-group mt-4">
          <button class="btn-bioscom-primary w-full" type="submit">
            {{ esEdicion ? 'Actualizar' : 'Crear' }} Cobranza
          </button>
        </div>
      </form>
    </div>
  </transition>
</template>

<script>
import axios from 'axios';
export default {
  name: 'CobranzaForm',
  props: {
    visible: Boolean,
    cobranza: Object // Puede ser null para nuevo
  },
  data() {
    return {
      form: {
        id_factura: '',
        cliente_id: '',
        monto_adeudado: '',
        fecha_emision: '',
        fecha_vencimiento: '',
        estado: 'pendiente'
      },
      errores: {},
      estados: [
        'pendiente', 'en_gestion', 'pagada', 'vencida', 'parcialmente_pagada',
        'en_disputa', 'incobrable', 'renegociada'
      ]
    }
  },
  computed: {
    esEdicion() {
      return this.cobranza !== null && typeof this.cobranza === 'object';
    }
  },
  watch: {
    cobranza: {
      immediate: true,
      handler(nueva) {
        if (nueva) {
          this.form = { ...nueva };
        } else {
          this.form = {
            id_factura: '',
            cliente_id: '',
            monto_adeudado: '',
            fecha_emision: '',
            fecha_vencimiento: '',
            estado: 'pendiente'
          };
        }
        this.errores = {};
      }
    }
  },
  methods: {
    validar() {
      this.errores = {};
      if (!this.form.id_factura) this.errores.id_factura = 'Este campo es obligatorio';
      if (!this.form.cliente_id) this.errores.cliente_id = 'Este campo es obligatorio';
      if (!this.form.monto_adeudado) this.errores.monto_adeudado = 'Este campo es obligatorio';
      if (!this.form.fecha_emision) this.errores.fecha_emision = 'Este campo es obligatorio';
      if (!this.form.fecha_vencimiento) this.errores.fecha_vencimiento = 'Este campo es obligatorio';

      return Object.keys(this.errores).length === 0;
    },
    guardar() {
      if (!this.validar()) return;

      const url = this.esEdicion
        ? `/crm-bioscom/public/api/cobranzas/${this.cobranza.id}`
        : '/crm-bioscom/public/api/cobranzas';

      const metodo = this.esEdicion ? 'put' : 'post';

      axios[metodo](url, this.form)
        .then(() => {
          this.$emit('guardado');
          this.$emit('cerrar');
          window.mostrarToast('success', `Cobranza ${this.esEdicion ? 'actualizada' : 'creada'} ✅`);
        })
        .catch(err => {
          console.error(err);
          window.mostrarToast('error', 'Error al guardar la cobranza');
        });
    }
  }
};
</script>

<style scoped>
.panel-lateral {
  position: fixed;
  top: 0;
  right: 0;
  width: 400px;
  height: 100%;
  background: #fff;
  box-shadow: -2px 0 6px rgba(0, 0, 0, 0.1);
  z-index: 9999;
  padding: 24px;
  overflow-y: auto;
}
.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}
.slide-enter-active, .slide-leave-active {
  transition: transform 0.3s ease;
}
.slide-enter-from, .slide-leave-to {
  transform: translateX(100%);
}
.text-error {
  color: #d33;
  font-size: 0.85em;
  margin-top: 4px;
}
.has-error input,
.has-error select {
  border-color: #d33 !important;
  background-color: #fff6f6;
}
</style>
