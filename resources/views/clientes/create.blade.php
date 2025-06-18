@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-4 mb-6 rounded-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    <i class="fas fa-user-plus mr-2 text-bioscom-primary"></i>
                    Nuevo Cliente
                </h1>
                <p class="mt-1 text-sm text-gray-600">Completa la información para registrar un nuevo cliente</p>
            </div>
            <a href="{{ route('clientes.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bioscom-primary transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver a Clientes
            </a>
        </div>
    </div>

    <!-- Componente Vue para crear cliente -->
    <cliente-form></cliente-form>
</div>

<!-- Toast Container -->
<div id="toast-container" class="fixed top-4 right-4 z-50"></div>
@endsection