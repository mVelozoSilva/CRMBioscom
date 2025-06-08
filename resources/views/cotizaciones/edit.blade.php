<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cotización - Bioscom CRM</title>
    @vite('resources/js/app.js')
    {{-- Aquí puedes añadir CSS adicional si lo necesitas --}}
</head>
<body>
    <div id="app">
        <cotizacion-form :initial-cotizacion="{{ $initialCotizacion->toJson() }}"></cotizacion-form>
    </div>
</body>
</html>