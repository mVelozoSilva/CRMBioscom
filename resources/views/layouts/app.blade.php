<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'CRM Bioscom') }}</title>
    
    <!-- Favicon Bioscom -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/bioscom-isotipo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/bioscom-isotipo.png') }}">
    
    <!-- CRÍTICO: Carga los assets de Vite (incluirá app.css y app.js procesados por Tailwind) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-bioscom-background text-bioscom-text-primary font-sans">
    <div id="app">
        {{-- Navbar de Bioscom con Diseño Exacto --}}
        <nav class="bg-bioscom-background shadow-sm h-16 border-b border-bioscom-gray-200"> {{-- Fondo de la app, sombra sutil, borde inferior --}}
            <div class="container mx-auto px-[var(--bioscom-space-md)] h-full flex justify-between items-center">
                {{-- Logo de Bioscom: Ajustado al alto de la navbar con padding lateral --}}
                <a class="h-full py-[var(--bioscom-space-sm)] flex items-center" href="/"> {{-- Padding vertical para "aire" --}}
                    <img src="{{ asset('assets/images/bioscom-logo-dark.png') }}" 
                         alt="Bioscom Chile SpA" 
                         class="h-full w-auto"> {{-- Logo ocupando el alto del link, mantiene proporción --}}
                </a>
                
                {{-- Enlaces de navegación --}}
                <div class="flex space-x-[var(--bioscom-space-lg)]"> {{-- Espaciado entre enlaces --}}
                    <a class="text-bioscom-text-primary hover:text-bioscom-primary transition-colors duration-200 flex items-center font-semibold text-sm" 
                       href="{{ route('clientes.index') }}">
                        <i class="fas fa-building mr-[var(--bioscom-space-xs)]"></i> Clientes
                    </a>
                    <a class="text-bioscom-text-primary hover:text-bioscom-primary transition-colors duration-200 flex items-center font-semibold text-sm" 
                       href="{{ route('productos.index') }}">
                        <i class="fas fa-box mr-[var(--bioscom-space-xs)]"></i> Productos
                    </a>
                    <a class="text-bioscom-text-primary hover:text-bioscom-primary transition-colors duration-200 flex items-center font-semibold text-sm" 
                       href="{{ route('cotizaciones.index') }}">
                        <i class="fas fa-file-invoice mr-[var(--bioscom-space-xs)]"></i> Cotizaciones
                    </a>
                    <a class="text-bioscom-text-primary hover:text-bioscom-primary transition-colors duration-200 flex items-center font-bold text-sm border-b-2 border-bioscom-primary" 
                       href="{{ route('seguimiento.index') }}">
                        <i class="fas fa-tasks mr-[var(--bioscom-space-xs)]"></i> Seguimiento
                    </a>
                </div>
            </div>
        </nav>

        {{-- Contenido principal con espaciado adecuado --}}
        <main class="py-[var(--bioscom-space-lg)] px-[var(--bioscom-space-md)] bg-bioscom-background text-bioscom-text-primary min-h-screen">
            <div class="container mx-auto">
                @yield('content')
            </div>
        </main>
    </div>
   <script>
// Configurar axios cuando esté disponible (después de que Vue lo cargue)
document.addEventListener('DOMContentLoaded', function() {
    // Esperar a que axios esté disponible
    function configurarAxios() {
        if (typeof axios !== 'undefined') {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            axios.defaults.headers.common['Accept'] = 'application/json';
            axios.defaults.headers.common['Content-Type'] = 'application/json';
            console.log('✅ Axios configurado correctamente');
        } else {
            // Reintentar después de 100ms si axios no está disponible
            setTimeout(configurarAxios, 100);
        }
    }
    
    configurarAxios();
});
</script>
</body>
</html>
