// resources/js/app.js

import { createApp } from 'vue';
import axios from 'axios';


// Importar todos los componentes
import CotizacionForm from './components/CotizacionForm.vue';
import ClienteForm from './components/ClienteForm.vue';
import ClienteTable from './components/ClienteTable.vue';
import SeguimientoTable from './components/SeguimientoTable.vue'; 
import CotizacionTable from './components/CotizacionTable.vue';
import TareaDayList from './components/TareaDayList.vue';
import TareaForm from './components/TareaForm.vue';
import TareaWeekView from './components/TareaWeekView.vue';
import CobranzaTable from './components/CobranzaTable.vue'; 
import UserDisplayPreferences from './components/UserDisplayPreferences.vue';
import CobranzaForm from './components/CobranzaForm.vue';
import FormularioInteligente from './components/FormularioInteligente.vue';
import ServicioTecnicoTable from './components/servicio_tecnico/ServicioTecnicoTable.vue';
import ServicioTecnicoForm from './components/servicio_tecnico/ServicioTecnicoForm.vue';
import CampaniaForm from './components/campanias/CampaniaForm.vue';
import CampaniaTable from './components/campanias/CampaniaTable.vue';
import ArchivoForm from './components/archivos/ArchivoForm.vue';
import ArchivoTable from './components/archivos/ArchivoTable.vue';
import NotificacionForm from './components/notificaciones/NotificacionForm.vue';
import NotificacionTable from './components/notificaciones/NotificacionTable.vue';
import FormularioGestorForm from './components/formularios/FormularioGestorForm.vue';
import FormularioGestorTable from './components/formularios/FormularioGestorTable.vue';
import ConfiguracionSupervisorForm from './components/configuracion/ConfiguracionSupervisorForm.vue';
import ConfiguracionSupervisorTable from './components/configuracion/ConfiguracionSupervisorTable.vue';



console.log('🚀 Iniciando Vue 3...');
console.log('✅ Componentes importados exitosamente');


// Configurar axios globalmente ANTES de crear la app
console.log('🔧 Configurando Axios...');
window.axios = axios;

// Configurar CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    console.log('✅ CSRF token configurado');
} else {
    console.warn('⚠️ CSRF token no encontrado');
}

// Configurar headers por defecto
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['Content-Type'] = 'application/json';

// Interceptor para debugging de requests
axios.interceptors.request.use(function (config) {
    console.log('📤 Enviando request:', config.method?.toUpperCase(), config.url);
    return config;
}, function (error) {
    console.error('❌ Error en request:', error);
    return Promise.reject(error);
});

// Interceptor para debugging de responses
axios.interceptors.response.use(function (response) {
    console.log('📥 Response recibido:', response.status, response.config.url);
    return response;
}, function (error) {
    console.error('❌ Error en response:', error.response?.status, error.config?.url);
    return Promise.reject(error);
});

// 🔧 FUNCIÓN GLOBAL PARA TOASTS - FUERA DE initializeVue()
window.mostrarToast = function(tipo, mensaje) {
    console.log('🍞 Mostrando toast:', tipo, mensaje);
    
    const toastContainer = document.getElementById('toast-container');
    
    if (!toastContainer) {
        console.warn('⚠️ Toast container no encontrado, creando uno temporal');
        // Crear container temporal si no existe
        const tempContainer = document.createElement('div');
        tempContainer.id = 'toast-container';
        tempContainer.className = 'fixed top-4 right-4 z-50';
        document.body.appendChild(tempContainer);
        return window.mostrarToast(tipo, mensaje); // Llamar recursivamente
    }
    
    const toast = document.createElement('div');
    toast.className = `p-4 rounded-lg shadow-lg transition-all duration-300 mb-2 ${
        tipo === 'success' ? 'bg-green-500 text-white' :
        tipo === 'error' ? 'bg-red-500 text-white' :
        tipo === 'warning' ? 'bg-yellow-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="${
                tipo === 'success' ? 'fas fa-check-circle' :
                tipo === 'error' ? 'fas fa-exclamation-circle' :
                tipo === 'warning' ? 'fas fa-exclamation-triangle' :
                'fas fa-info-circle'
            } mr-2"></i>
            ${mensaje}
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);
};

// Función para inicializar Vue
function initializeVue() {
    const appElement = document.getElementById('app');
    
    if (!appElement) {
        console.warn('⚠️ Elemento #app no encontrado - Vue no se montará');
        return;
    }

    console.log('🏗️ Creando aplicación Vue 3...');
    
    try {
        // Crear una única aplicación Vue
        const app = createApp({
            mounted() {
                console.log('🎯 Aplicación Vue montada exitosamente!');
            }
        });
        
        // Registrar componentes globalmente
        console.log('📝 Registrando componentes...');
        app.component('cotizacion-form', CotizacionForm);  
        app.component('cliente-form', ClienteForm);
        app.component('seguimiento-table', SeguimientoTable);
        app.component('cotizacion-table', CotizacionTable); 
        app.component('cliente-table', ClienteTable);
        app.component('tarea-day-list', TareaDayList);
        app.component('tarea-form', TareaForm);
        app.component('tarea-week-view', TareaWeekView);
        app.component('cobranza-table', CobranzaTable); // ← REGISTRAR NUEVO COMPONENTE
        app.component('user-display-preferences', UserDisplayPreferences)
        app.component('cobranza-form', CobranzaForm); // ← REGISTRAR NUEVO COMPONENTE
        app.component('formulario-inteligente', FormularioInteligente); // ← REGISTRAR NUEVO COMPONENTE
        app.component('campania-table', CampaniaTable);
        app.component('campania-form', CampaniaForm);
        app.component('servicio-tecnico-table', ServicioTecnicoTable);
        app.component('servicio-tecnico-form', ServicioTecnicoForm);
        app.component('formulario-gestor-form', FormularioGestorForm);
        app.component('formulario-gestor-table', FormularioGestorTable);
        app.component('notificacion-form', NotificacionForm);
        app.component('notificacion-table', NotificacionTable);
        app.component('archivo-form', ArchivoForm);
        app.component('archivo-table', ArchivoTable);
        app.component('configuracion-supervisor-table', ConfiguracionSupervisorTable);
        app.component('configuracion-supervisor-form', ConfiguracionSupervisorForm);
      


        console.log('✅ Componentes registrados:', ['cotizacion-form', 'cliente-form', 'seguimiento-table', 'cotizacion-table', 'cliente-table', 'tarea-day-list', 'tarea-form', 'tarea-week-view', 'cobranza-table', 'user-display-preferences', 'cobranza-form', 'formulario-inteligente', 'campania-table', 'campania-form', 'servicio-tecnico-table', 'servicio-tecnico-form', 'archivos'].join(', '));

        // 🔧 HACER DISPONIBLE MOSTRARTOAST EN VUE
        app.config.globalProperties.$mostrarToast = window.mostrarToast;

        // Montar la aplicación
        console.log('🎯 Montando Vue en #app...');
        const mountedApp = app.mount('#app');
        document.getElementById('app').__vue_app__ = app;
        console.log('✅ Vue montado exitosamente en #app');
        
        // Hacer disponible la instancia globalmente para debugging
        window.vueApp = mountedApp;
        
    } 
    
    catch (mountError) {
        console.error('❌ Error crítico al montar Vue:', mountError);
        console.error('Stack trace:', mountError.stack);
    }
}

// Esperar a que el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeVue);
} else {
    // DOM ya está listo
    initializeVue();
}

// Export para uso en otros archivos si es necesario
export { axios };