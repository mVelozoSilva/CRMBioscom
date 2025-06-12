@extends('layouts.app')

@section('title', 'Crear Nuevo Cliente')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-user-plus mr-3 text-blue-600"></i>
                        Crear Nuevo Cliente
                    </h1>
                    <p class="text-gray-600 mt-1">Agrega un nuevo cliente a la base de datos de Bioscom</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('clientes.index') }}" class="btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver a Lista
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <!-- Progreso del Formulario -->
            <div class="bg-blue-50 border-b border-blue-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-medium">1</div>
                            <span class="ml-2 text-sm font-medium text-blue-800">Información Básica</span>
                        </div>
                        <div class="w-12 h-0.5 bg-gray-300"></div>
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">2</div>
                            <span class="ml-2 text-sm text-gray-600">Contacto Principal</span>
                        </div>
                        <div class="w-12 h-0.5 bg-gray-300"></div>
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">3</div>
                            <span class="ml-2 text-sm text-gray-600">Configuración</span>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600">
                        Paso 1 de 3
                    </div>
                </div>
            </div>

            <!-- Formulario Principal -->
            <form action="{{ route('clientes.store') }}" method="POST" id="form-cliente" enctype="multipart/form-data">
                @csrf
                
                <div class="px-6 py-6 space-y-8">
                    
                    <!-- Sección 1: Información de la Institución -->
                    <div class="form-section active" id="seccion-institucion">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                <i class="fas fa-building mr-2 text-blue-600"></i>
                                Información de la Institución
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">Datos básicos de la empresa o institución cliente</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre de Institución -->
                            <div class="md:col-span-2">
                                <label for="nombre_institucion" class="form-label required">
                                    Nombre de la Institución
                                </label>
                                <input
                                    type="text"
                                    name="nombre_institucion"
                                    id="nombre_institucion"
                                    value="{{ old('nombre_institucion') }}"
                                    class="input-field @error('nombre_institucion') border-red-500 @enderror"
                                    placeholder="Ej: Hospital Regional de Antofagasta"
                                    required
                                    maxlength="255"
                                >
                                @error('nombre_institucion')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- RUT -->
                            <div>
                                <label for="rut" class="form-label">
                                    RUT de la Institución
                                </label>
                                <input
                                    type="text"
                                    name="rut"
                                    id="rut"
                                    value="{{ old('rut') }}"
                                    class="input-field @error('rut') border-red-500 @enderror"
                                    placeholder="Ej: 12.345.678-9"
                                    maxlength="12"
                                    onblur="validarRUT(this)"
                                >
                                @error('rut')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                                <p class="help-text">Opcional. Formato: XX.XXX.XXX-X</p>
                            </div>

                            <!-- Tipo de Cliente -->
                            <div>
                                <label for="tipo_cliente" class="form-label required">
                                    Tipo de Cliente
                                </label>
                                <select
                                    name="tipo_cliente"
                                    id="tipo_cliente"
                                    class="input-field @error('tipo_cliente') border-red-500 @enderror"
                                    required
                                    onchange="mostrarInfoTipoCliente()"
                                >
                                    <option value="">Seleccionar tipo...</option>
                                    <option value="Cliente Público" {{ old('tipo_cliente') === 'Cliente Público' ? 'selected' : '' }}>
                                        Cliente Público
                                    </option>
                                    <option value="Cliente Privado" {{ old('tipo_cliente') === 'Cliente Privado' ? 'selected' : '' }}>
                                        Cliente Privado
                                    </option>
                                    <option value="Revendedor" {{ old('tipo_cliente') === 'Revendedor' ? 'selected' : '' }}>
                                        Revendedor
                                    </option>
                                </select>
                                @error('tipo_cliente')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                                
                                <!-- Información del Tipo de Cliente -->
                                <div id="info-tipo-cliente" class="hidden mt-2 p-3 rounded-md text-sm">
                                    <!-- Se llenará dinámicamente con JavaScript -->
                                </div>
                            </div>

                            <!-- Dirección -->
                            <div class="md:col-span-2">
                                <label for="direccion" class="form-label">
                                    Dirección
                                </label>
                                <input
                                    type="text"
                                    name="direccion"
                                    id="direccion"
                                    value="{{ old('direccion') }}"
                                    class="input-field @error('direccion') border-red-500 @enderror"
                                    placeholder="Ej: Av. Argentina 1962, Antofagasta"
                                    maxlength="255"
                                >
                                @error('direccion')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end mt-8">
                            <button type="button" onclick="siguientePaso()" class="btn-primary">
                                <i class="fas fa-arrow-right mr-2"></i>
                                Siguiente: Contacto Principal
                            </button>
                        </div>
                    </div>

                    <!-- Sección 2: Información de Contacto -->
                    <div class="form-section hidden" id="seccion-contacto">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                <i class="fas fa-user mr-2 text-blue-600"></i>
                                Contacto Principal
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">Persona de contacto principal en la institución</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre del Contacto -->
                            <div>
                                <label for="nombre_contacto" class="form-label">
                                    Nombre del Contacto
                                </label>
                                <input
                                    type="text"
                                    name="nombre_contacto"
                                    id="nombre_contacto"
                                    value="{{ old('nombre_contacto') }}"
                                    class="input-field @error('nombre_contacto') border-red-500 @enderror"
                                    placeholder="Ej: Dr. Juan Pérez"
                                    maxlength="255"
                                >
                                @error('nombre_contacto')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="form-label">
                                    Email de Contacto
                                </label>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    value="{{ old('email') }}"
                                    class="input-field @error('email') border-red-500 @enderror"
                                    placeholder="contacto@hospital.cl"
                                    maxlength="255"
                                >
                                @error('email')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Teléfono -->
                            <div>
                                <label for="telefono" class="form-label">
                                    Teléfono de Contacto
                                </label>
                                <input
                                    type="tel"
                                    name="telefono"
                                    id="telefono"
                                    value="{{ old('telefono') }}"
                                    class="input-field @error('telefono') border-red-500 @enderror"
                                    placeholder="+56 9 1234 5678"
                                    maxlength="20"
                                >
                                @error('telefono')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-between mt-8">
                            <button type="button" onclick="pasoAnterior()" class="btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Anterior
                            </button>
                            <button type="button" onclick="siguientePaso()" class="btn-primary">
                                <i class="fas fa-arrow-right mr-2"></i>
                                Siguiente: Configuración
                            </button>
                        </div>
                    </div>

                    <!-- Sección 3: Configuración Avanzada -->
                    <div class="form-section hidden" id="seccion-configuracion">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                <i class="fas fa-cogs mr-2 text-blue-600"></i>
                                Configuración y Asignaciones
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">Configuración de vendedores y notas adicionales</p>
                        </div>

                        <div class="space-y-6">
                            <!-- Vendedores a Cargo -->
                            <div>
                                <label for="vendedores_a_cargo" class="form-label">
                                    Vendedores Asignados
                                </label>
                                <div class="space-y-2">
                                    @foreach($vendedores ?? [] as $vendedor)
                                        <label class="flex items-center">
                                            <input
                                                type="checkbox"
                                                name="vendedores_a_cargo[]"
                                                value="{{ $vendedor->id }}"
                                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                {{ in_array($vendedor->id, old('vendedores_a_cargo', [])) ? 'checked' : '' }}
                                            >
                                            <span class="ml-2 text-sm text-gray-700">{{ $vendedor->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('vendedores_a_cargo')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                                <p class="help-text">Selecciona uno o más vendedores responsables de este cliente</p>
                            </div>

                            <!-- Información Adicional -->
                            <div>
                                <label for="informacion_adicional" class="form-label">
                                    Información Adicional
                                </label>
                                <textarea
                                    name="informacion_adicional"
                                    id="informacion_adicional"
                                    rows="4"
                                    class="input-field @error('informacion_adicional') border-red-500 @enderror"
                                    placeholder="Notas, observaciones especiales, historial previo, etc."
                                >{{ old('informacion_adicional') }}</textarea>
                                @error('informacion_adicional')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Checkbox de Confirmación -->
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                <label class="flex items-start">
                                    <input
                                        type="checkbox"
                                        id="confirmar_datos"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-1"
                                        required
                                    >
                                    <span class="ml-2 text-sm text-blue-800">
                                        <strong>Confirmo que los datos ingresados son correctos</strong><br>
                                        <span class="text-blue-600">
                                            He revisado toda la información y está lista para ser registrada en el sistema.
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-between mt-8">
                            <button type="button" onclick="pasoAnterior()" class="btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Anterior
                            </button>
                            <div class="flex space-x-3">
                                <button type="button" onclick="guardarBorrador()" class="btn-secondary">
                                    <i class="fas fa-save mr-2"></i>
                                    Guardar Borrador
                                </button>
                                <button type="submit" class="btn-primary" id="btn-crear-cliente">
                                    <i class="fas fa-plus mr-2"></i>
                                    Crear Cliente
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>

        <!-- Panel de Ayuda -->
        <div class="mt-8 bg-white rounded-lg shadow-sm p-6">
            <h4 class="text-md font-medium text-gray-900 mb-4 flex items-center">
                <i class="fas fa-lightbulb mr-2 text-yellow-500"></i>
                Consejos para Crear Clientes
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                <div>
                    <h5 class="font-medium text-gray-900 mb-2">Tipos de Cliente:</h5>
                    <ul class="space-y-1">
                        <li><strong>Cliente Público:</strong> Hospitales, consultorios públicos, organismos estatales</li>
                        <li><strong>Cliente Privado:</strong> Clínicas privadas, centros médicos particulares</li>
                        <li><strong>Revendedor:</strong> Distribuidores, representantes comerciales</li>
                    </ul>
                </div>
                <div>
                    <h5 class="font-medium text-gray-900 mb-2">Datos Importantes:</h5>
                    <ul class="space-y-1">
                        <li>• El <strong>nombre de institución</strong> es obligatorio</li>
                        <li>• El RUT es opcional pero recomendado</li>
                        <li>• Puedes asignar múltiples vendedores</li>
                        <li>• La información adicional es útil para el seguimiento</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast de Notificaciones -->
<div id="toast" class="toast hidden">
    <div class="flex items-center">
        <i id="toast-icon" class="mr-2"></i>
        <span id="toast-message"></span>
    </div>
    <button onclick="cerrarToast()" class="ml-4 text-white">
        <i class="fas fa-times"></i>
    </button>
</div>

@endsection

@section('scripts')
<script>
// Variables globales
let pasoActual = 1;
const totalPasos = 3;

// Información de tipos de cliente
const infoTiposCliente = {
    'Cliente Público': {
        color: 'bg-purple-50 border-purple-200 text-purple-800',
        icono: 'fas fa-university',
        descripcion: 'Instituciones del sector público como hospitales públicos, consultorios, organismos estatales.',
        caracteristicas: ['Procesos de compra más largos', 'Requiere licitaciones', 'Facturación centralizada']
    },
    'Cliente Privado': {
        color: 'bg-green-50 border-green-200 text-green-800',
        icono: 'fas fa-building',
        descripcion: 'Clínicas privadas, centros médicos particulares, consultas privadas.',
        caracteristicas: ['Decisiones más rápidas', 'Presupuestos flexibles', 'Relación directa']
    },
    'Revendedor': {
        color: 'bg-orange-50 border-orange-200 text-orange-800',
        icono: 'fas fa-handshake',
        descripcion: 'Distribuidores, representantes comerciales que revenden nuestros productos.',
        caracteristicas: ['Descuentos especiales', 'Volúmenes altos', 'Soporte técnico específico']
    }
};

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    actualizarProgreso();
    
    // Auto-formatear RUT mientras se escribe
    document.getElementById('rut').addEventListener('input', function(e) {
        formatearRUT(e.target);
    });
    
    // Auto-formatear teléfono
    document.getElementById('telefono').addEventListener('input', function(e) {
        formatearTelefono(e.target);
    });
    
    // Validación en tiempo real
    configurarValidacionTiempoReal();
});

