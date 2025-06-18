<template>
  <div class="constructor-visual bg-white border border-gray-200 rounded-lg shadow-sm">
    <!-- Header del Constructor -->
    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-lg">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-medium text-gray-900">Constructor Visual</h3>
          <p class="text-sm text-gray-600">Diseña el contenido para cotizaciones profesionales</p>
        </div>
        <div class="flex space-x-2">
          <button @click="previsualizarContenido" 
                  class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <i class="fas fa-eye mr-2"></i>
            Previsualizar
          </button>
          <button @click="guardarPlantilla" 
                  class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <i class="fas fa-save mr-2"></i>
            Guardar
          </button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-0">
      <!-- Panel de Herramientas -->
      <div class="lg:col-span-1 bg-gray-50 border-r border-gray-200 p-4">
        <!-- Plantillas Predefinidas -->
        <div class="mb-6">
          <h4 class="text-sm font-medium text-gray-900 mb-3">Plantillas Base</h4>
          <div class="space-y-2">
            <button v-for="plantilla in plantillasDisponibles" 
                    :key="plantilla.id"
                    @click="cargarPlantilla(plantilla)"
                    class="w-full text-left p-3 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors"
                    :class="{ 'border-blue-500 bg-blue-50': plantillaSeleccionada?.id === plantilla.id }">
              <div class="font-medium text-sm text-gray-900">{{ plantilla.nombre }}</div>
              <div class="text-xs text-gray-600">{{ plantilla.descripcion }}</div>
            </button>
          </div>
        </div>

        <!-- Bloques Disponibles -->
        <div class="mb-6">
          <h4 class="text-sm font-medium text-gray-900 mb-3">Bloques Disponibles</h4>
          <div class="space-y-2">
            <div v-for="bloque in bloquesDisponibles" 
                 :key="bloque.tipo"
                 draggable="true"
                 @dragstart="iniciarArrastre(bloque)"
                 class="p-3 border border-gray-200 rounded-lg cursor-move hover:border-blue-300 hover:bg-blue-50 transition-colors">
              <div class="flex items-center">
                <i :class="bloque.icono" class="text-gray-500 mr-3"></i>
                <div>
                  <div class="font-medium text-sm text-gray-900">{{ bloque.nombre }}</div>
                  <div class="text-xs text-gray-600">{{ bloque.descripcion }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Configuración de Estilo -->
        <div class="mb-6">
          <h4 class="text-sm font-medium text-gray-900 mb-3">Configuración</h4>
          <div class="space-y-4">
            <!-- Tema de Colores -->
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-2">Tema de Colores</label>
              <select v-model="configuracion.tema" 
                      class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="bioscom">Bioscom (Corporativo)</option>
                <option value="minimalista">Minimalista</option>
                <option value="medico">Médico Profesional</option>
                <option value="tecnologico">Tecnológico</option>
              </select>
            </div>

            <!-- Tamaño de Fuente -->
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-2">Tamaño de Fuente</label>
              <select v-model="configuracion.tamanoFuente" 
                      class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="pequeno">Pequeño</option>
                <option value="normal">Normal</option>
                <option value="grande">Grande</option>
              </select>
            </div>

            <!-- Espaciado -->
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-2">Espaciado</label>
              <select v-model="configuracion.espaciado" 
                      class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="compacto">Compacto</option>
                <option value="normal">Normal</option>
                <option value="amplio">Amplio</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Área de Construcción -->
      <div class="lg:col-span-2 p-6">
        <div class="mb-4 flex items-center justify-between">
          <h4 class="text-sm font-medium text-gray-900">Diseño del Producto</h4>
          <div class="text-xs text-gray-500">
            Arrastra bloques aquí para construir tu contenido
          </div>
        </div>

        <!-- Zona de Drop -->
        <div ref="zonaConstructor" 
             @drop="soltarBloque" 
             @dragover.prevent 
             @dragenter.prevent
             class="min-h-96 border-2 border-dashed border-gray-300 rounded-lg p-4 transition-colors"
             :class="{ 'border-blue-400 bg-blue-50': arrastrando }">
          
          <!-- Bloques Construidos -->
          <div v-if="bloquesConstructor.length === 0" 
               class="flex flex-col items-center justify-center h-48 text-gray-400">
            <i class="fas fa-puzzle-piece text-4xl mb-4"></i>
            <p class="text-lg font-medium">Comienza arrastrando bloques aquí</p>
            <p class="text-sm">O selecciona una plantilla predefinida</p>
          </div>

          <div v-else class="space-y-4">
            <div v-for="(bloque, index) in bloquesConstructor" 
                 :key="bloque.id"
                 class="relative group border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
              
              <!-- Controles del Bloque -->
              <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex space-x-1">
                <button @click="moverBloque(index, -1)" 
                        :disabled="index === 0"
                        class="p-1 text-gray-400 hover:text-gray-600 disabled:opacity-30">
                  <i class="fas fa-arrow-up text-xs"></i>
                </button>
                <button @click="moverBloque(index, 1)" 
                        :disabled="index === bloquesConstructor.length - 1"
                        class="p-1 text-gray-400 hover:text-gray-600 disabled:opacity-30">
                  <i class="fas fa-arrow-down text-xs"></i>
                </button>
                <button @click="editarBloque(bloque, index)" 
                        class="p-1 text-blue-500 hover:text-blue-700">
                  <i class="fas fa-edit text-xs"></i>
                </button>
                <button @click="eliminarBloque(index)" 
                        class="p-1 text-red-500 hover:text-red-700">
                  <i class="fas fa-trash text-xs"></i>
                </button>
              </div>

              <!-- Contenido del Bloque -->
              <component :is="'bloque-' + bloque.tipo" 
                        :bloque="bloque" 
                        :configuracion="configuracion"
                        @actualizar="actualizarBloque(index, $event)" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Edición de Bloque -->
    <div v-if="bloqueEditando" 
         class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
             @click="cerrarEdicion"></div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                  Editar {{ obtenerNombreBloque(bloqueEditando.tipo) }}
                </h3>
                
                <component :is="'editor-' + bloqueEditando.tipo" 
                          :bloque="bloqueEditando" 
                          @actualizar="actualizarBloqueEditando" />
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button @click="guardarEdicion" 
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
              Guardar
            </button>
            <button @click="cerrarEdicion" 
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Previsualización -->
    <div v-if="mostrandoPreview" 
         class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
             @click="cerrarPreview"></div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all my-8 align-middle max-w-4xl w-full">
          <div class="bg-white px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Previsualización - {{ productoNombre || 'Producto' }}
              </h3>
              <button @click="cerrarPreview" 
                      class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          <div class="p-6 max-h-96 overflow-y-auto" 
               v-html="htmlPreview">
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProductoConstructorVisual',
  props: {
    producto: {
      type: Object,
      default: () => ({})
    },
    readonly: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      bloquesConstructor: [],
      plantillaSeleccionada: null,
      configuracion: {
        tema: 'bioscom',
        tamanoFuente: 'normal',
        espaciado: 'normal'
      },
      arrastrando: false,
      bloqueEditando: null,
      indiceEditando: -1,
      mostrandoPreview: false,
      htmlPreview: '',
      
      plantillasDisponibles: [
        {
          id: 'simple',
          nombre: 'Producto Simple',
          descripcion: 'Para insumos y productos básicos',
          bloques: [
            { tipo: 'titulo', contenido: { texto: 'Nombre del Producto', nivel: 'h2' } },
            { tipo: 'imagen', contenido: { url: '', alt: 'Imagen del producto' } },
            { tipo: 'descripcion', contenido: { texto: 'Descripción del producto...' } },
            { tipo: 'precio', contenido: { precio: 0, moneda: 'CLP' } }
          ]
        },
        {
          id: 'medio',
          nombre: 'Producto Medio',
          descripcion: 'Para equipos con especificaciones',
          bloques: [
            { tipo: 'titulo', contenido: { texto: 'Nombre del Producto', nivel: 'h2' } },
            { tipo: 'galeria', contenido: { imagenes: [] } },
            { tipo: 'descripcion', contenido: { texto: 'Descripción detallada...' } },
            { tipo: 'especificaciones', contenido: { specs: [] } },
            { tipo: 'accesorios', contenido: { lista: [] } },
            { tipo: 'precio', contenido: { precio: 0, moneda: 'CLP' } }
          ]
        },
        {
          id: 'complejo',
          nombre: 'Producto Complejo',
          descripcion: 'Para equipamiento médico avanzado',
          bloques: [
            { tipo: 'titulo', contenido: { texto: 'Nombre del Producto', nivel: 'h1' } },
            { tipo: 'galeria', contenido: { imagenes: [] } },
            { tipo: 'descripcion', contenido: { texto: 'Descripción completa...' } },
            { tipo: 'especificaciones', contenido: { specs: [] } },
            { tipo: 'caracteristicas', contenido: { features: [] } },
            { tipo: 'accesorios', contenido: { lista: [] } },
            { tipo: 'opcionales', contenido: { opciones: [] } },
            { tipo: 'garantia', contenido: { texto: 'Información de garantía...' } },
            { tipo: 'precio', contenido: { precio: 0, moneda: 'CLP' } }
          ]
        }
      ],
      
      bloquesDisponibles: [
        { tipo: 'titulo', nombre: 'Título', descripcion: 'Título del producto', icono: 'fas fa-heading' },
        { tipo: 'imagen', nombre: 'Imagen', descripcion: 'Imagen individual', icono: 'fas fa-image' },
        { tipo: 'galeria', nombre: 'Galería', descripcion: 'Múltiples imágenes', icono: 'fas fa-images' },
        { tipo: 'descripcion', nombre: 'Descripción', descripcion: 'Texto descriptivo', icono: 'fas fa-align-left' },
        { tipo: 'especificaciones', nombre: 'Especificaciones', descripcion: 'Tabla de specs', icono: 'fas fa-list-ul' },
        { tipo: 'caracteristicas', nombre: 'Características', descripcion: 'Lista de features', icono: 'fas fa-check-circle' },
        { tipo: 'accesorios', nombre: 'Accesorios', descripcion: 'Lista de accesorios', icono: 'fas fa-plus-circle' },
        { tipo: 'opcionales', nombre: 'Opcionales', descripcion: 'Elementos opcionales', icono: 'fas fa-toggle-on' },
        { tipo: 'garantia', nombre: 'Garantía', descripcion: 'Info de garantía', icono: 'fas fa-shield-alt' },
        { tipo: 'precio', nombre: 'Precio', descripcion: 'Precio del producto', icono: 'fas fa-dollar-sign' }
      ]
    }
  },
  
  computed: {
    productoNombre() {
      return this.producto?.nombre || 'Nuevo Producto';
    }
  },
  
  mounted() {
    this.inicializar();
  },
  
  methods: {
    inicializar() {
      // Cargar contenido existente si está editando
      if (this.producto?.bloques_contenido) {
        try {
          this.bloquesConstructor = JSON.parse(this.producto.bloques_contenido);
        } catch (e) {
          console.error('Error al parsear bloques existentes:', e);
        }
      }
      
      // Cargar configuración existente
      if (this.producto?.configuracion_visual) {
        try {
          this.configuracion = { ...this.configuracion, ...JSON.parse(this.producto.configuracion_visual) };
        } catch (e) {
          console.error('Error al parsear configuración existente:', e);
        }
      }
    },
    
    cargarPlantilla(plantilla) {
      this.plantillaSeleccionada = plantilla;
      this.bloquesConstructor = plantilla.bloques.map((bloque, index) => ({
        ...bloque,
        id: this.generarId()
      }));
      this.emitirCambios();
    },
    
    iniciarArrastre(bloque) {
      this.arrastrando = true;
      this.bloqueArrastrado = bloque;
    },
    
    soltarBloque(event) {
      event.preventDefault();
      this.arrastrando = false;
      
      if (this.bloqueArrastrado) {
        const nuevoBloque = {
          ...this.bloqueArrastrado,
          id: this.generarId(),
          contenido: this.obtenerContenidoDefault(this.bloqueArrastrado.tipo)
        };
        this.bloquesConstructor.push(nuevoBloque);
        this.emitirCambios();
      }
    },
    
    moverBloque(index, direccion) {
      const newIndex = index + direccion;
      if (newIndex >= 0 && newIndex < this.bloquesConstructor.length) {
        const bloque = this.bloquesConstructor[index];
        this.bloquesConstructor.splice(index, 1);
        this.bloquesConstructor.splice(newIndex, 0, bloque);
        this.emitirCambios();
      }
    },
    
    editarBloque(bloque, index) {
      this.bloqueEditando = { ...bloque };
      this.indiceEditando = index;
    },
    
    eliminarBloque(index) {
      if (confirm('¿Estás seguro de que quieres eliminar este bloque?')) {
        this.bloquesConstructor.splice(index, 1);
        this.emitirCambios();
      }
    },
    
    actualizarBloque(index, contenido) {
      this.bloquesConstructor[index].contenido = contenido;
      this.emitirCambios();
    },
    
    actualizarBloqueEditando(contenido) {
      this.bloqueEditando.contenido = contenido;
    },
    
    guardarEdicion() {
      if (this.indiceEditando >= 0) {
        this.bloquesConstructor[this.indiceEditando] = { ...this.bloqueEditando };
        this.emitirCambios();
      }
      this.cerrarEdicion();
    },
    
    cerrarEdicion() {
      this.bloqueEditando = null;
      this.indiceEditando = -1;
    },
    
    async previsualizarContenido() {
      try {
        const response = await fetch('/crm-bioscom/public/api/productos/previsualizar-constructor', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            bloques_contenido: JSON.stringify(this.bloquesConstructor),
            plantilla_base: this.plantillaSeleccionada?.id,
            configuracion_visual: this.configuracion
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.htmlPreview = data.html;
          this.mostrandoPreview = true;
        } else {
          alert('Error al generar previsualización');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión al generar previsualización');
      }
    },
    
    cerrarPreview() {
      this.mostrandoPreview = false;
      this.htmlPreview = '';
    },
    
    guardarPlantilla() {
      this.emitirCambios();
      this.$emit('guardar');
    },
    
    emitirCambios() {
      this.$emit('actualizar', {
        bloques_contenido: JSON.stringify(this.bloquesConstructor),
        plantilla_base: this.plantillaSeleccionada?.id,
        configuracion_visual: this.configuracion
      });
    },
    
    generarId() {
      return 'bloque_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    },
    
    obtenerContenidoDefault(tipo) {
      const defaults = {
        titulo: { texto: 'Nuevo Título', nivel: 'h2' },
        imagen: { url: '', alt: 'Imagen del producto' },
        galeria: { imagenes: [] },
        descripcion: { texto: 'Nueva descripción...' },
        especificaciones: { specs: [] },
        caracteristicas: { features: [] },
        accesorios: { lista: [] },
        opcionales: { opciones: [] },
        garantia: { texto: 'Información de garantía...' },
        precio: { precio: 0, moneda: 'CLP' }
      };
      return defaults[tipo] || {};
    },
    
    obtenerNombreBloque(tipo) {
      const bloque = this.bloquesDisponibles.find(b => b.tipo === tipo);
      return bloque ? bloque.nombre : tipo;
    }
  }
}
</script>

<style scoped>
.constructor-visual {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.zona-drop-active {
  border-color: #3b82f6;
  background-color: rgba(59, 130, 246, 0.05);
}

.bloque-constructor {
  transition: all 0.2s ease;
}

.bloque-constructor:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.bloque-arrastre {
  cursor: move;
  user-select: none;
}

.bloque-arrastre:hover {
  transform: scale(1.02);
}

/* Estilos para los diferentes temas */
.tema-bioscom {
  --color-primario: #6284b8;
  --color-secundario: #5f87b8;
  --color-acento: #00334e;
}

.tema-minimalista {
  --color-primario: #1f2937;
  --color-secundario: #4b5563;
  --color-acento: #6b7280;
}

.tema-medico {
  --color-primario: #059669;
  --color-secundario: #10b981;
  --color-acento: #047857;
}

.tema-tecnologico {
  --color-primario: #3b82f6;
  --color-secundario: #60a5fa;
  --color-acento: #1d4ed8;
}
</style>