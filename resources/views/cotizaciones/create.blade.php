<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Crear Cotización - CRM Bioscom</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <!-- Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">CRM Bioscom</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/clientes">Clientes</a>
                <a class="nav-link" href="/productos">Productos</a>
                <a class="nav-link" href="/cotizaciones">Cotizaciones</a>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container my-4">
        <!-- Mensaje de debug temporal -->
        <div class="alert alert-info">
            <strong>🔧 Debug Info:</strong>
            <ul class="mb-0">
                <li>✅ Vista Blade cargada sin layout</li>
                <li>📊 Clientes disponibles: {{ isset($clientes) ? count($clientes) : 'No definidos' }}</li>
                <li>🔗 URL actual: {{ request()->url() }}</li>
            </ul>
        </div>
        
        <!-- Componente Vue -->
        <div id="app">
            <cotizacion-form :clientes="{{ json_encode($clientes ?? []) }}"></cotizacion-form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script para debugging -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🌟 Vista create.blade.php cargada (sin layout)');
        console.log('📱 Elemento #app existe:', document.getElementById('app') !== null);
        console.log('🗂️ Clientes desde PHP:', @json($clientes ?? []));
        
        // Verificar que Vue se está cargando
        setTimeout(() => {
            const appElement = document.querySelector('#app');
            console.log('🔍 Elemento #app encontrado:', appElement ? '✅ Sí' : '❌ No');
            console.log('📦 Contenido del #app:', appElement ? appElement.innerHTML.length + ' caracteres' : 'N/A');
        }, 2000);
    });
    </script>
</body>
</html>