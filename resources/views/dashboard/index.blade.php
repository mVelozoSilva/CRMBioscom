<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bioscom CRM</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f6fa; color: #00334e; }
        
        /* Header */
        .topbar { background-color: #ffffff; color: #00334e; padding: 15px 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
        .topbar .logo { font-size: 1.5em; font-weight: bold; color: #6284b8; }
        .topbar nav ul { list-style: none; display: flex; }
        .topbar nav ul li { margin-left: 25px; }
        .topbar nav ul li a { color: #00334e; text-decoration: none; padding: 5px 10px; border-radius: 4px; transition: all 0.3s ease; }
        .topbar nav ul li a:hover, .topbar nav ul li a.active { background-color: #5f87b8; color: white; }

        /* Main Content */
        .main-content { padding: 30px; max-width: 1400px; margin: 0 auto; }
        .dashboard-header { text-align: center; margin-bottom: 40px; }
        .dashboard-header h1 { color: #00334e; font-size: 2.5em; margin-bottom: 10px; }
        .dashboard-header p { color: #6c757d; font-size: 1.1em; }

        /* Cards Grid */
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .metric-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; transition: transform 0.3s ease; }
        .metric-card:hover { transform: translateY(-5px); }
        .metric-card .number { font-size: 2.5em; font-weight: bold; margin-bottom: 10px; }
        .metric-card .label { color: #6c757d; font-size: 0.9em; text-transform: uppercase; letter-spacing: 1px; }
        .metric-card.primary .number { color: #6284b8; }
        .metric-card.success .number { color: #28a745; }
        .metric-card.warning .number { color: #ffc107; }
        .metric-card.info .number { color: #17a2b8; }

        /* Content Grid */
        .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 40px; }
        
        /* Tables and Lists */
        .card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card h3 { color: #00334e; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f1f1f1; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background-color: #f8f9fa; color: #495057; font-weight: 600; }
        tr:hover { background-color: #f8f9fa; }

        /* Alerts */
        .alerts-section { margin-bottom: 30px; }
        .alert { padding: 15px; margin-bottom: 15px; border-radius: 8px; border-left: 4px solid; }
        .alert.warning { background-color: #fff3cd; border-color: #ffc107; color: #856404; }
        .alert.info { background-color: #d1ecf1; border-color: #17a2b8; color: #0c5460; }
        .alert.success { background-color: #d4edda; border-color: #28a745; color: #155724; }

        /* Quick Actions */
        .quick-actions { display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap; }
        .btn { padding: 12px 24px; background-color: #5f87b8; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; transition: background-color 0.3s ease; display: inline-flex; align-items: center; gap: 8px; }
        .btn:hover { background-color: #4a6b94; }
        .btn.secondary { background-color: #6c757d; }
        .btn.secondary:hover { background-color: #545b62; }

        /* Status badges */
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 0.8em; font-weight: 500; }
        .status-badge.pendiente { background-color: #fff3cd; color: #856404; }
        .status-badge.enviada { background-color: #d1ecf1; color: #0c5460; }
        .status-badge.aceptada { background-color: #d4edda; color: #155724; }
        .status-badge.rechazada { background-color: #f8d7da; color: #721c24; }

        /* Empty states */
        .empty-state { text-align: center; padding: 40px; color: #6c757d; }
        .empty-state h4 { margin-bottom: 10px; }

        /* Responsive */
        @media (max-width: 768px) {
            .content-grid { grid-template-columns: 1fr; }
            .metrics-grid { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
            .topbar { flex-direction: column; gap: 15px; }
            .topbar nav ul { flex-wrap: wrap; justify-content: center; }
        }
    </style>
</head>
<body>
    <!-- Header Navigation -->
    <div class="topbar">
        <div class="logo">Bioscom CRM</div>
        <nav>
            <ul>
                <li><a href="{{ route('dashboard') }}" class="active">Dashboard</a></li>
                <li><a href="{{ route('clientes.index') }}">Clientes</a></li>
                <li><a href="{{ route('productos.index') }}">Productos</a></li>
                <li><a href="{{ route('cotizaciones.index') }}">Cotizaciones</a></li>
                <li><a href="#">Ventas</a></li>
                <li><a href="#">Seguimiento</a></li>
                <li><a href="#">Tareas</a></li>
                <li><a href="#">Servicio Técnico</a></li>
                <li><a href="#">Cobranzas</a></li>
                <li><a href="#">Informes</a></li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="dashboard-header">
            <h1>Dashboard de Bioscom CRM</h1>
            <p>Resumen ejecutivo y métricas principales • {{ date('d/m/Y') }}</p>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="{{ route('cotizaciones.create') }}" class="btn">➕ Nueva Cotización</a>
            <a href="{{ route('clientes.create') }}" class="btn secondary">👥 Nuevo Cliente</a>
            <a href="{{ route('productos.create') }}" class="btn secondary">📦 Nuevo Producto</a>
        </div>

        <!-- Alerts Section -->
        @if(!empty($alertas))
        <div class="alerts-section">
            @foreach($alertas as $alerta)
            <div class="alert {{ $alerta['tipo'] }}">
                {{ $alerta['mensaje'] }}
            </div>
            @endforeach
        </div>
        @endif

        <!-- Metrics Grid -->
        <div class="metrics-grid">
            <div class="metric-card primary">
                <div class="number">{{ $totalClientes }}</div>
                <div class="label">Total Clientes</div>
            </div>
            <div class="metric-card success">
                <div class="number">{{ $totalCotizaciones }}</div>
                <div class="label">Cotizaciones</div>
            </div>
            <div class="metric-card warning">
                <div class="number">{{ $cotizacionesPendientes }}</div>
                <div class="label">Pendientes</div>
            </div>
            <div class="metric-card info">
                <div class="number">${{ number_format($valorCotizacionesEsteMes, 0, ',', '.') }}</div>
                <div class="label">Valor Este Mes</div>
            </div>
            <div class="metric-card primary">
                <div class="number">{{ $productosActivos }}</div>
                <div class="label">Productos Activos</div>
            </div>
            <div class="metric-card success">
                <div class="number">{{ $clientesEsteMes }}</div>
                <div class="label">Clientes Nuevos</div>
            </div>
            <div class="metric-card info">
                <div class="number">{{ $tasaConversion }}%</div>
                <div class="label">Tasa Conversión</div>
            </div>
            <div class="metric-card warning">
                <div class="number">{{ $totalContactos }}</div>
                <div class="label">Total Contactos</div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Recent Quotations -->
            <div class="card">
                <h3>📋 Cotizaciones Recientes</h3>
                @if($cotizacionesRecientes->count() > 0)
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Cliente</th>
                                <th>Valor</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cotizacionesRecientes as $cotizacion)
                            <tr>
                                <td><strong>{{ $cotizacion->codigo }}</strong></td>
                                <td>{{ $cotizacion->cliente->nombre_institucion ?? 'N/A' }}</td>
                                <td>${{ number_format($cotizacion->total_con_iva, 0, ',', '.') }}</td>
                                <td>
                                    <span class="status-badge {{ strtolower($cotizacion->estado) }}">
                                        {{ $cotizacion->estado }}
                                    </span>
                                </td>
                                <td>{{ $cotizacion->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="{{ route('cotizaciones.index') }}" class="btn">Ver Todas las Cotizaciones</a>
                </div>
                @else
                <div class="empty-state">
                    <h4>No hay cotizaciones recientes</h4>
                    <p>Crea tu primera cotización para comenzar</p>
                    <a href="{{ route('cotizaciones.create') }}" class="btn">Crear Cotización</a>
                </div>
                @endif
            </div>

            <!-- Right Sidebar -->
            <div>
                <!-- Recent Clients -->
                <div class="card" style="margin-bottom: 30px;">
                    <h3>👥 Clientes Recientes</h3>
                    @if($clientesRecientes->count() > 0)
                    @foreach($clientesRecientes as $cliente)
                    <div style="padding: 10px 0; border-bottom: 1px solid #f1f1f1;">
                        <div style="font-weight: 600; color: #00334e;">{{ $cliente->nombre_institucion }}</div>
                        <div style="font-size: 0.9em; color: #6c757d;">{{ $cliente->nombre_contacto }}</div>
                        <div style="font-size: 0.8em; color: #6c757d;">{{ $cliente->created_at->format('d/m/Y') }}</div>
                    </div>
                    @endforeach
                    <div style="text-align: center; margin-top: 15px;">
                        <a href="{{ route('clientes.index') }}" class="btn">Ver Todos</a>
                    </div>
                    @else
                    <div class="empty-state">
                        <p>No hay clientes registrados</p>
                        <a href="{{ route('clientes.create') }}" class="btn">Agregar Cliente</a>
                    </div>
                    @endif
                </div>

                <!-- Product Categories -->
                <div class="card">
                    <h3>📦 Productos por Categoría</h3>
                    @if(!empty($categoriaStats))
                    @foreach($categoriaStats as $categoria)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f1f1;">
                        <span style="color: #00334e;">{{ $categoria['nombre'] }}</span>
                        <span style="background-color: #6284b8; color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.8em;">
                            {{ $categoria['cantidad'] }}
                        </span>
                    </div>
                    @endforeach
                    <div style="text-align: center; margin-top: 15px;">
                        <a href="{{ route('productos.index') }}" class="btn">Ver Productos</a>
                    </div>
                    @else
                    <div class="empty-state">
                        <p>No hay productos registrados</p>
                        <a href="{{ route('productos.create') }}" class="btn">Agregar Producto</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>