// Funciones de navegación entre pasos
function siguientePaso() {
    if (validarPasoActual()) {
        if (pasoActual < totalPasos) {
            pasoActual++;
            mostrarPaso(pasoActual);
            actualizarProgreso();
        }
    }
}

function pasoAnterior() {
    if (pasoActual > 1) {
        pasoActual--;
        mostrarPaso(pasoActual);
        actualizarProgreso();
    }
}

function mostrarPaso(paso) {
    // Ocultar todas las secciones
    document.querySelectorAll('.form-section').forEach(section => {
        section.classList.add('hidden');
        section.classList.remove('active');
    });
    
    // Mostrar sección actual
    const seccionActual = document.getElementById(`seccion-${getNombrePaso(paso)}`);
    if (seccionActual) {
        seccionActual.classList.remove('hidden');
        seccionActual.classList.add('active');
    }
    
    // Scroll al inicio de la sección
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function getNombrePaso(paso) {
    const nombres = {
        1: 'institucion',
        2: 'contacto', 
        3: 'configuracion'
    };
    return nombres[paso] || 'institucion';
}

function actualizarProgreso() {
    // Actualizar indicadores de progreso
    for (let i = 1; i <= totalPasos; i++) {
        const indicator = document.querySelector(`.bg-blue-600, .bg-gray-300`);
        // Aquí actualizarías visualmente el progreso
    }
    
    // Actualizar texto de paso
    const pasoTexto = document.querySelector('.text-sm.text-gray-600');
    if (pasoTexto) {
        pasoTexto.textContent = `Paso ${pasoActual} de ${totalPasos}`;
    }
}

// Validaciones por paso
function validarPasoActual() {
    switch (pasoActual) {
        case 1:
            return validarPaso1();
        case 2:
            return validarPaso2();
        case 3:
            return validarPaso3();
        default:
            return true;
    }
}

function validarPaso1() {
    const nombreInstitucion = document.getElementById('nombre_institucion').value.trim();
    const tipoCliente = document.getElementById('tipo_cliente').value;
    
    if (!nombreInstitucion) {
        mostrarToast('El nombre de la institución es obligatorio', 'error');
        document.getElementById('nombre_institucion').focus();
        return false;
    }
    
    if (!tipoCliente) {
        mostrarToast('Selecciona un tipo de cliente', 'error');
        document.getElementById('tipo_cliente').focus();
        return false;
    }
    
    return true;
}

function validarPaso2() {
    // Validaciones opcionales para paso 2
    const email = document.getElementById('email').value.trim();
    
    if (email && !validarEmail(email)) {
        mostrarToast('El formato del email no es válido', 'error');
        document.getElementById('email').focus();
        return false;
    }
    
    return true;
}

function validarPaso3() {
    const confirmarDatos = document.getElementById('confirmar_datos').checked;
    
    if (!confirmarDatos) {
        mostrarToast('Debes confirmar que los datos son correctos', 'error');
        document.getElementById('confirmar_datos').focus();
        return false;
    }
    
    return true;
}

// Funciones de formateo
function formatearRUT(input) {
    let value = input.value.replace(/[^0-9kK]/g, '');
    
    if (value.length > 1) {
        // Separar dígito verificador
        let rut = value.slice(0, -1);
        let dv = value.slice(-1);
        
        // Formatear RUT con puntos
        rut = rut.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        
        input.value = rut + '-' + dv;
    } else {
        input.value = value;
    }
}

function validarRUT(input) {
    const rut = input.value.trim();
    
    if (rut && !validarFormatoRUT(rut)) {
        mostrarToast('El formato del RUT no es válido', 'warning');
        input.classList.add('border-yellow-500');
        return false;
    } else {
        input.classList.remove('border-yellow-500');
        return true;
    }
}

function validarFormatoRUT(rut) {
    // Validación básica de formato RUT chileno
    const rutRegex = /^\d{1,2}\.\d{3}\.\d{3}-[\dkK]$/;
    return rutRegex.test(rut);
}

function formatearTelefono(input) {
    let value = input.value.replace(/[^0-9\+]/g, '');
    
    // Formateo básico para números chilenos
    if (value.startsWith('569') && value.length === 11) {
        value = '+56 9 ' + value.slice(3, 7) + ' ' + value.slice(7);
    } else if (value.startsWith('9') && value.length === 9) {
        value = '+56 9 ' + value.slice(1, 5) + ' ' + value.slice(5);
    }
    
    input.value = value;
}

function validarEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Mostrar información del tipo de cliente
function mostrarInfoTipoCliente() {
    const tipoCliente = document.getElementById('tipo_cliente').value;
    const infoDiv = document.getElementById('info-tipo-cliente');
    
    if (tipoCliente && infoTiposCliente[tipoCliente]) {
        const info = infoTiposCliente[tipoCliente];
        
        infoDiv.className = `mt-2 p-3 rounded-md text-sm border ${info.color}`;
        infoDiv.innerHTML = `
            <div class="flex items-start">
                <i class="${info.icono} mr-2 mt-0.5"></i>
                <div>
                    <p class="font-medium mb-2">${info.descripcion}</p>
                    <ul class="text-xs space-y-1">
                        ${info.caracteristicas.map(c => `<li>• ${c}</li>`).join('')}
                    </ul>
                </div>
            </div>
        `;
        infoDiv.classList.remove('hidden');
    } else {
        infoDiv.classList.add('hidden');
    }
}

// Configurar validación en tiempo real
function configurarValidacionTiempoReal() {
    // Validar nombre de institución
    document.getElementById('nombre_institucion').addEventListener('blur', function() {
        if (!this.value.trim()) {
            this.classList.add('border-red-500');
        } else {
            this.classList.remove('border-red-500');
        }
    });
    
    // Validar email
    document.getElementById('email').addEventListener('blur', function() {
        const email = this.value.trim();
        if (email && !validarEmail(email)) {
            this.classList.add('border-red-500');
        } else {
            this.classList.remove('border-red-500');
        }
    });
}

// Funciones de guardado
function guardarBorrador() {
    mostrarToast('Función de borrador en desarrollo', 'info');
}

// Envío del formulario
document.getElementById('form-cliente').addEventListener('submit', function(e) {
    if (!validarFormularioCompleto()) {
        e.preventDefault();
        return false;
    }
    
    // Mostrar loading en el botón
    const btnCrear = document.getElementById('btn-crear-cliente');
    btnCrear.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creando Cliente...';
    btnCrear.disabled = true;
});

function validarFormularioCompleto() {
    // Validar todos los pasos
    for (let i = 1; i <= totalPasos; i++) {
        const pasoOriginal = pasoActual;
        pasoActual = i;
        
        if (!validarPasoActual()) {
            pasoActual = pasoOriginal;
            mostrarPaso(i);
            return false;
        }
        
        pasoActual = pasoOriginal;
    }
    
    return true;
}

// Sistema de notificaciones Toast
function mostrarToast(mensaje, tipo = 'info') {
    const toast = document.getElementById('toast');
    const icon = document.getElementById('toast-icon');
    const messageElement = document.getElementById('toast-message');
    
    // Configurar icono y clases según el tipo
    toast.className = 'toast fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 flex items-center text-white transition-all duration-300';
    
    switch(tipo) {
        case 'success':
            toast.classList.add('bg-green-500');
            icon.className = 'fas fa-check-circle mr-2';
            break;
        case 'error':
            toast.classList.add('bg-red-500');
            icon.className = 'fas fa-exclamation-circle mr-2';
            break;
        case 'warning':
            toast.classList.add('bg-yellow-500');
            icon.className = 'fas fa-exclamation-triangle mr-2';
            break;
        default:
            toast.classList.add('bg-blue-500');
            icon.className = 'fas fa-info-circle mr-2';
    }
    
    messageElement.textContent = mensaje;
    toast.classList.remove('hidden');
    
    // Auto-ocultar después de 5 segundos
    setTimeout(() => {
        cerrarToast();
    }, 5000);
}

function cerrarToast() {
    const toast = document.getElementById('toast');
    toast.classList.add('hidden');
}

// Navegación con teclado
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case 'ArrowRight':
                e.preventDefault();
                siguientePaso();
                break;
            case 'ArrowLeft':
                e.preventDefault();
                pasoAnterior();
                break;
            case 'Enter':
                if (e.shiftKey) {
                    e.preventDefault();
                    if (pasoActual === totalPasos) {
                        document.getElementById('form-cliente').submit();
                    } else {
                        siguientePaso();
                    }
                }
                break;
        }
    }
});
</script>
@endsection