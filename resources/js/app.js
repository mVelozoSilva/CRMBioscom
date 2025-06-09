// resources/js/app.js

import './bootstrap'; // Asegúrate de que este archivo maneje la importación de Bootstrap
import { createApp } from 'vue';
import CotizacionForm from './components/CotizacionForm.vue';
import ClienteForm from './components/ClienteForm.vue';
import SeguimientoTable from './components/SeguimientoTable.vue'; // ¡RUTA CORRECTA AHORA QUE NO ESTÁ EN SUBCARPETA!

console.log('🚀 Iniciando Vue...');
console.log('✅ Componentes importados exitosamente');

// Crear una única aplicación Vue
console.log('🏗️ Creando aplicación Vue...');
const app = createApp({});

// Registrar componentes globalmente
console.log('📝 Registrando componentes...');
app.component('cotizacion-form', CotizacionForm);
app.component('cliente-form', ClienteForm);
app.component('seguimiento-table', SeguimientoTable); // Registra el nuevo componente

console.log('✅ Componentes registrados exitosamente');

// Montar la aplicación en el elemento principal (todos los componentes compartirán esta instancia)
const appElement = document.getElementById('app');
if (appElement) {
    console.log('🎯 Montando Vue en #app...');
    try {
        app.mount('#app');
        console.log('✅ Vue montado exitosamente');
    } catch (mountError) {
        console.error('❌ Error al montar Vue:', mountError);
    }
} else {
    console.error('❌ Elemento #app no encontrado. Asegúrate de tener <div id="app"> en tu vista principal.');
}