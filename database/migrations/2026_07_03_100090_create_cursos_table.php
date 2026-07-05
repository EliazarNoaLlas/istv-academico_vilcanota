<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->increments('id_curso');
            $table->unsignedInteger('id_docente')->nullable();
            $table->string('nombre_curso', 150);
            $table->string('modulo', 100);
            $table->string('semestre', 10);
            $table->integer('creditos');
            $table->integer('horas_teoria');
            $table->integer('horas_practica');
            $table->integer('horas_ud');
            $table->integer('total_teoria');
            $table->integer('total_practica');
            $table->integer('total_horas');
            $table->softDeletes();

            $table->foreign('id_docente')->references('id_docente')->on('docentes')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
