<template>
    <div class="container mt-4">
        <!-- Mensaje de debug -->
        <div class="alert alert-info">
            <strong>Debug:</strong> Componente Vue montado correctamente ✅
        </div>
        
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">{{ isEditing ? 'Editar Cotización' : 'Crear Nueva Cotización' }}</h3>
            </div>
            <div class="card-body">
                <!-- Mensajes de alerta -->
                <div v-if="generalError" class="alert alert-danger">
                    {{ generalError }}
                </div>
                <div v-if="successMessage" class="alert alert-success">
                    {{ successMessage }}
                </div>

                <form @submit.prevent="submitForm">
                    <!-- Información General -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">📋 Información General</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Nombre de la Cotización <span class="text-danger">*</span></label>
                                        <input 
                                            type="text" 
                                            class="form-control" 
                                            v-model="form.nombre_cotizacion"
                                            placeholder="Ej: Equipamiento UCI - Hospital Regional"
                                            required
                                        >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Código de Cotización (Opcional)</label>
                                        <input 
                                            type="text" 
                                            class="form-control" 
                                            v-model="form.codigo"
                                            placeholder="Se generará automáticamente si se deja vacío"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Buscar Cliente -->
                            <div class="form-group mb-3">
                                <label class="form-label">Buscar Cliente <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input 
                                        type="text" 
                                        class="form-control"
                                        v-model="clienteSearchTerm"
                                        @input="searchClientes"
                                        @focus="showClienteDropdown = true"
                                        placeholder="Escriba nombre de institución o RUT..."
                                        autocomplete="off"
                                    >
                                    
                                    <!-- Dropdown de resultados de clientes -->
                                    <div v-if="showClienteDropdown && clienteResults.length > 0" 
                                         class="dropdown-menu show w-100 mt-1" 
                                         style="max-height: 200px; overflow-y: auto;">
                                        <button 
                                            type="button"
                                            class="dropdown-item"
                                            v-for="cliente in clienteResults" 
                                            :key="cliente.id"
                                            @click="selectCliente(cliente)"
                                        >
                                            <strong>{{ cliente.nombre_institucion }}</strong><br>
                                            <small class="text-muted">{{ cliente.rut }} • {{ cliente.nombre_contacto }}</small>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Información del Cliente Seleccionado -->
                            <div v-if="selectedCliente" class="alert alert-info">
                                <h6><strong>Cliente Seleccionado:</strong></h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Institución:</strong> {{ selectedCliente.nombre_institucion }}<br>
                                        <strong>RUT:</strong> {{ selectedCliente.rut }}<br>
                                        <strong>Contacto:</strong> {{ selectedCliente.nombre_contacto }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Email:</strong> {{ selectedCliente.email }}<br>
                                        <strong>Teléfono:</strong> {{ selectedCliente.telefono }}<br>
                                        <strong>Tipo:</strong> {{ selectedCliente.tipo_cliente }}
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" @click="clearCliente">
                                    Cambiar Cliente
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Productos -->
                    <div class="card mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">📦 Productos</h5>
                            <button type="button" class="btn btn-success btn-sm" @click="addProduct">
                                ➕ Añadir Producto
                            </button>
                        </div>
                        <div class="card-body">
                            <div v-if="form.productos_cotizados.length === 0" class="text-center text-muted py-4">
                                <p>No hay productos añadidos. Haz clic en "Añadir Producto" para comenzar.</p>
                            </div>

                            <!-- Lista de productos -->
                            <div v-for="(producto, index) in form.productos_cotizados" :key="index" class="card mb-3 border-secondary">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Producto #{{ index + 1 }}</h6>
                                    <button type="button" class="btn btn-danger btn-sm" @click="removeProduct(index)">
                                        🗑️ Eliminar
                                    </button>
                                </div>
                                <div class="card-body">
                                    <!-- Buscar Producto -->
                                    <div class="form-group mb-3">
                                        <label class="form-label">Buscar Producto <span class="text-danger">*</span></label>
                                        <div class="position-relative">
                                            <input 
                                                type="text" 
                                                class="form-control"
                                                v-model="producto.searchTerm"
                                                @input="searchProductos(index)"
                                                placeholder="Escriba nombre o categoría del producto..."
                                                autocomplete="off"
                                            >
                                            
                                            <!-- Dropdown de resultados de productos -->
                                            <div v-if="producto.showDropdown && producto.results && producto.results.length > 0" 
                                                 class="dropdown-menu show w-100 mt-1" 
                                                 style="max-height: 200px; overflow-y: auto;">
                                                <button 
                                                    type="button"
                                                    class="dropdown-item"
                                                    v-for="prod in producto.results" 
                                                    :key="prod.id"
                                                    @click="selectProducto(index, prod)"
                                                >
                                                    <strong>{{ prod.nombre }}</strong><br>
                                                    <small class="text-muted">{{ prod.categoria }} • ${{ formatNumber(prod.precio_neto) }}</small>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Información del producto seleccionado -->
                                    <div v-if="producto.id_producto" class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Nombre de Producto</label>
                                                <input type="text" class="form-control" v-model="producto.nombre_producto" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Descripción Corta (editable)</label>
                                                <textarea class="form-control" v-model="producto.descripcion_corta" rows="2"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Precio Unitario (editable) <span class="text-danger">*</span></label>
                                                <input 
                                                    type="number" 
                                                    step="0.01"
                                                    class="form-control" 
                                                    v-model="producto.precio_unitario"
                                                    @input="updateTotals"
                                                    required
                                                >
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Cantidad Solicitada <span class="text-danger">*</span></label>
                                                <input 
                                                    type="number" 
                                                    min="1"
                                                    class="form-control" 
                                                    v-model="producto.cantidad"
                                                    @input="updateTotals"
                                                    required
                                                >
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Subtotal</label>
                                                <input 
                                                    type="text" 
                                                    class="form-control bg-light" 
                                                    :value="'$' + formatNumber(producto.precio_unitario * producto.cantidad)"
                                                    readonly
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Resumen de Precios -->
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>💰 Resumen de Precios</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Total Neto: ${{ formatNumber(totales.neto) }}</strong>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>IVA (19%): ${{ formatNumber(totales.iva) }}</strong>
                                        </div>
                                        <div class="col-md-4">
                                            <strong class="text-primary">Total con IVA: ${{ formatNumber(totales.total) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Condiciones Comerciales -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">📋 Condiciones Comerciales y Legales</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Validez de Oferta <span class="text-danger">*</span></label>
                                        <select class="form-control" v-model="form.validez_oferta" required>
                                            <option value="">Seleccione...</option>
                                            <option value="30">30 días corridos</option>
                                            <option value="60">60 días corridos</option>
                                            <option value="90">90 días corridos</option>
                                            <option value="120">120 días corridos</option>
                                            <option value="150">150 días corridos</option>
                                            <option value="180">180 días corridos</option>
                                            <option value="240">240 días corridos</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Forma de Pago</label>
                                        <select class="form-control" v-model="form.forma_pago">
                                            <option value="">Seleccione...</option>
                                            <option value="OC 30 días">Orden de Compra a 30 días</option>
                                            <option value="OC 45 días">Orden de Compra a 45 días</option>
                                            <option value="Contado transferencia">Pago al contado con transferencia</option>
                                            <option value="15% orden, saldo entrega">15% con la orden, saldo contra entrega</option>
                                            <option value="30% orden, saldo entrega">30% con la orden, saldo contra entrega</option>
                                            <option value="50% orden, saldo entrega">50% con la orden, saldo contra entrega</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Plazo de Entrega</label>
                                        <input type="text" class="form-control" v-model="form.plazo_entrega" placeholder="Ej: 15 días hábiles">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Garantía Técnica</label>
                                        <select class="form-control" v-model="form.garantia_tecnica">
                                            <option value="">Seleccione...</option>
                                            <option value="6 meses">6 meses</option>
                                            <option value="12 meses">12 meses</option>
                                            <option value="18 meses">18 meses</option>
                                            <option value="24 meses">24 meses</option>
                                            <option value="36 meses">36 meses</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Información Adicional</label>
                                <textarea class="form-control" v-model="form.informacion_adicional" rows="3" placeholder="Información adicional, condiciones especiales, etc."></textarea>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Descripción Opcionales</label>
                                <textarea class="form-control" v-model="form.descripcion_opcionales" rows="3" placeholder="Descripción de productos opcionales o accesorios adicionales"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" @click="cancelar" :disabled="isSubmitting">
                            ❌ Cancelar
                        </button>
                        
                        <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                            <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
                            💾 {{ isSubmitting ? 'Guardando...' : 'Guardar Cotización' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'CotizacionForm',
    props: {
        initialCotizacion: {
            type: Object,
            default: null
        },
        clientes: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            form: {
                nombre_cotizacion: '',
                codigo: '',
                cliente_id: '',
                nombre_institucion: '',
                nombre_contacto: '',
                validez_oferta: '',
                forma_pago: '',
                plazo_entrega: '',
                garantia_tecnica: '',
                informacion_adicional: '',
                descripcion_opcionales: '',
                productos_cotizados: []
            },
            selectedCliente: null,
            clienteSearchTerm: '',
            clienteResults: [],
            showClienteDropdown: false,
            errors: {},
            generalError: '',
            successMessage: '',
            isEditing: false,
            isSubmitting: false,
            totales: {
                neto: 0,
                iva: 0,
                total: 0
            }
        };
    },
    mounted() {
        console.log('✅ CotizacionForm montado correctamente');
        console.log('📋 Clientes recibidos:', this.clientes);
        console.log('🔍 Elemento #app encontrado:', document.getElementById('app') !== null);
    },
    methods: {
        async searchClientes() {
            if (this.clienteSearchTerm.length < 2) {
                this.clienteResults = [];
                return;
            }

            try {
                const response = await axios.get('/api/buscar-clientes', {
                    params: { q: this.clienteSearchTerm }
                });
                this.clienteResults = response.data;
                this.showClienteDropdown = true;
            } catch (error) {
                console.error('Error buscando clientes:', error);
            }
        },

        selectCliente(cliente) {
            this.selectedCliente = cliente;
            this.clienteSearchTerm = cliente.nombre_institucion;
            this.form.cliente_id = cliente.id;
            this.form.nombre_institucion = cliente.nombre_institucion;
            this.form.nombre_contacto = cliente.nombre_contacto;
            this.showClienteDropdown = false;
            this.clienteResults = [];
        },

        clearCliente() {
            this.selectedCliente = null;
            this.clienteSearchTerm = '';
            this.form.cliente_id = '';
            this.form.nombre_institucion = '';
            this.form.nombre_contacto = '';
        },

        addProduct() {
            this.form.productos_cotizados.push({
                id_producto: '',
                nombre_producto: '',
                descripcion_corta: '',
                precio_unitario: 0,
                cantidad: 1,
                searchTerm: '',
                showDropdown: false,
                results: []
            });
        },

        removeProduct(index) {
            this.form.productos_cotizados.splice(index, 1);
            this.updateTotals();
        },

        async searchProductos(index) {
            const producto = this.form.productos_cotizados[index];
            
            if (!producto.searchTerm || producto.searchTerm.length < 2) {
                producto.results = [];
                producto.showDropdown = false;
                return;
            }

            try {
                const response = await axios.get('/api/buscar-productos', {
                    params: { q: producto.searchTerm }
                });
                producto.results = response.data;
                producto.showDropdown = true;
            } catch (error) {
                console.error('Error buscando productos:', error);
                producto.results = [];
                producto.showDropdown = false;
            }
        },

        selectProducto(index, producto) {
            const item = this.form.productos_cotizados[index];
            item.id_producto = producto.id;
            item.nombre_producto = producto.nombre;
            item.descripcion_corta = producto.descripcion || '';
            item.precio_unitario = parseFloat(producto.precio_neto);
            item.searchTerm = producto.nombre;
            item.showDropdown = false;
            item.results = [];
            this.updateTotals();
        },

        updateTotals() {
            let neto = 0;
            
            this.form.productos_cotizados.forEach(producto => {
                if (producto.precio_unitario && producto.cantidad) {
                    neto += parseFloat(producto.precio_unitario) * parseInt(producto.cantidad);
                }
            });
            
            const iva = neto * 0.19;
            const total = neto + iva;
            
            this.totales = {
                neto: neto,
                iva: iva,
                total: total
            };
        },

        formatNumber(number) {
            return new Intl.NumberFormat('es-CL').format(number || 0);
        },

        async submitForm() {
            this.errors = {};
            this.generalError = '';
            this.isSubmitting = true;

            try {
                console.log('Enviando formulario:', this.form);
                this.successMessage = 'Cotización guardada exitosamente!';
                
                setTimeout(() => {
                    window.location.href = '/cotizaciones';
                }, 1500);
                
            } catch (error) {
                this.generalError = 'Error al guardar la cotización.';
            } finally {
                this.isSubmitting = false;
            }
        },

        cancelar() {
            if (confirm('¿Estás seguro de que quieres cancelar?')) {
                window.location.href = '/cotizaciones';
            }
        }
    }
};
</script>

<style scoped>
.dropdown-menu.show {
    display: block;
    position: absolute;
    z-index: 1000;
    border: 1px solid #ddd;
    border-radius: 0.375rem;
    background-color: white;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.dropdown-item {
    padding: 0.5rem 1rem;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    border-bottom: 1px solid #f8f9fa;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

.position-relative {
    position: relative;
}
</style>