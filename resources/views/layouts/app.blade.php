<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', config('app.name', 'CRM Bioscom'))</title>
    
    <!-- FontAwesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS will be compiled by Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="bg-gray-100">
    <div id="app">
        <!-- Navbar con Tailwind -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="/" class="text-xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-heartbeat text-blue-600 mr-2"></i>
                            CRM Bioscom
                        </a>
                    </div>
                    <div class="hidden md:flex items-center space-x-6">
                        <a href="/clientes" class="text-gray-600 hover:text-gray-900 flex items-center transition-colors {{ request()->is('clientes*') ? 'text-blue-600 font-semibold' : '' }}">
                            <i class="fas fa-users mr-1"></i> Clientes
                        </a>
                        <a href="/productos" class="text-gray-600 hover:text-gray-900 flex items-center transition-colors {{ request()->is('productos*') ? 'text-blue-600 font-semibold' : '' }}">
                            <i class="fas fa-box mr-1"></i> Productos
                        </a>
                        <a href="/cotizaciones" class="text-gray-600 hover:text-gray-900 flex items-center transition-colors {{ request()->is('cotizaciones*') ? 'text-blue-600 font-semibold' : '' }}">
                            <i class="fas fa-file-invoice-dollar mr-1"></i> Cotizaciones
                        </a>
                        <!-- Enlace de Seguimiento con badge -->
                        <a href="/seguimiento" class="text-gray-600 hover:text-gray-900 flex items-center relative transition-colors {{ request()->is('seguimiento*') ? 'text-blue-600 font-semibold' : '' }}">
                            <i class="fas fa-tasks mr-1"></i> Seguimiento
                            <span id="badge-atrasados" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">!</span>
                        </a>
                    </div>
                    
                    <!-- Mobile menu button -->
                    <div class="md:hidden flex items-center">
                        <button type="button" class="text-gray-500 hover:text-gray-700 focus:outline-none focus:text-gray-700" id="mobile-menu-button">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Mobile menu -->
                <div class="md:hidden hidden" id="mobile-menu">
                    <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 border-t border-gray-200 mt-2">
                        <a href="/clientes" class="block px-3 py-2 text-gray-600 hover:text-gray-900">
                            <i class="fas fa-users mr-1"></i> Clientes
                        </a>
                        <a href="/productos" class="block px-3 py-2 text-gray-600 hover:text-gray-900">
                            <i class="fas fa-box mr-1"></i> Productos
                        </a>
                        <a href="/cotizaciones" class="block px-3 py-2 text-gray-600 hover:text-gray-900">
                            <i class="fas fa-file-invoice-dollar mr-1"></i> Cotizaciones
                        </a>
                        <a href="/seguimiento" class="block px-3 py-2 text-gray-600 hover:text-gray-900">
                            <i class="fas fa-tasks mr-1"></i> Seguimiento
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Alertas con Tailwind -->
        @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Errores de validación:</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        @endif

        <!-- Contenido principal -->
        <main class="min-h-screen">
            @yield('content')
        </main>
    </div>

    <!-- Scripts de utilidad -->
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });

        // Actualizar badge de seguimientos atrasados
        async function actualizarBadgeAtrasados() {
            try {
                const response = await fetch('/api/seguimiento/data?filtro=atrasados');
                const data = await response.json();
                
                if (data.success && data.stats) {
                    const badge = document.getElementById('badge-atrasados');
                    if (badge) {
                        if (data.stats.atrasados > 0) {
                            badge.textContent = data.stats.atrasados;
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }
                }
            } catch (error) {
                console.log('No se pudo actualizar el badge de seguimientos');
            }
        }

        // Actualizar badge cada 5 minutos
        if (document.querySelector('nav')) {
            actualizarBadgeAtrasados();
            setInterval(actualizarBadgeAtrasados, 300000); // 5 minutos
        }

        // Configuración global de CSRF para requests AJAX
        window.Laravel = {
            csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };
    </script>

    @stack('scripts')
</body>
</html>