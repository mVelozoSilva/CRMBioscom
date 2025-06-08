<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Bioscom CRM</title>
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
    </style>
</head>
<body>
    <div class="topbar">
        <div class="logo">Bioscom CRM</div>
        <nav>
            <ul>
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('clientes.index') }}">Clientes</a></li>
                <li><a href="{{ route('productos.index') }}" class="active">Productos</a></li> <li><a href="#">Cotizaciones</a></li>
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
        <h1>Gestión de Productos Bioscom</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <a href="{{ route('productos.create') }}" class="btn">Crear Nuevo Producto</a>

        @if($productos->isEmpty())
            <p>No hay productos registrados aún.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio Neto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $producto)
                        <tr>
                            <td>{{ $producto->id }}</td>
                            <td>{{ $producto->nombre }}</td>
                            <td>{{ $producto->categoria ?? 'N/A' }}</td>
                            <td>${{ number_format($producto->precio_neto, 2, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('productos.show', $producto->id) }}" style="color: #6284b8; text-decoration: none; margin-right: 10px;">Ver</a>
                                <a href="{{ route('productos.edit', $producto->id) }}" style="color: #5f87b8; text-decoration: none; margin-right: 10px;">Editar</a>
                                <form action="{{ route('productos.destroy', $producto->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="color: red; background: none; border: none; cursor: pointer; text-decoration: underline;" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?');">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</body>
</html>