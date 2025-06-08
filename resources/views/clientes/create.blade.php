<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cliente - Bioscom CRM</title>
    {{-- Carga los assets de Vite (incluirá Vue y tus componentes) --}}
    @vite('resources/js/app.js')
    
    {{-- Opcional: Puedes añadir estilos CSS específicos si los necesitas,
         por ejemplo, si tienes un archivo CSS para tus estilos globales:
         @vite('resources/css/app.css')
    --}}

    {{-- Si quieres mantener los estilos que tenías en el head para un diseño básico,
         tendrías que moverlos a un archivo CSS y cargarlo con Vite o en tu app.js.
         Por ahora, el componente Vue ya tiene sus propios estilos o usa Bootstrap.
         Considera que la mayoría de estos estilos serán manejados por Bootstrap
         o por tus propios estilos en el ecosistema Vue.
    --}}
</head>
<body>
    {{-- Aquí es donde se montará tu aplicación Vue.js --}}
    <div id="app">
        {{-- Aquí se cargará tu componente ClienteForm.vue --}}
        <cliente-form></cliente-form>
    </div>
</body>
</html>