<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programas_estudio', function (Blueprint $table) {
            $table->increments('id_programa');
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 150);
            $table->string('familia_profesional', 120)->nullable();
            $table->tinyInteger('duracion_ciclos')->default(6);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programas_estudio');
    }
};
