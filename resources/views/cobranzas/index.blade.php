@extends('layouts.app')

@section('title', 'Gestión de Cobranzas')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header del Módulo -->
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

           <div class="flex flex-wrap justify-between items-start py-6">
            <div class="flex flex-col">
                <h1 class="heading-bioscom-1 text-2xl font-bold text-gray-900">
                    Cobranza
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Gestiona y recupera cuentas por cobrar de manera eficiente
                </p>
            </div>

            <button
                class="btn-bioscom-primary mt-4 sm:mt-0"
                onclick="document.getElementById('app').__vue_app__?._instance?.refs?.cobranzaTable?.abrirFormulario(null)"
            >
                ➕ Nueva Cobranza
            </button>
        </div>

    </div>
</div>

    <!-- Contenido Principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Componente Vue de Tabla de Cobranzas -->
        <div id="app">
            <cobranza-table ref="cobranzaTable"></cobranza-table>
        </div>
    </div>
</div>
@endsection