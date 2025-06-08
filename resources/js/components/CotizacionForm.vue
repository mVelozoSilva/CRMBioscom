<template>
    <div class="card p-4">
        <h3>{{ isEditing ? 'Editar Cotización' : 'Crear Nueva Cotización' }}</h3>

        <form @submit.prevent="submitForm">
            <div class="mb-4">
                <h4>Información General</h4>
                <div class="form-group mb-3">
                    <label for="nombre_cotizacion">Nombre de la Cotización:</label>
                    <input type="text" class="form-control" id="nombre_cotizacion" v-model="form.nombre_cotizacion" required>
                    <div v-if="errors.nombre_cotizacion" class="text-danger">{{ errors.nombre_cotizacion[0] }}</div>
                </div>
                <div class="form-group mb-3">
                    <label for="codigo">Código de Cotización (Opcional):</label>
                    <input type="text" class="form-control" id="codigo" v-model="form.codigo">
                    <div v-if="errors.codigo" class="text-danger">{{ errors.codigo[0] }}</div>
                </div>
                <div class="form-group mb-3">
                    <label for="searchTermCliente">Buscar Cliente:</label>
                    <input type="text" class="form-control" id="searchTermCliente"
                           v-model="searchTermCliente"
                           @input="buscarClientes"
                           @keyup.delete="limpiarCliente"
                           placeholder="Buscar por institución o RUT"
                           required>
                    <div v-if="errors.cliente_id" class="text-danger">{{ errors.cliente_id[0] }}</div>

                    <ul v-if="resultadosClientes.length > 0 && searchTermCliente.length >= 3 && !clienteSeleccionado" class="list-group mt-1">
                        <li v-for="cliente in resultadosClientes" :key="cliente.id"
                            class="list-group-item list-group-item-action"
                            @click="seleccionarCliente(cliente)">
                            {{ cliente.nombre_institucion }} ({{ cliente.rut || 'N/A' }}) - {{ cliente.nombre_contacto || 'N/A' }}
                        </li>
                    </ul>

                    <div v-if="clienteSeleccionado" class="mt-2 p-2 border rounded bg-light">
                        <p class="mb-0"><strong>Cliente Seleccionado:</strong> {{ clienteSeleccionado.nombre_institucion }}</p>
                        <p class="mb-0">RUT: {{ clienteSeleccionado.rut || 'N/A' }}</p>
                        <p class="mb-0">Contacto: {{ clienteSeleccionado.nombre_contacto || 'N/A' }}</p>
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2" @click="limpiarCliente(); searchTermCliente = ''">Cambiar Cliente</button>
                        <input type="hidden" v-model="form.cliente_id">
                    </div>
                </div>
                <div v-if="clienteSeleccionado" class="form-group mb-3">
                    <label for="searchTermContacto">Contacto del Cliente:</label>
                    <input type="text" class="form-control" id="searchTermContacto"
                           v-model="searchTermContacto"
                           @input="buscarContactos"
                           @keyup.delete="limpiarContactoSeleccionado"
                           placeholder="Buscar contacto o 'Sin información'"
                           :required="!form.nombre_contacto || form.nombre_contacto === 'Sin información'">
                    <div v-if="errors.nombre_contacto" class="text-danger">{{ errors.nombre_contacto[0] }}</div>

                    <ul v-if="resultadosContactos.length > 0 || searchTermContacto.length === 0" class="list-group mt-1">
                        <li class="list-group-item list-group-item-action"
                            @click="seleccionarContacto({ id: null, nombre: 'Sin información', cargo: null, email: null })">
                            Sin información (sin contacto específico)
                        </li>
                        <li v-for="contacto in resultadosContactos" :key="contacto.id"
                            class="list-group-item list-group-item-action"
                            @click="seleccionarContacto(contacto)">
                            {{ contacto.nombre }} ({{ contacto.cargo || 'N/A' }}) - {{ contacto.email || 'N/A' }}
                        </li>
                    </ul>

                    <div v-if="contactoSeleccionado" class="mt-2 p-2 border rounded bg-light">
                        <p class="mb-0"><strong>Contacto Seleccionado:</strong> {{ contactoSeleccionado.nombre }}</p>
                        <p v-if="contactoSeleccionado.cargo" class="mb-0">Cargo: {{ contactoSeleccionado.cargo }}</p>
                        <p v-if="contactoSeleccionado.email" class="mb-0">Email: {{ contactoSeleccionado.email }}</p>
                        <input type="hidden" v-model="form.nombre_contacto">
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="nombre_institucion">Nombre Institución:</label>
                    <input type="text" class="form-control" id="nombre_institucion" v-model="form.nombre_institucion" required readonly>
                    <div v-if="errors.nombre_institucion" class="text-danger">{{ errors.nombre_institucion[0] }}</div>
                </div>
            </div>

            <div class="mb-4">
                <h4>Productos <button type="button" class="btn btn-sm btn-success ms-2" @click="addProduct">Añadir Producto</button></h4>

                <div v-if="errors.productos_cotizados" class="text-danger mb-3">
                    Se debe añadir al menos un producto a la cotización.
                </div>

                <div v-for="(product, index) in form.productos_cotizados" :key="index" class="product-item p-3 mb-3 border rounded">
                    <h5>Producto #{{ index + 1 }}</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label :for="'id_producto_' + index">ID Producto:</label>
                            <input type="text" class="form-control" :id="'id_producto_' + index" v-model="product.id_producto" required>
                            <div v-if="errors[`productos_cotizados.${index}.id_producto`]" class="text-danger">{{ errors[`productos_cotizados.${index}.id_producto`][0] }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label :for="'nombre_producto_' + index">Nombre de Producto:</label>
                            <input type="text" class="form-control" :id="'nombre_producto_' + index" v-model="product.nombre_producto" required>
                            <div v-if="errors[`productos_cotizados.${index}.nombre_producto`]" class="text-danger">{{ errors[`productos_cotizados.${index}.nombre_producto`][0] }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label :for="'descripcion_corta_' + index">Descripción Corta (editable):</label>
                            <textarea class="form-control" :id="'descripcion_corta_' + index" v-model="product.descripcion_corta"></textarea>
                            <div v-if="errors[`productos_cotizados.${index}.descripcion_corta`]" class="text-danger">{{ errors[`productos_cotizados.${index}.descripcion_corta`][0] }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label :for="'precio_unitario_' + index">Precio Unitario (editable):</label>
                            <input type="number" step="0.01" class="form-control" :id="'precio_unitario_' + index" v-model.number="product.precio_unitario" required min="0">
                            <div v-if="errors[`productos_cotizados.${index}.precio_unitario`]" class="text-danger">{{ errors[`productos_cotizados.${index}.precio_unitario`][0] }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label :for="'cantidad_' + index">Cantidad Solicitada:</label>
                            <input type="number" class="form-control" :id="'cantidad_' + index" v-model.number="product.cantidad" required min="1">
                            <div v-if="errors[`productos_cotizados.${index}.cantidad`]" class="text-danger">{{ errors[`productos_cotizados.${index}.cantidad`][0] }}</div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm" @click="removeProduct(index)">Eliminar Producto</button>
                </div>
            </div>

            <div class="mb-4">
                <h4>Resumen de Precios</h4>
                <div class="form-group mb-3">
                    <label>Total Neto:</label>
                    <input type="text" class="form-control" :value="totalNeto.toFixed(2)" readonly>
                </div>
                <div class="form-group mb-3">
                    <label>IVA ({{ ivaRate * 100 }}%):</label>
                    <input type="text" class="form-control" :value="ivaMonto.toFixed(2)" readonly>
                </div>
                <div class="form-group mb-3">
                    <label>Total con IVA:</label>
                    <input type="text" class="form-control" :value="totalConIva.toFixed(2)" readonly>
                </div>
            </div>

            <div class="mb-4">
                <h4>Condiciones Comerciales y Legales</h4>
                <div class="form-group mb-3">
                    <label for="validezOfertaOpcion">Validez de Oferta:</label>
                    <select class="form-control" id="validezOfertaOpcion" v-model="validezOfertaOpcion" @change="calcularValidezOferta" required>
                        <option v-for="option in validezOfertaOptions" :key="option.value" :value="option.value">
                            {{ option.text }}
                        </option>
                    </select>
                    <div v-if="validezOfertaOpcion === 'otro'" class="mt-2">
                        <label for="validezOfertaPersonalizada">Especificar Fecha:</label>
                        <input type="date" class="form-control" id="validezOfertaPersonalizada" v-model="validezOfertaPersonalizada" @input="calcularValidezOferta" required>
                    </div>
                    <div v-if="errors.validez_oferta" class="text-danger">{{ errors.validez_oferta[0] }}</div>
                </div>
                <div class="form-group mb-3">
                    <label for="formaPagoOpcion">Forma de Pago:</label>
                    <select class="form-control" id="formaPagoOpcion" v-model="formaPagoOpcion" @change="establecerFormaPago">
                        <option v-for="option in formaPagoOptions" :key="option.value" :value="option.value">
                            {{ option.text }}
                        </option>
                    </select>
                    <div v-if="formaPagoOpcion === 'otro'" class="mt-2">
                        <label for="formaPagoPersonalizada">Especificar Forma de Pago:</label>
                        <input type="text" class="form-control" id="formaPagoPersonalizada" v-model="formaPagoPersonalizada" @input="establecerFormaPago">
                    </div>
                    <div v-if="errors.forma_pago" class="text-danger">{{ errors.forma_pago[0] }}</div>
                </div>
                <div class="form-group mb-3">
                    <label for="plazo_entrega">Plazo de Entrega:</label>
                    <input type="text" class="form-control" id="plazo_entrega" v-model="form.plazo_entrega">
                    <div v-if="errors.plazo_entrega" class="text-danger">{{ errors.plazo_entrega[0] }}</div>
                </div>
                <div class="form-group mb-3">
                    <label for="garantiaTecnicaOpcion">Garantía Técnica:</label>
                    <select class="form-control" id="garantiaTecnicaOpcion" v-model="garantiaTecnicaOpcion" @change="establecerGarantiaTecnica">
                        <option v-for="option in garantiaTecnicaOptions" :key="option.value" :value="option.value">
                            {{ option.text }}
                        </option>
                    </select>
                    <div v-if="garantiaTecnicaOpcion === 'personalizada'" class="mt-2">
                        <label for="garantiaTecnicaPersonalizada">Especificar Garantía:</label>
                        <textarea class="form-control" id="garantiaTecnicaPersonalizada" v-model="garantiaTecnicaPersonalizada" @input="establecerGarantiaTecnica"></textarea>
                    </div>
                    <div v-if="errors.garantia_tecnica" class="text-danger">{{ errors.garantia_tecnica[0] }}</div>
                </div>
                <div class="form-group mb-3">
                    <label for="informacion_adicional">Información Adicional:</label>
                    <textarea class="form-control" id="informacion_adicional" v-model="form.informacion_adicional"></textarea>
                    <div v-if="errors.informacion_adicional" class="text-danger">{{ errors.informacion_adicional[0] }}</div>
                </div>
                <div class="form-group mb-3">
                    <label for="descripcion_opcionales">Descripción Opcionales:</label>
                    <textarea class="form-control" id="descripcion_opcionales" v-model="form.descripcion_opcionales"></textarea>
                    <div v-if="errors.descripcion_opcionales" class="text-danger">{{ errors.descripcion_opcionales[0] }}</div>
                </div>
            </div>

            <button type="submit" class="btn btn-success">Guardar Cotización</button>
        </form>
    </div>
</template>
<script>
import axios from 'axios'; // Importa Axios para hacer peticiones HTTP

export default {
    props: {
        // Si pasas datos iniciales (ej. para edición) desde Blade
        initialCotizacion: {
            type: Object,
            default: null
        },
        // Propiedad para recibir la lista de clientes desde Laravel
        clientes: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            form: {
                nombre_institucion: '',
                nombre_contacto: '',
                cliente_id: '', // Para el select de clientes
                productos_cotizados: [], // Array para los productos
                validez_oferta: '',
                forma_pago: '',
                plazo_entrega: '',
                garantia_tecnica: '',
                informacion_adicional: '',
                descripcion_opcionales: '',
                nombre_cotizacion: '', 
                codigo: '', 
            },
            isEditing: false, 
            validezOfertaOpcion: '30_dias', // Opción seleccionada para la validez
            validezOfertaPersonalizada: '', // Campo para fecha si se selecciona "Otro"
            validezOfertaOptions: [ // Opciones para el selector de validez
                { value: '30_dias', text: '30 días corridos' },
                { value: '60_dias', text: '60 días corridos' },
                { value: '90_dias', text: '90 días corridos' },
                { value: '120_dias', text: '120 días corridos' },
                { value: '150_dias', text: '150 días corridos' },
                { value: '180_dias', text: '180 días corridos' },
                { value: '240_dias', text: '240 días corridos' },
                { value: 'otro', text: 'Otro (especificar fecha)' },
            ],
            formaPagoOpcion: 'oc_30_dias', // Opción por defecto: Orden de Compra a 30 días
            formaPagoPersonalizada: '', // Campo para texto si se selecciona "Otro (especificar)"
            formaPagoOptions: [ // Opciones para el selector de forma de pago
                { value: 'oc_30_dias', text: 'Orden de Compra a 30 días' },
                { value: 'oc_45_dias', text: 'Orden de Compra a 45 días' },
                { value: 'contado_transferencia', text: 'Pago al contado con transferencia' },
                { value: '15_orden_saldo_entrega', text: '15% con la orden, saldo contra entrega' },
                { value: '30_orden_saldo_entrega', text: '30% con la orden, saldo contra entrega' },
                { value: '50_orden_saldo_entrega', text: '50% con la orden, saldo contra entrega' },
                { value: 'contado_100_anticipado', text: 'Contado, 100% anticipado' },
                { value: 'leasing_externo', text: 'Leasing Externo' },
                { value: 'cheque', text: 'Cheque' },
                { value: 'otro', text: 'Otro (especificar)' }, // Opción "Otro" para texto libre
            ],
            ivaRate: 0.19, // 19% de IVA para Chile
            errors: {}, // Objeto para almacenar errores de validación de Laravel
            searchTermCliente: '', // Término de búsqueda para el autocompletado
            resultadosClientes: [], // Resultados de la búsqueda de clientes
            clienteSeleccionado: null, // Almacena el objeto del cliente seleccionado
            searchTermContacto: '', // Término de búsqueda para el autocompletado de contactos
            resultadosContactos: [], // Resultados de la búsqueda de contactos
            contactoSeleccionado: null, // Almacena el objeto del contacto seleccionado
            garantiaTecnicaOpcion: '12_meses', // Opción por defecto: 12 meses
            garantiaTecnicaPersonalizada: '', // Campo para texto si se selecciona "Personalizada"
            garantiaTecnicaOptions: [ // Opciones para el selector de garantía en meses
                { value: '6_meses', text: '6 meses' },
                { value: '12_meses', text: '12 meses' },
                { value: '18_meses', text: '18 meses' },
                { value: '24_meses', text: '24 meses' },
                { value: '36_meses', text: '36 meses' },
                { value: 'personalizada', text: 'Personalizada (especificar)' },
                { value: 'sin_garantia', text: 'Sin garantía' },
            ],
        };
    },
    computed: {
        totalNeto() {
            return this.form.productos_cotizados.reduce((sum, product) => {
                const precio = parseFloat(product.precio_unitario) || 0;
                const cantidad = parseInt(product.cantidad) || 0;
                return sum + (precio * cantidad);
            }, 0);
        },
        ivaMonto() {
            return this.totalNeto * this.ivaRate;
        },
        totalConIva() {
            return this.totalNeto + this.ivaMonto;
        }
    },
    created() {
        if (this.initialCotizacion) {
            this.isEditing = true; 
            if (typeof this.initialCotizacion.productos_cotizados === 'string') {
                this.initialCotizacion.productos_cotizados = JSON.parse(this.initialCotizacion.productos_cotizados);
            }
            this.form = { ...this.initialCotizacion };
        }
        // Asegurar que productos_cotizados sea siempre un array, incluso si initialCotizacion no lo define
        this.form.productos_cotizados = this.form.productos_cotizados || [];
        if (!this.isEditing && this.form.productos_cotizados.length === 0) {
            this.addProduct();
        }
        if (this.form.validez_oferta) {
            const initialDate = new Date(this.form.validez_oferta);
            const today = new Date(); // Definir 'today' aquí si no está definida globalmente
            let matchedOption = false;

            for (const option of this.validezOfertaOptions) {
                if (option.value !== 'otro') {
                    const daysToAdd = parseInt(option.value.split('_')[0]);
                    const calculatedDate = new Date(today);
                    calculatedDate.setDate(today.getDate() + daysToAdd);
                    if (initialDate.toDateString() === calculatedDate.toDateString()) {
                        this.validezOfertaOpcion = option.value;
                        matchedOption = true;
                        break;
                    }
                }
            }
            if (!matchedOption) {
                this.validezOfertaOpcion = 'otro';
                this.validezOfertaPersonalizada = this.form.validez_oferta;
            }
        }
        if (this.form.garantia_tecnica) {
            const matchedOption = this.garantiaTecnicaOptions.find(
                opt => opt.text === this.form.garantia_tecnica
            );
            if (matchedOption) {
                this.garantiaTecnicaOpcion = matchedOption.value;
            } else {
                this.garantiaTecnicaOpcion = 'personalizada';
                this.garantiaTecnicaPersonalizada = this.form.garantia_tecnica;
            }
        }
        if (this.form.forma_pago) {
            const matchedOption = this.formaPagoOptions.find(
                opt => opt.text === this.form.forma_pago
            );
            if (matchedOption) {
                this.formaPagoOpcion = matchedOption.value;
            } else {
                this.formaPagoOpcion = 'otro';
                this.formaPagoPersonalizada = this.form.forma_pago;
            }
        }
        this.calcularValidezOferta();
        this.establecerGarantiaTecnica();
        this.establecerFormaPago();
    },
    methods: {
        async buscarClientes() {
            if (this.searchTermCliente.length < 3) {
                this.resultadosClientes = [];
                return;
            }
            try {
                const response = await axios.get(`/api/clientes/buscar?q=${this.searchTermCliente}`);
                this.resultadosClientes = response.data;
            } catch (error) {
                console.error('Error al buscar clientes:', error);
                this.resultadosClientes = [];
            }
        },
        seleccionarCliente(cliente) {
            this.clienteSeleccionado = cliente;
            this.form.cliente_id = cliente.id;
            this.form.nombre_institucion = cliente.nombre_institucion;
            
            this.searchTermContacto = '';
            this.limpiarContactoSeleccionado();

            if (cliente.nombre_contacto) {
                this.form.nombre_contacto = cliente.nombre_contacto;
                this.searchTermContacto = cliente.nombre_contacto;
                this.contactoSeleccionado = { id: null, nombre: cliente.nombre_contacto, cargo: null, email: null };
            } else {
                this.form.nombre_contacto = 'Sin información';
                this.searchTermContacto = 'Sin información';
                this.contactoSeleccionado = { id: null, nombre: 'Sin información', cargo: null, email: null };
            }
            this.resultadosClientes = [];
        },
        limpiarCliente() {
            if (this.searchTermCliente === '') {
                this.clienteSeleccionado = null;
                this.form.cliente_id = '';
                this.form.nombre_institucion = '';
                this.form.nombre_contacto = '';
            }
        },
        async buscarContactos() {
            if (!this.clienteSeleccionado || this.searchTermContacto.length < 2) {
                this.resultadosContactos = [];
                return;
            }
            try {
                const response = await axios.get(`/api/contactos?cliente_id=<span class="math-inline">\{this\.clienteSeleccionado\.id\}&q\=</span>{this.searchTermContacto}`);
                this.resultadosContactos = response.data;
            } catch (error) {
                console.error('Error al buscar contactos:', error);
                this.resultadosContactos = [];
            }
        },
        seleccionarContacto(contacto) {
            this.contactoSeleccionado = contacto;
            this.form.nombre_contacto = contacto.nombre;
            this.searchTermContacto = contacto.nombre;
            this.resultadosContactos = [];
        },
        limpiarContactoSeleccionado() {
            if (this.searchTermContacto === '') {
                this.contactoSeleccionado = null;
                this.form.nombre_contacto = '';
            }
        },
        calcularValidezOferta() {
            const today = new Date();
            let daysToAdd = 0;

            if (this.validezOfertaOpcion === 'otro') {
                this.form.validez_oferta = this.validezOfertaPersonalizada;
                return;
            }
            switch (this.validezOfertaOpcion) {
                case '30_dias':
                    daysToAdd = 30;
                    break;
                case '60_dias':
                    daysToAdd = 60;
                    break;
                case '90_dias':
                    daysToAdd = 90;
                    break;
                case '180_dias':
                    daysToAdd = 180;
                    break;
                default:
                    daysToAdd = 30;
            }
            const futureDate = new Date(today);
            futureDate.setDate(today.getDate() + daysToAdd);
            this.form.validez_oferta = futureDate.toISOString().slice(0, 10);
        },
        establecerGarantiaTecnica() {
            if (this.garantiaTecnicaOpcion === 'personalizada') {
                this.form.garantia_tecnica = this.garantiaTecnicaPersonalizada;
            } else if (this.garantiaTecnicaOpcion === 'sin_garantia') {
                this.form.garantia_tecnica = 'Sin garantía';
            } else {
                this.form.garantia_tecnica = this.garantiaTecnicaOptions.find(
                    opt => opt.value === this.garantiaTecnicaOpcion
                )?.text || '';
            }
        },
        establecerFormaPago() {
            if (this.formaPagoOpcion === 'otro') {
                this.form.forma_pago = this.formaPagoPersonalizada;
            } else {
                this.form.forma_pago = this.formaPagoOptions.find(
                    opt => opt.value === this.formaPagoOpcion
                )?.text || '';
            }
        },
        addProduct() {
            this.form.productos_cotizados.push({
                id_producto: '',
                nombre_producto: '',
                descripcion_corta: '',
                precio_unitario: 0,
                cantidad: 1,
            });
        },
        removeProduct(index) {
            this.form.productos_cotizados.splice(index, 1);
        },
        // resources/js/components/CotizacionForm.vue (dentro de methods: { ... })

        async submitForm() {
            this.errors = {};
            console.log('Valor de nombre_cotizacion antes de enviar:', this.form.nombre_cotizacion);

            try {
                const url = this.initialCotizacion
                    ? `/cotizaciones/${this.initialCotizacion.id}` // URL para edición
                    : '/cotizaciones'; // URL para creación

                // Ahora usaremos 'patch' para actualizaciones
                const method = this.isEditing ? 'patch' : 'post'; // <-- CAMBIO AQUÍ: 'patch'

                const dataToSend = {
                    ...this.form,
                    // Si es PATCH, el _method debe ser PATCH
                    _method: this.isEditing ? 'PATCH' : 'POST', // <-- CAMBIO AQUÍ: 'PATCH'
                    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    total_neto: this.totalNeto.toFixed(2),
                    iva: this.ivaMonto.toFixed(2),
                    total_con_iva: this.totalConIva.toFixed(2),
                };

                // Usamos axios[method] para que envíe PATCH o POST
                const response = await axios[method](url, dataToSend); // <-- SE MANTIENE AXIOS[METHOD]

                alert('Cotización guardada exitosamente!');
                window.location.href = '/cotizaciones';
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                    alert('Por favor, corrige los errores del formulario.');
                } else {
                    console.error('Error al guardar la cotización:', error);
                    alert('Hubo un error al guardar la cotización.');
                }
            }
        }
    }
};
</script>

<style scoped>
/* Puedes añadir estilos específicos para este componente aquí */
.product-item {
    background-color: #f9f9f9;
}
</style>