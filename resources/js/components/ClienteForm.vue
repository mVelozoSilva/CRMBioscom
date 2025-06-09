<template>
    <div class="container mt-4">
        <div class="card p-4 shadow">
            <h3 class="text-primary mb-4">
                {{ isEditing ? 'Editar Cliente' : 'Crear Nuevo Cliente' }}
            </h3>

            <!-- Mostrar errores generales -->
            <div v-if="generalError" class="alert alert-danger">
                {{ generalError }}
            </div>

            <!-- Mostrar mensaje de éxito -->
            <div v-if="successMessage" class="alert alert-success">
                {{ successMessage }}
            </div>

            <form @submit.prevent="submitForm">
                <!-- Información Principal del Cliente -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Información Principal del Cliente</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nombre_institucion" class="form-label">
                                        Nombre Institución <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        :class="{'is-invalid': errors.nombre_institucion}"
                                        id="nombre_institucion" 
                                        v-model="form.nombre_institucion" 
                                        required
                                        placeholder="Ej: Hospital Regional de Santiago"
                                    >
                                    <div v-if="errors.nombre_institucion" class="invalid-feedback">
                                        {{ errors.nombre_institucion[0] }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="rut" class="form-label">
                                        RUT <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        :class="{'is-invalid': errors.rut}"
                                        id="rut" 
                                        v-model="form.rut" 
                                        required
                                        placeholder="Ej: 12.345.678-9"
                                    >
                                    <div v-if="errors.rut" class="invalid-feedback">
                                        {{ errors.rut[0] }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="tipo_cliente" class="form-label">
                                Tipo de Cliente <span class="text-danger">*</span>
                            </label>
                            <select 
                                class="form-control" 
                                :class="{'is-invalid': errors.tipo_cliente}"
                                id="tipo_cliente" 
                                v-model="form.tipo_cliente" 
                                required
                            >
                                <option value="" disabled>Selecciona un tipo</option>
                                <option value="Cliente Público">Cliente Público</option>
                                <option value="Cliente Privado">Cliente Privado</option>
                                <option value="Revendedor">Revendedor</option>
                            </select>
                            <div v-if="errors.tipo_cliente" class="invalid-feedback">
                                {{ errors.tipo_cliente[0] }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de Contacto Principal -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Información de Contacto Principal</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nombre_contacto" class="form-label">
                                        Nombre de Contacto <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        :class="{'is-invalid': errors.nombre_contacto}"
                                        id="nombre_contacto" 
                                        v-model="form.nombre_contacto" 
                                        required
                                        placeholder="Ej: María González"
                                    >
                                    <div v-if="errors.nombre_contacto" class="invalid-feedback">
                                        {{ errors.nombre_contacto[0] }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email" class="form-label">
                                        Correo Electrónico <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        type="email" 
                                        class="form-control" 
                                        :class="{'is-invalid': errors.email}"
                                        id="email" 
                                        v-model="form.email" 
                                        required
                                        placeholder="Ej: maria.gonzalez@hospital.cl"
                                    >
                                    <div v-if="errors.email" class="invalid-feedback">
                                        {{ errors.email[0] }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        :class="{'is-invalid': errors.telefono}"
                                        id="telefono" 
                                        v-model="form.telefono"
                                        placeholder="Ej: +56 9 1234 5678"
                                    >
                                    <div v-if="errors.telefono" class="invalid-feedback">
                                        {{ errors.telefono[0] }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <textarea 
                                class="form-control" 
                                :class="{'is-invalid': errors.direccion}"
                                id="direccion" 
                                v-model="form.direccion"
                                rows="3"
                                placeholder="Ej: Av. Libertador Bernardo O'Higgins 1234, Santiago"
                            ></textarea>
                            <div v-if="errors.direccion" class="invalid-feedback">
                                {{ errors.direccion[0] }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información Adicional y Asignaciones -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Información Adicional y Asignaciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="vendedores_a_cargo" class="form-label">
                                Vendedor(es) a Cargo
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                :class="{'is-invalid': errors.vendedores_a_cargo}"
                                id="vendedores_a_cargo" 
                                v-model="vendedoresInput"
                                placeholder="Ej: Juan Pérez, María Silva"
                            >
                            <small class="form-text text-muted">
                                Ingrese nombres de vendedores separados por comas.
                            </small>
                            <div v-if="errors.vendedores_a_cargo" class="invalid-feedback">
                                {{ errors.vendedores_a_cargo[0] }}
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="informacion_adicional" class="form-label">
                                Información Adicional del Cliente
                            </label>
                            <textarea 
                                class="form-control" 
                                :class="{'is-invalid': errors.informacion_adicional}"
                                id="informacion_adicional" 
                                v-model="form.informacion_adicional"
                                rows="4"
                                placeholder="Información relevante sobre el cliente, preferencias, historial, etc."
                            ></textarea>
                            <div v-if="errors.informacion_adicional" class="invalid-feedback">
                                {{ errors.informacion_adicional[0] }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="d-flex justify-content-between">
                    <button 
                        type="button" 
                        class="btn btn-secondary" 
                        @click="cancelar"
                        :disabled="isSubmitting"
                    >
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    
                    <button 
                        type="submit" 
                        class="btn btn-success" 
                        :disabled="isSubmitting"
                    >
                        <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="fas fa-save"></i>
                        {{ isSubmitting ? 'Guardando...' : (isEditing ? 'Actualizar Cliente' : 'Crear Cliente') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'ClienteForm',
    props: {
        initialCliente: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            form: {
                nombre_institucion: '',
                rut: '',
                tipo_cliente: '',
                nombre_contacto: '',
                email: '',
                telefono: '',
                direccion: '',
                vendedores_a_cargo: [],
                informacion_adicional: '',
            },
            errors: {},
            generalError: '',
            successMessage: '',
            isEditing: false,
            isSubmitting: false,
            vendedoresInput: '', // Para manejar el input de vendedores
        };
    },
    created() {
        this.initializeForm();
    },
    methods: {
        initializeForm() {
            if (this.initialCliente) {
                this.isEditing = true;
                this.form = { ...this.initialCliente };
                
                // Convertir array de vendedores a string
                if (Array.isArray(this.form.vendedores_a_cargo)) {
                    this.vendedoresInput = this.form.vendedores_a_cargo.join(', ');
                } else if (this.form.vendedores_a_cargo) {
                    this.vendedoresInput = this.form.vendedores_a_cargo;
                }
            }
        },

        async submitForm() {
            // Limpiar estados previos
            this.errors = {};
            this.generalError = '';
            this.successMessage = '';
            this.isSubmitting = true;

            // Procesar vendedores
            this.form.vendedores_a_cargo = this.vendedoresInput
                .split(',')
                .map(vendedor => vendedor.trim())
                .filter(vendedor => vendedor !== '');

            try {
                const url = this.isEditing
                    ? `/api/clientes/${this.initialCliente.id}`
                    : '/api/clientes';

                const method = this.isEditing ? 'put' : 'post';

                const response = await axios[method](url, this.form);

                this.successMessage = response.data.message;
                
                // Redirigir después de un breve delay
                setTimeout(() => {
                    window.location.href = '/clientes';
                }, 1500);

            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors || {};
                    this.generalError = 'Por favor, corrige los errores del formulario.';
                } else {
                    console.error('Error al guardar el cliente:', error);
                    this.generalError = error.response?.data?.message || 'Hubo un error al guardar el cliente.';
                }
            } finally {
                this.isSubmitting = false;
            }
        },

        cancelar() {
            if (confirm('¿Estás seguro de que quieres cancelar? Los cambios no guardados se perderán.')) {
                window.location.href = '/clientes';
            }
        }
    }
};
</script>

<style scoped>
.card-header h5 {
    margin: 0;
}

.form-label {
    font-weight: 600;
    color: #495057;
}

.text-danger {
    color: #dc3545 !important;
}

.btn {
    border-radius: 5px;
    padding: 10px 20px;
    font-weight: 500;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

.alert {
    border-radius: 5px;
}

.shadow {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>