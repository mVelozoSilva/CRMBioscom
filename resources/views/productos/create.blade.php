<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Producto - Bioscom CRM</title>
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
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="number"], textarea {
            width: calc(100% - 22px); padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;
        }
        textarea { min-height: 80px; resize: vertical; }
        .btn { padding: 10px 15px; background-color: #5f87b8; color: white; text-decoration: none; border-radius: 5px; cursor: pointer; border: none; }
        .btn-back { background-color: #6c757d; margin-left: 10px; }
        .error-message { color: red; font-size: 0.9em; margin-top: 5px; }
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
        <h1>Crear Nuevo Producto</h1>

        @if ($errors->any())
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #f5c6cb;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('productos.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="nombre">Nombre del Producto:</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                @error('nombre') <span class="error-message">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción Detallada:</label>
                <textarea id="descripcion" name="descripcion">{{ old('descripcion') }}</textarea>
                @error('descripcion') <span class="error-message">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="precio_neto">Precio Neto:</label>
                <input type="number" step="0.01" id="precio_neto" name="precio_neto" value="{{ old('precio_neto') }}" required min="0">
                @error('precio_neto') <span class="error-message">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="categoria">Categoría:</label>
                <input type="text" id="categoria" name="categoria" value="{{ old('categoria') }}">
                @error('categoria') <span class="error-message">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="imagenes">Imágenes (JSON de URLs):</label>
                <textarea id="imagenes" name="imagenes" placeholder='["url_imagen1.jpg", "url_imagen2.png"]'>{{ old('imagenes') }}</textarea>
                <small>Ingresa URLs de imágenes como un array JSON. Ej: `["https://ejemplo.com/img1.jpg", "https://ejemplo.com/img2.png"]`</small>
                @error('imagenes') <span class="error-message">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="accesorios">Accesorios (JSON de nombres):</label>
                <textarea id="accesorios" name="accesorios" placeholder='["Accesorio A", "Accesorio B"]'>{{ old('accesorios') }}</textarea>
                <small>Ingresa los accesorios como un array JSON. Ej: `["Cable de alimentación", "Batería extra"]`</small>
                @error('accesorios') <span class="error-message">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="opcionales">Opcionales (JSON de nombres):</label>
                <textarea id="opcionales" name="opcionales" placeholder='["Opción X", "Opción Y"]'>{{ old('opcionales') }}</textarea>
                <small>Ingresa los opcionales como un array JSON. Ej: `["Soporte de pared", "Funda protectora"]`</small>
                @error('opcionales') <span class="error-message">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="btn">Guardar Producto</button>
            <a href="{{ route('productos.index') }}" class="btn btn-back">Volver a Productos</a>
        </form>
    </div>
</body>
</html>