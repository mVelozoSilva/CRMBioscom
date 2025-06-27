<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('servicio_tecnicos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('estado');
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('servicio_tecnicos');
    }
};