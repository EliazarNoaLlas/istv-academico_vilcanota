<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriculas', function (Blueprint $table) {
            $table->increments('id_matricula');
            $table->unsignedInteger('id_estudiante');
            $table->unsignedInteger('id_periodo');
            $table->enum('ciclo', ['I', 'II', 'III', 'IV', 'V', 'VI']);
            $table->enum('estado', ['MATRICULADO', 'RESERVA', 'RETIRADO', 'CERRADO'])->default('MATRICULADO');
            $table->date('fecha_matricula');

            $table->unique(['id_estudiante', 'id_periodo'], 'uk_matriculas_estudiante_periodo');
            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiantes')->cascadeOnDelete();
            $table->foreign('id_periodo')->references('id_periodo')->on('periodos_academicos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};
