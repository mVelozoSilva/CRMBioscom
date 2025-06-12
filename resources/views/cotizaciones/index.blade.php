<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        .btn:hover { background-color: #4a6b96; color: white; text-decoration: none; }
        .btn-danger { background-color: #dc3545; }
        .btn-danger:hover { background-color: #c82333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #5f87b8; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .alert { padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
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
        .action-btn { color: #6284b8; text-decoration: none; margin-right: 10px; padding: 4px 8px; border-radius: 3px; font-size: 0.9em; }
        .action-btn:hover { background-color: #f8f9fa; }
        .action-btn.delete { color: #dc3545; }
        .action-btn.delete:hover { background-color: #f8d7da; }
        .btn-confirm { padding: 8px 12px; margin: 0 5px; font-size: 0.85em; border: none; border-radius: 4px; cursor: pointer; }
        .btn-confirm-yes { background-color: #dc3545; color: white; }
        .btn-confirm-no { background-color: #6c757d; color: white; }
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

        {{-- Mensajes de éxito y error --}}
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
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
                        <th>Nombre Cotización</th>
                        <th>Institución Cliente</th>
                        <th>Contacto Cliente</th>
                        <th>Total c/IVA</th>
                        <th>Validez</th>
                        <th>Estado</th>
                        <th class="actions-column">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cotizaciones as $cotizacion)
                        <tr id="cotizacion-{{ $cotizacion->id }}">
                            <td>{{ $cotizacion->codigo }}</td>
                            <td>{{ $cotizacion->nombre_cotizacion }}</td>
                            <td>{{ $cotizacion->cliente->nombre_institucion ?? 'N/A' }}</td>
                            <td>{{ $cotizacion->cliente->nombre_contacto ?? 'N/A' }}</td>
                            <td>${{ number_format($cotizacion->total_con_iva, 2, ',', '.') }}</td>
                            <td>{{ $cotizacion->validez_oferta ? $cotizacion->validez_oferta->format('d/m/Y') : 'N/A' }}</td>
                            <td><span class="status-badge status-{{ $cotizacion->estado }}">{{ $cotizacion->estado }}</span></td>
                            <td>
                                <a href="{{ route('cotizaciones.show', $cotizacion->id) }}" class="action-btn">Ver</a>
                                <a href="{{ route('cotizaciones.edit', $cotizacion->id) }}" class="action-btn">Editar</a>
                                
                                {{-- 🚀 BOTÓN ELIMINAR FUNCIONAL --}}
                                <span id="delete-{{ $cotizacion->id }}">
                                    <button type="button" class="action-btn delete" onclick="confirmDelete({{ $cotizacion->id }}, '{{ $cotizacion->nombre_cotizacion }}')">
                                        Eliminar
                                    </button>
                                </span>
                                
                                {{-- Confirmación de eliminación (inicialmente oculta) --}}
                                <span id="confirm-{{ $cotizacion->id }}" style="display: none;">
                                    <button type="button" class="btn-confirm btn-confirm-yes" onclick="deleteCotizacion({{ $cotizacion->id }})">
                                        ¿Eliminar?
                                    </button>
                                    <button type="button" class="btn-confirm btn-confirm-no" onclick="cancelDelete({{ $cotizacion->id }})">
                                        Cancelar
                                    </button>
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- 🚀 JAVASCRIPT FUNCIONAL PARA ELIMINAR --}}
    <script>
        // Configurar token CSRF para todas las peticiones AJAX
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        function confirmDelete(id, nombre) {
            console.log('🗑️ Confirmando eliminación de:', nombre);
            
            // Ocultar botón eliminar y mostrar confirmación
            document.getElementById('delete-' + id).style.display = 'none';
            document.getElementById('confirm-' + id).style.display = 'inline';
        }
        
        function cancelDelete(id) {
            console.log('❌ Cancelando eliminación');
            
            // Ocultar confirmación y mostrar botón eliminar
            document.getElementById('confirm-' + id).style.display = 'none';
            document.getElementById('delete-' + id).style.display = 'inline';
        }
        
        async function deleteCotizacion(id) {
            console.log('🗑️ Eliminando cotización ID:', id);
            
            try {
                const response = await fetch(`/cotizaciones/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    console.log('✅ Cotización eliminada exitosamente');
                    
                    // Eliminar fila de la tabla con animación
                    const row = document.getElementById('cotizacion-' + id);
                    row.style.transition = 'opacity 0.3s ease';
                    row.style.opacity = '0';
                    
                    setTimeout(() => {
                        row.remove();
                        showMessage('Cotización eliminada exitosamente.', 'success');
                    }, 300);
                    
                } else {
                    const errorData = await response.json();
                    console.error('❌ Error del servidor:', errorData);
                    showMessage(errorData.message || 'Error al eliminar la cotización.', 'error');
                    cancelDelete(id);
                }
                
            } catch (error) {
                console.error('❌ Error de red:', error);
                showMessage('Error de conexión. Inténtalo de nuevo.', 'error');
                cancelDelete(id);
            }
        }
        
        function showMessage(message, type) {
            // Crear div de mensaje
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'}`;
            alertDiv.textContent = message;
            
            // Insertar al inicio del contenido principal
            const mainContent = document.querySelector('.main-content');
            mainContent.insertBefore(alertDiv, mainContent.firstChild.nextSibling);
            
            // Remover mensaje después de 5 segundos
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
        
        // Debug: Confirmar que el script se cargó
        console.log('✅ Script de eliminación cargado correctamente');
    </script>
</body>
</html>