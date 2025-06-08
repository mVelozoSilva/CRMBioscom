<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <meta name="csrf-token" content="{{ csrf_token() }}"> 
    <title>Crear Cotización - Bioscom CRM</title>
    
    <style>
        body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; background-color: #f3f6fa; color: #00334e; margin: 0; padding: 0; }
        .topbar { background-color: #ffffff; color: #00334e; padding: 15px 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .topbar .logo { font-size: 1.5em; font-weight: bold; color: #6284b8; }
        .topbar nav ul { list-style: none; padding: 0; margin: 0; display: flex; }
        .topbar nav ul li { margin-left: 25px; }
        .topbar nav ul li a { color: #00334e; text-decoration: none; display: block; padding: 5px 0; transition: color 0.3s ease; position: relative; }
        .topbar nav ul li a::after { content: ''; position: absolute; width: 0; height: 2px; bottom: 0; left: 0; background-color: #5f87b8; transition: width .3s ease-in-out; }
        .topbar nav ul li a:hover::after, .topbar nav ul li a.active::after { width: 100%; }
        .topbar nav ul li a:hover, .topbar nav ul li a.active { color: #5f87b8; }
        .main-content { flex-grow: 1; padding: 30px; max-width: 900px; margin: 30px auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #00334e; text-align: center; margin-bottom: 30px; }
        .form-section { border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-bottom: 25px; }
        .form-section-title { color: #6284b8; margin-top: 0; margin-bottom: 20px; font-size: 1.4em; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="date"], input[type="number"], select, textarea {
            width: calc(100% - 22px); padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;
        }
        textarea { min-height: 80px; resize: vertical; }
        .btn { padding: 10px 15px; background-color: #5f87b8; color: white; text-decoration: none; border-radius: 5px; cursor: pointer; border: none; }
        .btn-back { background-color: #6c757d; margin-left: 10px; }
        .error-message { color: red; font-size: 0.9em; margin-top: 5px; }
        .product-item { border: 1px solid #eee; padding: 15px; margin-bottom: 15px; background-color: #f9f9f9; border-radius: 5px; position: relative;}
        .remove-product-btn {
            background-color: #dc3545; color: white; border: none; border-radius: 50%; width: 25px; height: 25px;
            font-size: 1.2em; cursor: pointer; position: absolute; top: 10px; right: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .add-product-btn { background-color: #28a745; margin-top: 15px; }
        .totals-section { text-align: right; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        .totals-section div { margin-bottom: 8px; font-size: 1.1em; }
        .totals-section .final-total { font-size: 1.5em; font-weight: bold; color: #00334e; }
    </style>
    
    {{-- Aquí se cargará tu JavaScript compilado (incluyendo Vue.js) --}}
    @vite('resources/js/app.js')
</head>
<body>
    {{-- Este div es el punto de montaje de Vue.js. Todo lo que Vue renderice irá aquí dentro. --}}
    <div id="app">
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

        <div class="main-content">
            <h1>Crear Nueva Cotización</h1>

            {{-- Tu componente de formulario de cotizaciones de Vue.js --}}
            {{-- Pasamos la lista de clientes. Asegúrate de que $clientes siempre sea un array. --}}
            <cotizacion-form :clientes="{{ json_encode($clientes) }}"></cotizacion-form>
        </div>
    </div>
</body>
</html>
