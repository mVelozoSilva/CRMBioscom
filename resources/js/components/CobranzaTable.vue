<template>
  <div class="card-bioscom p-4">
    
    <!-- Resumen de métricas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="card-bioscom p-4 text-center">
        <div class="text-bioscom-caption mb-1">Total Deuda</div>
        <div class="text-2xl font-bold">${{ totalDeuda.toLocaleString() }}</div>
      </div>
      <div class="card-bioscom p-4 text-center">
        <div class="text-bioscom-caption mb-1">Facturas Vencidas</div>
        <div class="text-bioscom-danger font-bold">{{ vencidas.length }}</div>
      </div>
      <div class="card-bioscom p-4 text-center">
        <div class="text-bioscom-caption mb-1">Próximas a Vencer</div>
        <div class="text-bioscom-warning font-bold">{{ proximas.length }}</div>
      </div>
      <div class="card-bioscom p-4 text-center">
        <div class="text-bioscom-caption mb-1">Gestiones Pendientes</div>
        <div class="text-bioscom-info font-bold">{{ pendientes.length }}</div>
      </div>
    </div>

    <!-- Tabla con filtros estilo Excel -->
    <table class="table-bioscom w-full relative">
      <thead>
        <tr>
          <th class="w-6"></th>
          <th class="relative">
            <div class="flex items-center">
              Factura
              <button @click.stop="toggleFilter('factura')" class="ml-1">🔽</button>
            </div>
            <div v-if="filters.factura.open" class="filter-dropdown">
              <input
                v-model="filters.factura.search"
                type="text"
                placeholder="Buscar factura..."
                class="input-bioscom mb-2 w-full"
              />
              <div class="max-h-40 overflow-y-auto">
                <label
                  v-for="opt in filteredOptions('factura')"
                  :key="opt"
                  class="flex items-center mb-1"
                >
                  <input
                    type="checkbox"
                    v-model="filters.factura.selected"
                    :value="opt"
                    class="mr-2"
                  />
                  {{ opt }}
                </label>
              </div>
              <div class="mt-2 flex justify-between">
                <button class="text-sm text-bioscom-info" @click="clearFilter('factura')">Limpiar</button>
                <button class="text-sm text-bioscom-primary" @click="applyFilter('factura')">Aplicar</button>
              </div>
            </div>
          </th>
          <th class="relative">
            <div class="flex items-center">
              Cliente
              <button @click.stop="toggleFilter('cliente')" class="ml-1">🔽</button>
            </div>
            <div v-if="filters.cliente.open" class="filter-dropdown">
              <input
                v-model="filters.cliente.search"
                type="text"
                placeholder="Buscar cliente..."
                class="input-bioscom mb-2 w-full"
              />
              <div class="max-h-40 overflow-y-auto">
                <label
                  v-for="opt in filteredOptions('cliente')"
                  :key="opt"
                  class="flex items-center mb-1"
                >
                  <input
                    type="checkbox"
                    v-model="filters.cliente.selected"
                    :value="opt"
                    class="mr-2"
                  />
                  {{ opt }}
                </label>
              </div>
              <div class="mt-2 flex justify-between">
                <button class="text-sm text-bioscom-info" @click="clearFilter('cliente')">Limpiar</button>
                <button class="text-sm text-bioscom-primary" @click="applyFilter('cliente')">Aplicar</button>
              </div>
            </div>
          </th>
          <th class="w-1/6">Monto</th>
          <th class="relative w-1/6">
            <div class="flex items-center">
              Estado
              <button @click.stop="toggleFilter('estado')" class="ml-1">🔽</button>
            </div>
            <div v-if="filters.estado.open" class="filter-dropdown">
              <div class="max-h-40 overflow-y-auto">
                <label
                  v-for="opt in filteredOptions('estado')"
                  :key="opt"
                  class="flex items-center mb-1"
                >
                  <input
                    type="checkbox"
                    v-model="filters.estado.selected"
                    :value="opt"
                    class="mr-2"
                  />
                  {{ opt }}
                </label>
              </div>
              <div class="mt-2 flex justify-between">
                <button class="text-sm text-bioscom-info" @click="clearFilter('estado')">Limpiar</button>
                <button class="text-sm text-bioscom-primary" @click="applyFilter('estado')">Aplicar</button>
              </div>
            </div>
          </th>
          <th class="w-36">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="c in cobranzasFiltradas"
          :key="c.id"
          class="hover:bg-bioscom-gray-50"
        >
          <td><input type="checkbox" v-model="seleccionadas" :value="c.id" /></td>
          <td>{{ c.id_factura }}</td>
          <td>{{ c.cliente?.nombre_institucion }}</td>
          <td>${{ parseFloat(c.monto_adeudado).toLocaleString() }}</td>
          <td>
            <span :class="`badge-bioscom-${normalizarEstado(c.estado)}`">
              {{ c.estado }}
            </span>
          </td>
          <td class="flex gap-2">
            <button  @click="verDetalle(c)">👁</button>
            <button  @click="programarTarea(c)">📅</button>
            <button  @click="abrirFormulario(c)">✏️</button>
            <button @click="eliminarCobranza(c.id)">🗑</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Paginación -->
    <div class="mt-4 flex justify-end items-center">
      <button
        class="btn-bioscom-outline mr-2"
        :disabled="currentPage === 1"
        @click="currentPage--"
      >Anterior</button>
      <span class="px-3">Página {{ currentPage }} de {{ totalPages }}</span>
      <button
        class="btn-bioscom-outline ml-2"
        :disabled="currentPage === totalPages"
        @click="currentPage++"
      >Siguiente</button>
    </div>

    <!-- Teleport de modales fuera del contenedor -->
    <teleport to="body">
      <CobranzaForm
        :visible="formVisible"
        :cobranza="cobranzaEnEdicion"
        @cerrar="formVisible = false"
        @guardado="cargarCobranzas"
      />
      <TareaProgramadaModal
        :visible="modalTareaVisible"
        :cobranza="cobranzaParaTarea"
        @cerrar="modalTareaVisible = false"
        @tarea-guardada="cargarCobranzas"
      />
    </teleport>
  </div>
