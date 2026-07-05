<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aulas', function (Blueprint $table) {
            $table->increments('id_aula');
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 100);
            $table->enum('tipo', ['AULA', 'LABORATORIO', 'TALLER', 'CAMPO', 'OTRO'])->default('AULA');
            $table->integer('capacidad')->default(30);
            $table->string('ubicacion', 120)->nullable();
            $table->enum('estado', ['DISPONIBLE', 'MANTENIMIENTO', 'INACTIVO'])->default('DISPONIBLE');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aulas');
    }
};
