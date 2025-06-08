<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes de Bioscom CRM</title>
    {{-- Mantendremos tus estilos internos por ahora para que el listado se vea bien --}}
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f6fa; color: #00334e; margin: 20px; }
        .container { background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); max-width: 900px; margin: 0 auto; }
        h1 { color: #6284b8; text-align: center; margin-bottom: 30px; }
        .btn {
            display: inline-block; padding: 10px 15px; background-color: #5f87b8; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 20px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #5f87b8; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .alert { padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        /* Añadir estilos para las columnas nuevas si es necesario */
        .actions-column { width: 150px; text-align: center; } /* Ajustar ancho de la columna de acciones */
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestión de Clientes Bioscom</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <a href="{{ route('clientes.create') }}" class="btn">Crear Nuevo Cliente</a>

        @if($clientes->isEmpty())
            <p>No hay clientes registrados aún.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Institución</th> {{-- Cambiado de "Nombre" a "Institución" --}}
                        <th>RUT</th>          {{-- Nueva columna --}}
                        <th>Tipo</th>         {{-- Nueva columna --}}
                        <th>Contacto Principal</th> {{-- Nueva columna para nombre de contacto --}}
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th class="actions-column">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clientes as $cliente)
                        <tr>
                            <td>{{ $cliente->id }}</td>
                            <td>{{ $cliente->nombre_institucion }}</td> {{-- USAR EL NUEVO NOMBRE DE COLUMNA --}}
                            <td>{{ $cliente->rut }}</td>                 {{-- Mostrar RUT --}}
                            <td>{{ $cliente->tipo_cliente }}</td>       {{-- Mostrar Tipo de Cliente --}}
                            <td>{{ $cliente->nombre_contacto }}</td>    {{-- Mostrar Nombre de Contacto --}}
                            <td>{{ $cliente->email }}</td>
                            <td>{{ $cliente->telefono }}</td>
                            <td>{{ $cliente->direccion }}</td>
                            <td class="actions-column">
                                <a href="{{ route('clientes.show', $cliente->id) }}" style="color: #6284b8; text-decoration: none; margin-right: 10px;">Ver</a>
                                <a href="{{ route('clientes.edit', $cliente->id) }}" style="color: #5f87b8; text-decoration: none; margin-right: 10px;">Editar</a>
                                <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="color: red; background: none; border: none; cursor: pointer; text-decoration: underline;">Eliminar</button>
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
