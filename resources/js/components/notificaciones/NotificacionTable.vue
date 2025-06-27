<template>
  <div class="space-y-2">
    <h2 class="text-xl font-bold">Listado de Notificaciones</h2>
    <ul>
      <li v-for="(n, i) in notificaciones" :key="i">
        <span :class="{ 'font-bold text-red-600': n.urgente }">{{ n.titulo }}</span> - {{ n.mensaje }}
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'NotificacionTable',
  data() {
    return {
      notificaciones: [
        { titulo: 'Prueba', mensaje: 'Alerta de prueba', urgente: false }
      ]
    }
  }
}
</script>