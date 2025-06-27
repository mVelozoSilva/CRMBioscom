<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('formularios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('campo_formularios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formulario_id')->constrained()->onDelete('cascade');
            $table->string('tipo');
            $table->string('nombre');
            $table->string('etiqueta');
            $table->boolean('requerido')->default(false);
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('campo_formularios');
        Schema::dropIfExists('formularios');
    }
};