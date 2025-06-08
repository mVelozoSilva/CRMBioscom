<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotizaciones - Bioscom CRM</title>
    {{-- Mantenemos tus estilos internos por ahora --}}
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
        .main-content { flex-grow: 1; padding: 30px; max-width: 1200px; margin: 30px auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #00334e; text-align: center; margin-bottom: 30px; }
        .btn { display: inline-block; padding: 10px 15px; background-color: #5f87b8; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #5f87b8; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .alert { padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
        }
        .status-Pendiente { background-color: #ffc107; color: #343a40; } /* Amarillo para pendiente */
        .status-Enviada { background-color: #17a2b8; } /* Azul claro para enviada */
        .status-Ganada { background-color: #28a745; } /* Verde para ganada */
        .status-Perdida { background-color: #dc3545; } /* Rojo para perdida */
        .actions-column { width: 180px; text-align: center; } /* Ajustar ancho de la columna de acciones */
    </style>
</head>
<body>
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
        <h1>Gestión de Cotizaciones Bioscom</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <a href="{{ route('cotizaciones.create') }}" class="btn">Crear Nueva Cotización</a>

        @if($cotizaciones->isEmpty())
            <p>No hay cotizaciones registradas aún.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre Cotización</th> {{-- Nueva columna para el nombre que ya guardamos --}}
                        <th>Institución Cliente</th> {{-- Nombre de la institución del cliente --}}
                        <th>Contacto Cliente</th>    {{-- Nombre de contacto del cliente --}}
                        <th>Total c/IVA</th>
                        <th>Validez</th>
                        <th>Estado</th>
                        <th class="actions-column">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cotizaciones as $cotizacion)
                        <tr>
                            <td>{{ $cotizacion->codigo }}</td> {{-- Si 'codigo' se autogenera o es null, puede estar vacío --}}
                            <td>{{ $cotizacion->nombre_cotizacion }}</td> {{-- Mostrar el nuevo campo --}}
                            {{-- Para la institución cliente, accedemos a la relación con Cliente --}}
                            <td>{{ $cotizacion->cliente->nombre_institucion ?? 'N/A' }}</td>
                            {{-- Para el contacto del cliente --}}
                            <td>{{ $cotizacion->cliente->nombre_contacto ?? 'N/A' }}</td>
                            <td>${{ number_format($cotizacion->total_con_iva, 2, ',', '.') }}</td>
                            <td>{{ $cotizacion->validez_oferta ? $cotizacion->validez_oferta->format('d/m/Y') : 'N/A' }}</td>
                            <td><span class="status-badge status-{{ $cotizacion->estado }}">{{ $cotizacion->estado }}</span></td>
                            <td>
                                <a href="{{ route('cotizaciones.show', $cotizacion->id) }}" style="color: #6284b8; text-decoration: none; margin-right: 10px;">Ver</a>
                                <a href="{{ route('cotizaciones.edit', $cotizacion->id) }}" style="color: #5f87b8; text-decoration: none; margin-right: 10px;">Editar</a>
                                <a href="#" style="color: red; text-decoration: none;">Eliminar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</body>
</html>