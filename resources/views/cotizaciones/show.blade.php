<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Cotización - Bioscom CRM</title>
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
        .detail-section { margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .detail-section-title { color: #6284b8; margin-top: 0; margin-bottom: 15px; font-size: 1.3em; }
        .detail-item { margin-bottom: 10px; display: flex; align-items: flex-start; }
        .detail-item strong { display: inline-block; width: 180px; flex-shrink: 0; }
        .detail-item span { flex-grow: 1; }
        .product-list-item { border: 1px solid #eee; padding: 15px; margin-bottom: 15px; background-color: #f9f9f9; border-radius: 5px; }
        .product-list-item h4 { color: #00334e; margin-top: 0; margin-bottom: 10px; }
        .product-list-item div { margin-bottom: 5px; }
        .product-list-item strong { display: inline-block; width: 120px; }
        .image-gallery { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
        .image-gallery img { max-width: 100px; height: auto; border: 1px solid #ddd; border-radius: 4px; }
        .list-group { margin-top: 5px; }
        .list-group-item { margin-left: 20px; list-style: disc; }
        .totals-summary { text-align: right; margin-top: 30px; padding-top: 20px; border-top: 2px solid #6284b8; }
        .totals-summary div { margin-bottom: 8px; font-size: 1.1em; }
        .totals-summary .final-total { font-size: 1.8em; font-weight: bold; color: #00334e; }
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
        <h1>Detalle de Cotización: {{ $cotizacion->nombre_cotizacion ?? 'N/A' }}</h1> {{-- Usar nombre_cotizacion aquí --}}
        <p style="text-align: center; color: #6c757d;">Código: {{ $cotizacion->codigo ?? 'N/A' }}</p>

        <div class="detail-section">
            <h2 class="detail-section-title">Información General</h2>
            <div class="detail-item"><strong>Código:</strong> <span>{{ $cotizacion->codigo ?? 'N/A' }}</span></div>
            <div class="detail-item"><strong>Nombre Cotización:</strong> <span>{{ $cotizacion->nombre_cotizacion ?? 'N/A' }}</span></div>
            <div class="detail-item"><strong>Institución Cliente:</strong> <span>{{ $cotizacion->cliente->nombre_institucion ?? 'N/A' }}</span></div>
            <div class="detail-item"><strong>RUT Cliente:</strong> <span>{{ $cotizacion->cliente->rut ?? 'N/A' }}</span></div>
            <div class="detail-item"><strong>Contacto:</strong> <span>{{ $cotizacion->nombre_contacto ?? 'N/A' }}</span></div>
            {{-- Email y Teléfono del Cliente Relacionado --}}
            <div class="detail-item"><strong>Email Contacto:</strong> <span>{{ $cotizacion->cliente->email ?? 'N/A' }}</span></div>
            <div class="detail-item"><strong>Teléfono Contacto:</strong> <span>{{ $cotizacion->cliente->telefono ?? 'N/A' }}</span></div>
            <div class="detail-item"><strong>Vendedor:</strong> <span>{{ $cotizacion->info_contacto_vendedor ?? 'N/A' }}</span></div>
            <div class="detail-item"><strong>Validez de Oferta:</strong>
                <span>
                    @if($cotizacion->validez_oferta)
                        {{ $cotizacion->validez_oferta->format('d/m/Y') }}
                    @else
                        N/A
                    @endif
                </span>
            </div>
            <div class="detail-item"><strong>Estado:</strong> <span>{{ $cotizacion->estado }}</span></div>
           <div class="detail-item"><strong>Fecha Creación:</strong>
                <span>
                    @if($cotizacion->created_at)
                        {{ $cotizacion->created_at->format('d/m/Y H:i') }}
                    @else
                        N/A
                    @endif
                </span>
            </div>

        <div class="detail-section">
            <h2 class="detail-section-title">Productos Cotizados</h2>
            <div class="detail-section">
    <h2 class="detail-section-title">Productos Cotizados</h2>
    {{-- Decodificar productos_cotizados explícitamente, ya que el cast parece fallar --}}
    @php
        $productosDecodificados = is_string($cotizacion->productos_cotizados)
                                ? json_decode($cotizacion->productos_cotizados, true)
                                : $cotizacion->productos_cotizados;

        // Asegurarse de que $productosDecodificados sea un array incluso si la decodificación falla o es null
        if (!is_array($productosDecodificados)) {
            $productosDecodificados = [];
        }
    @endphp

    @if(count($productosDecodificados) > 0)
        <table class="product-table">
            <thead>
                <tr>
                    <th>ID Producto</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio Unitario</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productosDecodificados as $item) {{-- Ahora iteramos sobre los productos decodificados --}}
                    <tr>
                        <td>{{ $item['id_producto'] ?? 'N/A' }}</td>
                        <td>{{ $item['nombre'] ?? 'N/A' }}</td>
                        <td>{{ $item['descripcion_corta'] ?? 'N/A' }}</td>
                        <td>${{ number_format($item['precio_unitario'] ?? 0, 2, ',', '.') }}</td>
                        <td>{{ $item['cantidad'] ?? 0 }}</td>
                        <td>${{ number_format($item['subtotal'] ?? 0, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No se especificaron productos para esta cotización.</p>
    @endif

            <div class="totals-summary">
                <div>Total Neto: ${{ number_format($cotizacion->total_neto, 2, ',', '.') }}</div>
                <div>IVA (19%): ${{ number_format($cotizacion->iva, 2, ',', '.') }}</div>
                <div class="final-total">Total con IVA: ${{ number_format($cotizacion->total_con_iva, 2, ',', '.') }}</div>
            </div>
        </div>

        <div class="detail-section">
            <h2 class="detail-section-title">Condiciones Comerciales y Legales</h2>
            <div class="detail-item"><strong>Forma de Pago:</strong> <span>{{ $cotizacion->forma_pago ?? 'N/A' }}</span></div>
            <div class="detail-item"><strong>Plazo de Entrega:</strong> <span>{{ $cotizacion->plazo_entrega ?? 'N/A' }}</span></div>
            <div class="detail-item"><strong>Garantía Técnica:</strong> <span>{{ $cotizacion->garantia_tecnica ?? 'N/A' }}</span></div>
            <div class="detail-item"><strong>Información Adicional:</strong> <span>{{ $cotizacion->informacion_adicional ?? 'N/A' }}</span></div>
            <div class="detail-item"><strong>Descripción Opcionales:</strong> <span>{{ $cotizacion->descripcion_opcionales ?? 'N/A' }}</span></div>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ route('cotizaciones.index') }}" class="btn btn-back">Volver al Listado</a>
            {{-- Aquí irían los botones de Editar y Descargar PDF más adelante --}}
        </div>
    </div>
</body>
</html>