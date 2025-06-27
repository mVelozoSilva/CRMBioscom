<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    if (!Schema::hasTable('cobranzas')) return;

    // Laravel ya no necesita crearla. Esto es un placeholder.
}

public function down()
{
    // NO eliminamos la tabla si ya existía
}

};
