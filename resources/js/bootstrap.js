import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
import 'bootstrap'; // Importa el JavaScript completo de Bootstrap