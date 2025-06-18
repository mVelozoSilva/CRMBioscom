    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::table('tareas', function (Blueprint $table) {
                // Primero, verificar si la columna 'usuario_id' existe.
                // Si la columna ya existe, asumimos que fue renombrada y no necesita ser añadida.
                // Este fix es más para la clave foránea duplicada.

                // CRÍTICO: Primero elimina la clave foránea si existe para evitar duplicados.
                // Usamos el nombre por defecto que Laravel genera para las FKs.
                if (Schema::hasColumn('tareas', 'usuario_id') && Schema::hasTable('users')) {
                    // Verificar si la clave foránea existe antes de intentar eliminarla
                    // No hay un Schema::hasForeignKey, así que intentamos eliminar y controlamos el error.
                    try {
                        $table->dropForeign(['usuario_id']);
                    } catch (\Exception $e) {
                        // Ignorar si la FK no existe, esto es para evitar errores al intentar eliminar una FK inexistente
                    }
                }
                
                // Asegurarse de que la columna usuario_id sea unsignedBigInteger
                // y que se pueda modificar (after: para orden, nullable: si es necesario)
                if (Schema::hasColumn('tareas', 'usuario_id')) {
                    $table->unsignedBigInteger('usuario_id')->change(); // Cambiar tipo de columna
                } else {
                    $table->unsignedBigInteger('usuario_id')->after('seguimiento_id')->nullable(); // Añadir si no existe, ajustar posición
                }


                // AÑADIR LA CLAVE FORÁNEA. Si el problema era la FK duplicada,
                // la línea dropForeign() de arriba lo resuelve.
                // Si la columna 'usuario_id' no existe, añadirla primero.
                if (Schema::hasColumn('tareas', 'usuario_id') && Schema::hasTable('users')) {
                    // Solo añadir la clave foránea si aún no existe y las tablas referenciadas existen
                    // Laravel no genera dos FKs con el mismo nombre por defecto si la migración se escribe bien.
                    // El error "Duplicate foreign key constraint name" es porque una migración anterior ya la creó
                    // o esta la está creando dos veces.
                    $table->foreign('usuario_id')
                          ->references('id')->on('users')
                          ->onDelete('cascade');
                }
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('tareas', function (Blueprint $table) {
                // Revertir los cambios hechos en el método 'up'
                if (Schema::hasColumn('tareas', 'usuario_id')) {
                    // Primero eliminar la clave foránea antes de eliminar la columna
                    try {
                        $table->dropForeign(['usuario_id']);
                    } catch (\Exception $e) {
                        // Ignorar si la FK no existe
                    }
                    $table->dropColumn('usuario_id');
                }
            });
        }
    };
    