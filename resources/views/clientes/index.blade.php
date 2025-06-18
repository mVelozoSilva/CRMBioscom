@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Componente Vue moderno para listado de clientes -->
    <cliente-table></cliente-table>
</div>

<!-- Toast Container -->
<div id="toast-container" class="fixed top-4 right-4 z-50"></div>
@endsection