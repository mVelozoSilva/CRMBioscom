<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Producto - Bioscom CRM</title>
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
        .main-content { flex-grow: 1; padding: 30px; max-width: 800px; margin: 30px auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #00334e; text-align: center; margin-bottom: 30px; }
        .detail-item { margin-bottom: 10px; display: flex; align-items: flex-start; }
        .detail-item strong { display: inline-block; width: 150px; flex-shrink: 0; }
        .detail-item span { flex-grow: 1; }
        .image-gallery { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; }
        .image-gallery img { max-width: 150px; height: auto; border: 1px solid #ddd; border-radius: 4px; }
        .list-group { margin-top: 5px; }
        .list-group-item { margin-left: 20px; list-style: disc; }
        .btn { padding: 10px 15px; background-color: #5f87b8; color: white; text-decoration: none; border-radius: 5px; cursor: pointer; border: none; margin-top: 20px;}
        .btn-back { background-color: #6c757d; margin-left: 10px; }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="logo">Bioscom CRM</div>
        <nav>
            <ul>
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('clientes.index') }}">Clientes</a></li>
                <li><a href="{{ route('productos.index') }}" class="active">Productos</a></li>
                <li><a href="#">Cotizaciones</a></li>
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
        <h1>Detalle del Producto: {{ $producto->nombre }}</h1>

        <div class="detail-item">
            <strong>ID:</strong> <span>{{ $producto->id }}</span>
        </div>
        <div class="detail-item">
            <strong>Nombre:</strong> <span>{{ $producto->nombre }}</span>
        </div>
        <div class="detail-item">
            <strong>Categoría:</strong> <span>{{ $producto->categoria ?? 'N/A' }}</span>
        </div>
        <div class="detail-item">
            <strong>Precio Neto:</strong> <span>${{ number_format($producto->precio_neto, 2, ',', '.') }}</span>
        </div>
        <div class="detail-item">
            <strong>Descripción Detallada:</strong> <span>{{ $producto->descripcion ?? 'Sin descripción' }}</span>
        </div>

        @if($producto->imagenes)
            <div class="detail-item">
                <strong>Imágenes:</strong>
                <div class="image-gallery">
                    @foreach($producto->imagenes as $imagen)
                        <img src="{{ $imagen }}" alt="Imagen de {{ $producto->nombre }}" loading="lazy">
                    @endforeach
                </div>
            </div>
        @endif

        @if($producto->accesorios)
            <div class="detail-item">
                <strong>Accesorios:</strong>
                <ul class="list-group">
                    @foreach($producto->accesorios as $accesorio)
                        <li class="list-group-item">{{ $accesorio }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($producto->opcionales)
            <div class="detail-item">
                <strong>Opcionales:</strong>
                <ul class="list-group">
                    @foreach($producto->opcionales as $opcional)
                        <li class="list-group-item">{{ $opcional }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="detail-item">
            <strong>Creado:</strong> <span>{{ $producto->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="detail-item">
            <strong>Última Actualización:</strong> <span>{{ $producto->updated_at->format('d/m/Y H:i') }}</span>
        </div>

        <a href="{{ route('productos.index') }}" class="btn btn-back">Volver a Productos</a>
        <a href="{{ route('productos.edit', $producto->id) }}" class="btn">Editar Producto</a>
    </div>
</body>
</html>