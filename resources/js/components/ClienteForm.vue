<template>
    <div class="card p-4">
        <h3>{{ isEditing ? 'Editar Cliente' : 'Crear Nuevo Cliente' }}</h3>

        <form @submit.prevent="submitForm">
            <div class="mb-4">
                <h4>Información Principal del Cliente</h4>
                <div class="form-group mb-3">
                    <label for="nombre_institucion">Nombre Institución:</label>
                    <input type="text" class="form-control" id="nombre_institucion" v-model="form.nombre_institucion" required>
                    <div v-if="errors.nombre_institucion" class="text-danger">{{ errors.nombre_institucion[0] }}</div>
                </div>
                <div class="form-group mb-3">
                    <label for="rut">RUT:</label>
                    <input type="text" class="form-control" id="rut" v-model="form.rut" required>
                    <div v-if="errors.rut" class="text-danger">{{ errors.rut[0] }}</div>
                </div>
                <div class="form-group mb-3">
                    <label for="tipo_cliente">Tipo de Cliente:</label>
                    <select class="form-control" id="tipo_cliente" v-model="form.tipo_cliente" required>
                        <option value="" disabled>Selecciona un tipo</option>
                        <option value="Cliente Público">Cliente Público</option>
                        <option value="Cliente Privado">Cliente Privado</option>
                        <option value="Revendedor">Revendedor</option>
                    </select>
                    <div v-if="errors.tipo_cliente" class="text-danger">{{ errors.tipo_cliente[0] }}</div>
                </div>
            </div>

            <div class="mb-4">
                <h4>Información de Contacto Principal</h4>
                <div class="form-group mb-3">
                    <label for="nombre_contacto">Nombre de Contacto:</label>
                    <input type="text" class="form-control" id="nombre_contacto" v-model="form.nombre_contacto" required>
                    <div v-if="errors.nombre_contacto" class="text-danger">{{ errors.nombre_contacto[0] }}</div>
                </div>
                <div class="form-group mb-3">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" class="form-control" id="email" v-model="form.email" required>
                    <div v-if="errors.email" class="text-danger">{{ errors.email[0] }}</div>
                </div>
                <div class="form-group mb-3">
                    <label for="telefono">Teléfono:</label>
                    <input type="text" class="form-control" id="telefono" v-model="form.telefono">
                    <div v-if="errors.telefono" class="text-danger">{{ errors.telefono[0] }}</div>
                </div>
                <div class="form-group mb-3">
                    <label for="direccion">Dirección:</label>
                    <textarea class="form-control" id="direccion" v-model="form.direccion"></textarea>
                    <div v-if="errors.direccion" class="text-danger">{{ errors.direccion[0] }}</div>
                </div>
            </div>

            <div class="mb-4">
                <h4>Información Adicional y Asignaciones</h4>
                <div class="form-group mb-3">
                    <label for="vendedores_a_cargo">Vendedor(es) a Cargo (IDs):</label>
                    <input type="text" class="form-control" id="vendedores_a_cargo" v-model="vendedoresInput">
                    <small class="form-text text-muted">Ingrese IDs de vendedores separados por comas (ej. 1, 5, 10).</small>
                    <div v-if="errors.vendedores_a_cargo" class="text-danger">{{ errors.vendedores_a_cargo[0] }}</div>
                </div>
                <div class="form-group mb-3">
                    <label for="informacion_adicional">Información Adicional del Cliente:</label>
                    <textarea class="form-control" id="informacion_adicional" v-model="form.informacion_adicional"></textarea>
                    <div v-if="errors.informacion_adicional" class="text-danger">{{ errors.informacion_adicional[0] }}</div>
                </div>
            </div>

            <button type="submit" class="btn btn-success me-2">{{ isEditing ? 'Guardar Cambios' : 'Crear Cliente' }}</button>
            <button type="button" class="btn btn-secondary" @click="cancelar">Cancelar</button>
        </form>
    </div>
</template>

<script>
import axios from 'axios';

export default {
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
                nombre_contacto: '', // Este campo no estaba en la migración de la tabla cliente, pero en el frontend es importante.
                email: '',
                telefono: '',
                direccion: '',
                vendedores_a_cargo: [],
                informacion_adicional: '',
            },
            errors: {},
            isEditing: false,
            vendedoresInput: '', // Para manejar el input de vendedores separados por coma
        };
    },
    created() {
        if (this.initialCliente) {
            this.isEditing = true;
            this.form = { ...this.initialCliente };
            // Si vendedores_a_cargo llega como array, conviértelo a string para el input
            if (Array.isArray(this.form.vendedores_a_cargo)) {
                this.vendedoresInput = this.form.vendedores_a_cargo.join(', ');
            }
        }
    },
    methods: {
        async submitForm() {
            this.errors = {}; // Limpiar errores previos

            // Convertir la cadena de vendedores a un array de IDs
            this.form.vendedores_a_cargo = this.vendedoresInput
                .split(',')
                .map(id => id.trim())
                .filter(id => id !== ''); // Eliminar cadenas vacías

            try {
                const url = this.isEditing
                    ? `/api/clientes/${this.initialCliente.id}` // Para edición
                    : '/api/clientes'; // Para creación

                const method = this.isEditing ? 'put' : 'post';

                const response = await axios[method](url, this.form);

                alert(`Cliente ${this.isEditing ? 'actualizado' : 'creado'} exitosamente!`);
                // Redirigir a la lista de clientes o hacer algo más
                window.location.href = '/clientes'; // Ejemplo de redirección
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                    alert('Por favor, corrige los errores del formulario.');
                } else {
                    console.error('Error al guardar el cliente:', error);
                    alert('Hubo un error al guardar el cliente.');
                }
            }
        },
        cancelar() {
            window.location.href = '/clientes'; // Redirigir o emitir un evento para cerrar el formulario
        }
    }
};
</script>

<style scoped>
/* Puedes añadir estilos específicos aquí si es necesario */
</style>