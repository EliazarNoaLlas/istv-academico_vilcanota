<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docente_disponibilidades', function (Blueprint $table) {
            $table->increments('id_disponibilidad');

            $table->unsignedInteger('id_docente');
            $table->enum('dia', ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes']);

            $table->time('hora_inicio');
            $table->time('hora_fin');

            $table->enum('tipo', ['DISPONIBLE', 'NO_DISPONIBLE', 'PREFERENCIA'])->default('DISPONIBLE');
            $table->string('motivo', 180)->nullable();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');

            $table->timestamps();

            $table->foreign('id_docente')->references('id_docente')->on('docentes')->cascadeOnDelete();
            $table->index(['id_docente', 'dia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docente_disponibilidades');
    }
};