</template>

<script>
import axios from 'axios';
import CobranzaForm from './CobranzaForm.vue';
import TareaProgramadaModal from './TareaProgramadaModal.vue';

export default {
  name: 'CobranzaTable',
  components: { CobranzaForm, TareaProgramadaModal },
  data() {
    return {
      cobranzas: [],
      seleccionadas: [],
      filters: {
        factura: { open: false, search: '', selected: [] },
        cliente: { open: false, search: '', selected: [] },
        estado: { open: false, selected: [] }
      },
      formVisible: false,
      cobranzaEnEdicion: null,
      modalTareaVisible: false,
      cobranzaParaTarea: null,
      currentPage: 1,
      perPage: 20
    };
  },
  computed: {
    totalDeuda() {
      return this.cobranzas.reduce((sum, c) => sum + (parseFloat(c.monto_adeudado) || 0), 0);
    },
    vencidas() {
      const hoy = new Date();
      return this.cobranzas.filter(c => new Date(c.fecha_vencimiento) < hoy && c.estado !== 'pagada');
    },
    proximas() {
      const hoy = new Date();
      const enTres = new Date(); enTres.setDate(hoy.getDate() + 3);
      return this.cobranzas.filter(c => {
        const f = new Date(c.fecha_vencimiento);
        return f >= hoy && f <= enTres && c.estado !== 'pagada';
      });
    },
    pendientes() {
      return this.cobranzas.filter(c => ['pendiente', 'en_gestion'].includes(c.estado));
    },
    uniqueOptions() {
      return {
        factura: [...new Set(this.cobranzas.map(c => c.id_factura))],
        cliente: [...new Set(this.cobranzas.map(c => c.cliente?.nombre_institucion))],
        estado: [...new Set(this.cobranzas.map(c => c.estado))]
      };
    },
    cobranzasFiltradas() {
      let data = this.cobranzas;
      const f = this.filters;
      if (f.factura.selected.length) data = data.filter(c => f.factura.selected.includes(c.id_factura));
      if (f.cliente.selected.length) data = data.filter(c => f.cliente.selected.includes(c.cliente?.nombre_institucion));
      if (f.estado.selected.length) data = data.filter(c => f.estado.selected.includes(c.estado));
      const start = (this.currentPage - 1) * this.perPage;
      return data.slice(start, start + this.perPage);
    },
    totalPages() {
      return Math.ceil(this.cobranzas.length / this.perPage) || 1;
    }
  },
  methods: {
    toggleFilter(col) {
      // Cerrar otros filtros
      Object.keys(this.filters).forEach(key => {
        if (key !== col) this.filters[key].open = false;
      });
      this.filters[col].open = !this.filters[col].open;
    },
    filteredOptions(col) {
      const opts = this.uniqueOptions[col] || [];
      const search = this.filters[col].search.toLowerCase();
      return search
        ? opts.filter(o => o.toLowerCase().includes(search))
        : opts;
    },
    applyFilter(col) {
      this.filters[col].open = false;
    },
    clearFilter(col) {
      this.filters[col].selected = [];
    },
    abrirFormulario(c) {
      this.cobranzaEnEdicion = c;
      this.formVisible = true;
    },
    programarTarea(c) {
      this.cobranzaParaTarea = c;
      this.modalTareaVisible = true;
    },
    eliminarCobranza(id) {
      if (!confirm('¿Eliminar esta cobranza?')) return;
      axios.delete(`/crm-bioscom/public/api/cobranzas/${id}`)
        .then(() => { this.cargarCobranzas(); window.mostrarToast('success', 'Cobranza eliminada ✅'); })
        .catch(() => window.mostrarToast('error', 'No se pudo eliminar'));
    },
    cargarCobranzas() {
      axios.get('/crm-bioscom/public/api/cobranzas')
        .then(res => this.cobranzas = res.data);
    },
    normalizarEstado(e) {
      const map = { pendiente:'warning', en_gestion:'info', pagada:'success', vencida:'danger' };
      return map[e] || 'primary';
    }
  },
  mounted() {
    this.cargarCobranzas();
  }
};
</script>

<style>
.filter-dropdown {
  @apply absolute bg-white border border-gray-200 p-3 rounded shadow-lg mt-1 z-50;
}
</style>
