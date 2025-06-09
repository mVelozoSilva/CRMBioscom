@extends('layouts.app')

@section('title', 'Módulo de Seguimiento - Bioscom CRM')

@section('content')
<div id="seguimiento-app">
    <seguimiento-table 
        :vendedores='@json($vendedores)'
        csrf-token="{{ csrf_token() }}"
    ></seguimiento-table>
</div>
@endsection

@push('scripts')
<script>
// Variable global para comunicación con Vue
window.seguimientoApp = null;

// Funciones de utilidad
function formatearFecha(fecha) {
    if (!fecha) return '-';
    return new Date(fecha).toLocaleDateString('es-CL');
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('Módulo de Seguimiento iniciado - Bioscom CRM');
});
</script>
@endpush