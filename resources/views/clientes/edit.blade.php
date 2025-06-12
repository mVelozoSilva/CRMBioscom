<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestión de Clientes - Bioscom CRM</title>
    
    <!-- CSS Variables Bioscom -->
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
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: "Inter", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; 
            background-color: var(--bioscom-background); 
            color: var(--bioscom-text-primary); 
            line-height: 1.6;
        }

        /* Header Navigation */
        .topbar { 
            background-color: var(--bioscom-primary); 
            color: white; 
            padding: 15px 30px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            position: sticky; 
            top: 0; 
            z-index: 100; 
        }
        .topbar .logo { 
            font-size: 1.5em; 
            font-weight: bold; 
            color: white; 
            display: flex; 
            align-items: center; 
            gap: 10px;
        }
        .topbar nav ul { 
            list-style: none; 
            display: flex; 
            margin: 0;
        }
        .topbar nav ul li { 
            margin-left: 25px; 
        }
        .topbar nav ul li a { 
            color: rgba(255,255,255,0.9); 
            text-decoration: none; 
            padding: 8px 12px; 
            border-radius: 6px; 
            transition: all 0.3s ease; 
            font-weight: 500;
        }
        .topbar nav ul li a:hover, 
        .topbar nav ul li a.active { 
            background-color: rgba(255,255,255,0.2); 
            color: white; 
        }

        /* Main Content */
        .main-content { 
            padding: 30px; 
            max-width: 1400px; 
            margin: 0 auto; 
        }

        /* Breadcrumb */
        .breadcrumb-container {
            margin-bottom: 30px;
        }
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0;
            margin: 0;
            list-style: none;
            font-size: 14px;
        }
        .breadcrumb-item {
            color: var(--bioscom-neutral);
        }
        .breadcrumb-item a {
            color: var(--bioscom-primary);
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .breadcrumb-item a:hover {
            color: var(--bioscom-secondary);
        }
        .breadcrumb-item:not(:last-child)::after {
            content: ">";
            margin-left: 8px;
            color: var(--bioscom-neutral);
        }
        .breadcrumb-item.active {
            color: var(--bioscom-text-primary);
            font-weight: 500;
        }

        /* Page Header */
        .page-header { 
            background: linear-gradient(135deg, var(--bioscom-primary) 0%, var(--bioscom-secondary) 100%);
            color: white; 
            padding: 30px; 
            border-radius: 12px; 
            margin-bottom: 30px; 
            box-shadow: 0 4px 20px rgba(98, 132, 184, 0.3);
        }
        .page-header h1 { 
            font-size: 2.2em; 
            margin-bottom: 8px; 
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .page-header p { 
            font-size: 1.1em; 
            opacity: 0.9;
        }

        /* Stats Cards */
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px; 
            margin-bottom: 30px; 
        }
        .stat-card { 
            background: white; 
            padding: 25px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.08); 
            text-align: center; 
            transition: all 0.3s ease; 
            border-top: 4px solid;
        }
        .stat-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .stat-card .number { 
            font-size: 2.5em; 
            font-weight: 700; 
            margin-bottom: 10px; 
        }
        .stat-card .label { 
            color: var(--bioscom-neutral); 
            font-size: 0.9em; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            font-weight: 500;
        }
        .stat-card.primary { border-top-color: var(--bioscom-primary); }
        .stat-card.primary .number { color: var(--bioscom-primary); }
        .stat-card.success { border-top-color: var(--bioscom-success); }
        .stat-card.success .number { color: var(--bioscom-success); }
        .stat-card.info { border-top-color: var(--bioscom-info); }
        .stat-card.info .number { color: var(--bioscom-info); }

        /* Action Bar */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .search-box {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .search-input {
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            min-width: 300px;
            transition: all 0.3s ease;
        }
        .search-input:focus {
            outline: none;
            border-color: var(--bioscom-primary);
            box-shadow: 0 0 0 3px rgba(98, 132, 184, 0.1);
        }

        /* Buttons */
        .btn { 
            padding: 12px 24px; 
            background-color: var(--bioscom-primary); 
            color: white; 
            text-decoration: none; 
            border-radius: 8px; 
            font-weight: 500; 
            transition: all 0.3s ease; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            border: none; 
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(98, 132, 184, 0.3);
        }
        .btn:hover { 
            background-color: var(--bioscom-secondary); 
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(98, 132, 184, 0.4);
            color: white;
            text-decoration: none;
        }
        .btn.success { 
            background-color: var(--bioscom-success); 
        }
        .btn.success:hover { 
            background-color: #218838; 
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .table-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: between;
            align-items: center;
        }
        .table-title {
            font-size: 1.2em;
            font-weight: 600;
            color: var(--bioscom-text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .table-responsive { 
            overflow-x: auto; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        th, td { 
            padding: 16px 20px; 
            text-align: left; 
        }
        th { 
            background-color: #f8f9fa; 
            color: var(--bioscom-text-primary); 
            font-weight: 600; 
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e9ecef;
        }
        tbody tr {
            border-bottom: 1px solid #f1f1f1;
            transition: all 0.2s ease;
        }
        tbody tr:hover { 
            background-color: #f8f9fa; 
        }
        tbody tr:last-child {
            border-bottom: none;
        }

        /* Type Badges */
        .type-badge { 
            padding: 6px 12px; 
            border-radius: 20px; 
            font-size: 0.8em; 
            font-weight: 500; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .type-publico { 
            background-color: #e3f2fd; 
            color: #1565c0; 
        }
        .type-privado { 
            background-color: #f3e5f5; 
            color: #7b1fa2; 
        }
        .type-revendedor { 
            background-color: #fff3e0; 
            color: #ef6c00; 
        }

        /* Actions */
        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .action-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.85em;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .action-btn.view { 
            background-color: #e3f2fd; 
            color: #1565c0; 
        }
        .action-btn.view:hover { 
            background-color: #bbdefb; 
        }
        .action-btn.edit { 
            background-color: #f3e5f5; 
            color: #7b1fa2; 
        }
        .action-btn.edit:hover { 
            background-color: #e1bee7; 
        }
        .action-btn.delete { 
            background-color: #ffebee; 
            color: #c62828; 
        }
        .action-btn.delete:hover { 
            background-color: #ffcdd2; 
        }

        /* Alerts */
        .alert { 
            padding: 16px 20px; 
            margin-bottom: 20px; 
            border-radius: 8px; 
            border-left: 4px solid;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .alert.success { 
            background-color: #d4edda; 
            color: #155724; 
            border-color: var(--bioscom-success);
        }
        .alert.error { 
            background-color: #f8d7da; 
            color: #721c24; 
            border-color: var(--bioscom-error);
        }

        /* Empty State */
        .empty-state { 
            text-align: center; 
            padding: 60px 20px;
            color: var(--bioscom-neutral);
        }
        .empty-state .icon {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        .empty-state h3 { 
            margin-bottom: 10px; 
            color: var(--bioscom-text-primary);
        }
        .empty-state p {
            margin-bottom: 30px;
            font-size: 1.1em;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .topbar { 
                flex-direction: column; 
                gap: 15px; 
                padding: 15px;
            }
            .topbar nav ul { 
                flex-wrap: wrap; 
                justify-content: center; 
            }
            .action-bar {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            .search-input {
                min-width: auto;
            }
            .stats-grid { 
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); 
            }
            .table-responsive {
                font-size: 0.9em;
            }
            th, td {
                padding: 12px 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Header Navigation -->
    <div class="topbar">
        <div class="logo">
            🏥 Bioscom CRM
        </div>
        <nav>
            <ul>
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('clientes.index') }}" class="active">Clientes</a></li>
                <li><a href="{{ route('productos.index') }}">Productos</a></li>
                <li><a href="{{ route('cotizaciones.index') }}">Cotizaciones</a></li>
                <li><a href="{{ route('seguimiento.index') }}">Seguimiento</a></li>
                <li><a href="#">Servicio Técnico</a></li>
                <li><a href="#">Cobranzas</a></li>
                <li><a href="#">Informes</a></li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Breadcrumb -->
        <div class="breadcrumb-container">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Clientes</li>
            </ol>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1>
                <i class="fas fa-building"></i>
                Gestión de Clientes
            </h1>
            <p>Administra tu cartera de clientes de equipamiento médico e insumos hospitalarios</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="number">{{ $clientes->count() }}</div>
                <div class="label">Total Clientes</div>
            </div>
            <div class="stat-card success">
                <div class="number">{{ $clientes->where('tipo_cliente', 'Cliente Público')->count() }}</div>
                <div class="label">Públicos</div>
            </div>
            <div class="stat-card info">
                <div class="number">{{ $clientes->where('tipo_cliente', 'Cliente Privado')->count() }}</div>
                <div class="label">Privados</div>
            </div>
            <div class="stat-card primary">
                <div class="number">{{ $clientes->where('tipo_cliente', 'Revendedor')->count() }}</div>
                <div class="label">Revendedores</div>
            </div>
        </div>

        <!-- Messages -->
        @if(session('success'))
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        <!-- Action Bar -->
        <div class="action-bar">
            <div class="search-box">
                <input type="text" class="search-input" placeholder="🔍 Buscar por institución, RUT o contacto..." id="searchInput">
                <select class="search-input" style="min-width: 180px;" id="typeFilter">
                    <option value="">Todos los tipos</option>
                    <option value="Cliente Público">Cliente Público</option>
                    <option value="Cliente Privado">Cliente Privado</option>
                    <option value="Revendedor">Revendedor</option>
                </select>
            </div>
            <div>
                <a href="{{ route('clientes.create') }}" class="btn success">
                    <i class="fas fa-plus"></i> 
                    Nuevo Cliente
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-table"></i> 
                    Lista de Clientes ({{ $clientes->count() }} registros)
                </div>
            </div>
            
            @if($clientes->isEmpty())
                <div class="empty-state">
                    <div class="icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3>No hay clientes registrados</h3>
                    <p>Comienza agregando tu primer cliente para gestionar tus ventas</p>
                    <a href="{{ route('clientes.create') }}" class="btn success">
                        <i class="fas fa-plus"></i> 
                        Crear Primer Cliente
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table id="clientesTable">
                        <thead>
                            <tr>
                                <th>Institución</th>
                                <th>RUT</th>
                                <th>Tipo Cliente</th>
                                <th>Contacto Principal</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Registrado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clientes as $cliente)
                                <tr data-type="{{ $cliente->tipo_cliente }}" data-search="{{ strtolower($cliente->nombre_institucion . ' ' . $cliente->rut . ' ' . $cliente->nombre_contacto . ' ' . $cliente->email) }}">
                                    <td>
                                        <div style="font-weight: 600; color: var(--bioscom-text-primary);">
                                            {{ $cliente->nombre_institucion }}
                                        </div>
                                        @if($cliente->direccion)
                                            <div style="font-size: 0.85em; color: var(--bioscom-neutral); margin-top: 4px;">
                                                <i class="fas fa-map-marker-alt"></i> {{ Str::limit($cliente->direccion, 40) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <code style="background: #f8f9fa; padding: 4px 8px; border-radius: 4px; font-family: monospace;">
                                            {{ $cliente->rut }}
                                        </code>
                                    </td>
                                    <td>
                                        <span class="type-badge type-{{ strtolower(str_replace(' ', '', $cliente->tipo_cliente)) }}">
                                            {{ $cliente->tipo_cliente }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 500;">{{ $cliente->nombre_contacto }}</div>
                                        @if($cliente->vendedores_a_cargo && count($cliente->vendedores_a_cargo) > 0)
                                            <div style="font-size: 0.8em; color: var(--bioscom-info); margin-top: 4px;">
                                                <i class="fas fa-user-tie"></i> {{ implode(', ', array_slice($cliente->vendedores_a_cargo, 0, 2)) }}
                                                @if(count($cliente->vendedores_a_cargo) > 2)
                                                    <span title="{{ implode(', ', $cliente->vendedores_a_cargo) }}">...</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($cliente->email)
                                            <a href="mailto:{{ $cliente->email }}" style="color: var(--bioscom-primary); text-decoration: none;">
                                                {{ $cliente->email }}
                                            </a>
                                        @else
                                            <span style="color: var(--bioscom-neutral); font-style: italic;">Sin email</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($cliente->telefono)
                                            <a href="tel:{{ $cliente->telefono }}" style="color: var(--bioscom-primary); text-decoration: none;">
                                                {{ $cliente->telefono }}
                                            </a>
                                        @else
                                            <span style="color: var(--bioscom-neutral); font-style: italic;">Sin teléfono</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-size: 0.9em;">{{ $cliente->created_at->format('d/m/Y') }}</div>
                                        <div style="font-size: 0.8em; color: var(--bioscom-neutral);">{{ $cliente->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="{{ route('clientes.show', $cliente->id) }}" class="action-btn view" title="Ver detalles">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                            <a href="{{ route('clientes.edit', $cliente->id) }}" class="action-btn edit" title="Editar cliente">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <button type="button" class="action-btn delete" title="Eliminar cliente" onclick="confirmarEliminacion({{ $cliente->id }}, '{{ $cliente->nombre_institucion }}')">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Configurar token CSRF
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Funcionalidad de búsqueda
        document.getElementById('searchInput').addEventListener('input', function() {
            filterTable();
        });
        
        document.getElementById('typeFilter').addEventListener('change', function() {
            filterTable();
        });
        
        function filterTable() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const typeFilter = document.getElementById('typeFilter').value;
            const rows = document.querySelectorAll('#clientesTable tbody tr');
            
            rows.forEach(row => {
                const searchData = row.getAttribute('data-search');
                const typeData = row.getAttribute('data-type');
                
                const matchesSearch = searchData.includes(searchTerm);
                const matchesType = !typeFilter || typeData === typeFilter;
                
                if (matchesSearch && matchesType) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        // Confirmación de eliminación con diseño mejorado
        async function confirmarEliminacion(id, nombre) {
            if (!confirm(`¿Está seguro de eliminar el cliente "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
                return;
            }
            
            try {
                const response = await fetch(`/clientes/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    // Mostrar mensaje de éxito y recargar página
                    alert(`Cliente "${nombre}" eliminado exitosamente`);
                    window.location.reload();
                } else {
                    const errorData = await response.json();
                    alert('Error al eliminar el cliente: ' + (errorData.message || 'Error desconocido'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión al eliminar el cliente');
            }
        }
        
        // Auto-focus en campo de búsqueda
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ Lista de clientes cargada con diseño Bioscom');
            
            // Shortcut para buscar (Ctrl+F o Cmd+F)
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                    e.preventDefault();
                    document.getElementById('searchInput').focus();
                }
            });
        });
    </script>
</body>
</html>