<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matricula_cursos', function (Blueprint $table) {
            $table->increments('id_matricula_curso');
            $table->unsignedInteger('id_matricula');
            $table->unsignedInteger('id_curso');
            $table->enum('estado', ['EN_CURSO', 'APROBADO', 'DESAPROBADO', 'RETIRADO'])->default('EN_CURSO');

            $table->unique(['id_matricula', 'id_curso'], 'uk_matricula_curso');
            $table->foreign('id_matricula')->references('id_matricula')->on('matriculas')->cascadeOnDelete();
            $table->foreign('id_curso')->references('id_curso')->on('cursos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matricula_cursos');
    }
};
