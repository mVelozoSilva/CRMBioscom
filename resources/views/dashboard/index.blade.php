<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bioscom CRM</title>
    <style>
        /* Estilos basados en la paleta de colores de Bioscom */
    body {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        background-color: #f3f6fa; /* Color de fondo de Bioscom */
        color: #00334e; /* Color de letra principal oscuro de Bioscom */
        margin: 0;
        padding: 0;
        display: flex; /* Mantenemos flex para la estructura, pero cambiaremos la dirección */
        flex-direction: column; /* Contenido apilado verticalmente: header, luego main-content */
        min-height: 100vh;
    }

    /* --- Nuevo estilo para el MENÚ SUPERIOR (antes sidebar) --- */
    .topbar {
        background-color: #ffffff; /* Fondo claro para el topbar */
        color: #00334e; /* Texto oscuro para el topbar */
        padding: 15px 30px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .topbar .logo {
        font-size: 1.5em;
        font-weight: bold;
        color: #6284b8; /* Color primario de Bioscom para el logo */
    }
    .topbar nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex; /* Elementos del menú en línea */
    }
    .topbar nav ul li {
        margin-left: 25px; /* Espaciado entre elementos del menú */
    }
    .topbar nav ul li a {
        color: #00334e; /* Color de texto oscuro para los enlaces */
        text-decoration: none;
        display: block;
        padding: 5px 0;
        transition: color 0.3s ease;
        position: relative; /* Para el efecto de subrayado al pasar el ratón */
    }
    .topbar nav ul li a::after { /* Efecto de subrayado al pasar el ratón */
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 0;
        left: 0;
        background-color: #5f87b8; /* Color secundario */
        transition: width .3s ease-in-out;
    }
    .topbar nav ul li a:hover::after,
    .topbar nav ul li a.active::after {
        width: 100%;
    }
    .topbar nav ul li a:hover, .topbar nav ul li a.active {
        color: #5f87b8; /* Color secundario al pasar el ratón o activo */
    }

    .main-content {
        flex-grow: 1;
        padding: 30px;
    }
    .header {
        background-color: #ffffff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    /* --- Títulos en variante oscura --- */
    .header h1 {
        color: #00334e; /* Color de letra principal oscuro */
        margin: 0;
    }
    .header .user-info {
        font-weight: bold;
        color: #6284b8; /* Color primario de Bioscom */
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
    }
    .widget {
        background-color: #ffffff;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 120px;
    }
    .widget h3 {
        color: #00334e; /* Títulos de widgets en variante oscura */
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 1.2em;
    }
    .widget .value {
        font-size: 2.2em;
        font-weight: bold;
        color: #00334e; /* Color de letra principal */
        text-align: right;
    }
    .widget .description {
        font-size: 0.9em;
        color: #6c757d;
        text-align: right;
        margin-top: 5px;
    }
    .widget-actions {
        text-align: right;
        margin-top: 15px;
    }
    .widget-actions a {
        color: #5f87b8;
        text-decoration: none;
        font-size: 0.9em;
        font-weight: bold;
    }
    .widget-actions a:hover {
        text-decoration: underline;
    }
    .widget.small {
        background-color: #f3f6fa;
        border: 1px solid #ddd;
        padding: 15px;
        min-height: auto;
    }
    .widget.small h3 { font-size: 1em; margin-bottom: 10px; }
    .widget.small .value { font-size: 1.5em; }
    .widget.small .description { font-size: 0.8em; }
    
    </style>
</head>
<body>
    <body>
    <div class="topbar">
        <div class="logo">Bioscom CRM</div>
        <nav>
            <ul>
                <li><a href="{{ route('dashboard') }}" class="active">Dashboard</a></li>
                <li><a href="{{ route('clientes.index') }}">Clientes</a></li>
                <li><a href="#">Cotizaciones</a></li>
                <li><a href="#">Ventas</a></li>
                <li><a href="#">Seguimiento</a></li>
                <li><a href="#">Tareas</a></li>
                <li><a href="#">Servicio Técnico</a></li>
                <li><a href="#">Productos</a></li>
                <li><a href="#">Cobranzas</a></li>
                <li><a href="#">Informes</a></li>
            </ul>
        </nav>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Dashboard Principal</h1>
            <div class="user-info">Hola, [Nombre Usuario]!</div>
        </div>

        <div class="dashboard-grid">
            <div class="widget">
                <h3>Total de Clientes Registrados</h3>
                <div class="value">{{ $totalClientes }}</div>
                <div class="description">Clientes en tu base de datos</div>
                <div class="widget-actions">
                    <a href="{{ route('clientes.index') }}">Ver todos los clientes</a>
                </div>
            </div>

            <div class="widget">
                <h3>Tareas Pendientes</h3>
                <div class="value">5</div>
                <div class="description">Para hoy y esta semana</div>
                <div class="widget-actions">
                    <a href="#">Ir a Tareas</a>
                </div>
            </div>

            <div class="widget">
                <h3>Seguimientos Pendientes</h3>
                <div class="value">3</div>
                <div class="description">De alta prioridad</div>
                <div class="widget-actions">
                    <a href="#">Ir a Seguimientos</a>
                </div>
            </div>

            <div class="widget small">
                <h3>Cotizaciones Generadas (Este Mes)</h3>
                <div class="value">12</div>
                <div class="description">Valor estimado: $25,000,000</div>
            </div>

            <div class="widget small">
                <h3>Ventas Cerradas (Este Mes)</h3>
                <div class="value">7</div>
                <div class="description">Valor total: $18,500,000</div>
            </div>

            <div class="widget small">
                <h3>Servicio Técnico (Pendientes)</h3>
                <div class="value">2</div>
                <div class="description">Solicitudes activas</div>
            </div>

            </div>
    </div>
</body>
</html>