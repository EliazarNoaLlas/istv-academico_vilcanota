<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->increments('id_horario');
            $table->unsignedInteger('id_curso');
            $table->unsignedInteger('id_docente');
            $table->string('dia', 20)->nullable();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->string('aula', 80)->nullable();
            $table->string('estado', 30)->default('Confirmado');

            $table->foreign('id_curso')->references('id_curso')->on('cursos');
            $table->foreign('id_docente')->references('id_docente')->on('docentes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
