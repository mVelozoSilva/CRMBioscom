<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'CRM Bioscom') }}</title>
    
    <!-- Favicon Bioscom -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/bioscom-isotipo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/bioscom-isotipo.png') }}">
    
    <!-- CSS Variables para Paleta Bioscom -->
    <style>
        :root {
            --bioscom-primary: #6284b8;
            --bioscom-secondary: #5f87b8;
            --bioscom-accent: #00334e;
            --bioscom-background: #f3f6fa;
            --bioscom-text-primary: #00334e;
            --bioscom-success: #28a745;
            --bioscom-warning: #ffc107;
            --bioscom-error: #dc3545;
            --bioscom-info: #17a2b8;
            --bioscom-neutral: #6c757d;
        }
        
        .bg-bioscom-primary { background-color: var(--bioscom-primary); }
        .bg-bioscom-background { background-color: var(--bioscom-background); }
        .text-bioscom-text-primary { color: var(--bioscom-text-primary) !important; }
        .text-bioscom-primary { color: var(--bioscom-primary); }
        .text-bioscom-secondary { color: var(--bioscom-secondary); }
        .hover\:text-bioscom-secondary:hover { color: var(--bioscom-secondary); }
        .hover\:text-bioscom-primary:hover { color: var(--bioscom-primary) !important; }
        .border-bioscom-primary { border-color: var(--bioscom-primary); }
    </style>
    
    <!-- Assets de Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-bioscom-background text-bioscom-text-primary font-sans">
    <div id="app">
        {{-- Navbar Bioscom con colores oficiales --}}
        <nav class="bg-bioscom-background shadow-md h-16 border-b border-gray-200">
            <div class="container mx-auto px-8 h-full flex justify-between items-center">
                {{-- Logo con más aire/espaciado --}}
                <a class="flex items-center h-full py-3 pl-4" href="/">
                    <img src="{{ asset('assets/images/bioscom-logo-dark.png') }}" 
                         alt="Bioscom Chile SpA" 
                         class="h-10 w-auto">
                </a>
                
                {{-- Enlaces de navegación con color de títulos --}}
                <div class="flex space-x-8 pr-4">
                    <a class="text-bioscom-text-primary hover:text-bioscom-primary transition-colors duration-200 flex items-center font-semibold text-sm" 
                       href="{{ route('clientes.index') }}">
                        <i class="fas fa-building mr-2"></i> Clientes
                    </a>
                    <a class="text-bioscom-text-primary hover:text-bioscom-primary transition-colors duration-200 flex items-center font-semibold text-sm" 
                       href="{{ route('productos.index') }}">
                        <i class="fas fa-box mr-2"></i> Productos
                    </a>
                    <a class="text-bioscom-text-primary hover:text-bioscom-primary transition-colors duration-200 flex items-center font-semibold text-sm" 
                       href="{{ route('cotizaciones.index') }}">
                        <i class="fas fa-file-invoice mr-2"></i> Cotizaciones
                    </a>
                    <a class="text-bioscom-text-primary hover:text-bioscom-primary transition-colors duration-200 flex items-center font-bold text-sm border-b-2 border-bioscom-primary" 
                       href="{{ route('seguimiento.index') }}">
                        <i class="fas fa-tasks mr-2"></i> Seguimiento
                    </a>
                </div>
            </div>
        </nav>

        {{-- Contenido principal con espaciado adecuado --}}
        <main class="bg-bioscom-background min-h-screen pt-6">
            <div class="container mx-auto px-4">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>