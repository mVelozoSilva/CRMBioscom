<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('configuracion_supervisores', function (Blueprint $table) {
            $table->id();
            $table->boolean('modo_oscuro')->default(false);
            $table->boolean('contraste_alto')->default(false);
            $table->string('tamano_fuente')->default('mediana');
            $table->boolean('activar_alertas')->default(true);
            $table->string('orden_prioridad')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('configuracion_supervisores');
    }
};