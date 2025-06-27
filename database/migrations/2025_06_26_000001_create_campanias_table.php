<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('campanias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo');
            $table->text('descripcion')->nullable();
            $table->string('estado')->default('activa');
            $table->json('programacion')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('campanias');
    }
};