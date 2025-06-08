// resources/js/app.js

import './bootstrap';
import { createApp } from 'vue';
import CotizacionForm from './components/CotizacionForm.vue';
import ClienteForm from './components/ClienteForm.vue'; // ¡Importa el nuevo componente!

const app = createApp({});

app.component('cotizacion-form', CotizacionForm);
app.component('cliente-form', ClienteForm); // ¡Registra el nuevo componente!

app.mount('#app');