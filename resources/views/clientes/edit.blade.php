<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente - Bioscom CRM</title>
    {{-- Carga los assets de Vite (incluirá Vue y tus componentes) --}}
    @vite('resources/js/app.js')

    {{-- No es necesario cargar estilos aquí si los manejas con Vite/Vue/Bootstrap --}}
</head>
<body>
    {{-- Aquí es donde se montará tu aplicación Vue.js --}}
    <div id="app">
        {{-- Aquí se cargará tu componente ClienteForm.vue --}}
        {{-- El controlador le pasa los datos del cliente existente como 'initialCliente' --}}
        <cliente-form :initial-cliente="{{ $initialCliente->toJson() }}"></cliente-form>
    </div>
</body>
</html>