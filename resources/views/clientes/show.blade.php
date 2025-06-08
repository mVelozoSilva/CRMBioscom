<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Cliente - Bioscom CRM</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f6fa; color: #00334e; margin: 20px; }
        .container { background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); max-width: 600px; margin: 0 auto; }
        h1 { color: #6284b8; text-align: center; margin-bottom: 30px; }
        .detail-item { margin-bottom: 10px; }
        .detail-item strong { display: inline-block; width: 100px; }
        .btn { padding: 10px 15px; background-color: #5f87b8; color: white; text-decoration: none; border-radius: 5px; cursor: pointer; border: none; }
        .btn-back { background-color: #6c757d; margin-left: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Detalle del Cliente</h1>

        <div class="detail-item">
            <strong>ID:</strong> {{ $cliente->id }}
        </div>
        <div class="detail-item">
            <strong>Nombre:</strong> {{ $cliente->nombre }}
        </div>
        <div class="detail-item">
            <strong>Email:</strong> {{ $cliente->email }}
        </div>
        <div class="detail-item">
            <strong>Teléfono:</strong> {{ $cliente->telefono ?? 'N/A' }}
        </div>
        <div class="detail-item">
            <strong>Dirección:</strong> {{ $cliente->direccion ?? 'N/A' }}
        </div>
        <div class="detail-item">
            <strong>Creado:</strong> {{ $cliente->created_at->format('d/m/Y H:i') }}
        </div>
        <div class="detail-item">
            <strong>Última Actualización:</strong> {{ $cliente->updated_at->format('d/m/Y H:i') }}
        </div>

        <a href="{{ route('clientes.index') }}" class="btn btn-back">Volver a Clientes</a>
        <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn">Editar Cliente</a>
    </div>
</body>
</html>