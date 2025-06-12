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
    
    <!-- CSS Variables para Paleta Bioscom -->
    <style>
        :root {
            --bioscom-primary: #6284b8;
            --bioscom-secondary: #5f87b8;
            --bioscom-accent: #00334e;
            --bioscom-background: #f3f6fa;
            --bioscom-text-primary: #00334e;
        }
        
        body { 
            background-color: var(--bioscom-background); 
            color: var(--bioscom-text-primary);
            font-family: "Inter", "Helvetica Neue", Helvetica, Arial, sans-serif;
        }
        
        .topbar { 
            background-color: #ffffff; 
            color: var(--bioscom-text-primary); 
            padding: 15px 30px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        
        .topbar .logo { 
            font-size: 1.5em; 
            font-weight: bold; 
            color: var(--bioscom-primary); 
        }
        
        .topbar nav ul { 
            list-style: none; 
            padding: 0; 
            margin: 0; 
            display: flex; 
        }
        
        .topbar nav ul li { 
            margin-left: 25px; 
        }
        
        .topbar nav ul li a { 
            color: var(--bioscom-text-primary); 
            text-decoration: none; 
            display: block; 
            padding: 5px 0; 
            transition: color 0.3s ease; 
            position: relative; 
        }
        
        .topbar nav ul li a::after { 
            content: ''; 
            position: absolute; 
            width: 0; 
            height: 2px; 
            bottom: 0; 
            left: 0; 
            background-color: var(--bioscom-secondary); 
            transition: width .3s ease-in-out; 
        }
        
        .topbar nav ul li a:hover::after, 
        .topbar nav ul li a.active::after { 
            width: 100%; 
        }
        
        .topbar nav ul li a:hover, 
        .topbar nav ul li a.active { 
            color: var(--bioscom-secondary); 
        }
        
        .main-content {
            padding: 30px;
            max-width: 1200px;
            margin: 30px auto;
        }
        
        .breadcrumb-container {
            margin-bottom: 30px;
        }
        
        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin: 0;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            content: ">";
            color: var(--bioscom-primary);
        }
        
        .breadcrumb-item a {
            color: var(--bioscom-primary);
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: var(--bioscom-text-primary);
        }
        
        .page-header {
            background-color: white;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border-left: 4px solid var(--bioscom-primary);
        }
        
        .page-title {
            color: var(--bioscom-text-primary);
            margin: 0;
            font-size: 1.8em;
            font-weight: 600;
        }
        
        .page-subtitle {
            color: #6c757d;
            margin: 5px 0 0 0;
            font-size: 1em;
        }
        
        .stats-card {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-left: 3px solid var(--bioscom-secondary);
        }
        
        .stats-card h6 {
            color: var(--bioscom-primary);
            margin: 0 0 10px 0;
            font-weight: 600;
        }
        
        .stats-card .stat-number {
            font-size: 1.5em;
            font-weight: bold;
            color: var(--bioscom-text-primary);
        }
    </style>
</head>
<body>
    <!-- Navegación principal -->
    <div class="topbar">
        <div class="logo">Bioscom CRM</div>
        <nav>
            <ul>
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('clientes.index') }}">Clientes</a></li>
                <li><a href="{{ route('productos.index') }}">Productos</a></li>
                <li><a href="{{ route('cotizaciones.index') }}" class="active">Cotizaciones</a></li>
                <li><a href="#">Ventas</a></li>
                <li><a href="#">Seguimiento</a></li>
                <li><a href="#">Tareas</a></li>
                <li><a href="#">Servicio Técnico</a></li>
                <li><a href="#">Cobranzas</a></li>
                <li><a href="#">Informes</a></li>
            </ul>
        </nav>
    </div>

    <!-- Contenido principal -->
    <div class="main-content">
        <!-- Breadcrumb -->
        <div class="breadcrumb-container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cotizaciones.index') }}">Cotizaciones</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Crear Nueva</li>
                </ol>
            </nav>
        </div>
        
        <!-- Header de la página -->
        <div class="page-header">
            <h1 class="page-title">Crear Nueva Cotización</h1>
            <p class="page-subtitle">
                Complete el formulario para generar una nueva cotización profesional para sus clientes
            </p>
        </div>
        
        <!-- Estadísticas rápidas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <h6>📊 Clientes Activos</h6>
                    <div class="stat-number">{{ isset($clientes) ? count($clientes) : '0' }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h6>📦 Productos Disponibles</h6>
                    <div class="stat-number">{{ \App\Models\Producto::where('estado', 'Activo')->count() }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h6>📄 Cotizaciones Este Mes</h6>
                    <div class="stat-number">{{ \App\Models\Cotizacion::whereMonth('created_at', now()->month)->count() }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h6>💰 Total Pendiente</h6>
                    <div class="stat-number">${{ number_format(\App\Models\Cotizacion::where('estado', 'Pendiente')->sum('total_con_iva'), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        
        <!-- Mensajes de alerta -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        <!-- Información de debug temporal -->
        <div class="alert alert-info">
            <strong>🔧 Debug Info (Creación):</strong>
            <ul class="mb-0">
                <li>✅ Vista create.blade.php con layout unificado</li>
                <li>📊 Clientes disponibles: {{ isset($clientes) ? count($clientes) : 'No definidos' }}</li>
                <li>🔗 URL actual: {{ request()->url() }}</li>
                <li>🎯 Código autogenerado: {{ $codigoCotizacion ?? 'No definido' }}</li>
            </ul>
        </div>
        
        <!-- Componente Vue -->
        <div id="app">
            <cotizacion-form 
                :clientes="{{ json_encode($clientes ?? []) }}"
                :initial-cotizacion="null">
            </cotizacion-form>
        </div>
        
        <!-- Enlaces de navegación rápida -->
        <div class="mt-4 text-center">
            <a href="{{ route('cotizaciones.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list"></i> Ver Todas las Cotizaciones
            </a>
            <a href="{{ route('clientes.create') }}" class="btn btn-outline-primary ms-2">
                <i class="fas fa-plus"></i> Crear Cliente Nuevo
            </a>
            <a href="{{ route('productos.create') }}" class="btn btn-outline-success ms-2">
                <i class="fas fa-box"></i> Agregar Producto
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    <!-- Script para debugging y funcionalidad -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🌟 Vista create.blade.php cargada con layout unificado');
        console.log('📱 Elemento #app existe:', document.getElementById('app') !== null);
        console.log('🗂️ Clientes desde PHP:', @json($clientes ?? []));
        console.log('🎯 Código generado:', @json($codigoCotizacion ?? null));
        
        // Verificar que Vue se está cargando
        setTimeout(() => {
            const appElement = document.querySelector('#app');
            console.log('🔍 Componente Vue cargado:', appElement ? '✅ Sí' : '❌ No');
            console.log('📦 Contenido del #app:', appElement ? appElement.innerHTML.length + ' caracteres' : 'N/A');
        }, 2000);
        
        // Auto-dismiss alerts después de 5 segundos
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                const closeBtn = alert.querySelector('.btn-close');
                if (closeBtn) closeBtn.click();
            });
        }, 5000);
        
        // Estadísticas en tiempo real (opcional)
        console.log('📊 Estadísticas cargadas en el header');
    });
    </script>
</body>
</html>