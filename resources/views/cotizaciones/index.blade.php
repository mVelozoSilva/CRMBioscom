@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-sm text-gray-600">
            <li>
                <a href="{{ route('dashboard') }}" class="hover:text-bioscom-primary transition-colors">
                    <i class="fas fa-home mr-1"></i>Dashboard
                </a>
            </li>
            <li class="text-gray-400">/</li>
            <li class="text-gray-800 font-medium">Cotizaciones</li>
        </ol>
    </nav>

    <!-- APLICACIÓN VUE.JS -->
    <div id="app">
        <!-- Componente de tabla optimizada -->
        <cotizacion-table @toast="mostrarToast"></cotizacion-table>
    </div>

    <!-- Toast container (se maneja desde Vue) -->
    <div id="toast-container" class="fixed top-4 right-4 z-50"></div>
</div>

<!-- Scripts adicionales para el componente -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar manejo de toast desde Vue
    window.mostrarToast = function(tipo, mensaje) {
        const toastContainer = document.getElementById('toast-container');
        
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
            toast.remove();
        }, 5000);
    };
});
</script>
@endsection