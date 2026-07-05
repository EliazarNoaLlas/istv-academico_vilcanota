<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia_sesiones', function (Blueprint $table) {
            $table->increments('id_sesion');
            $table->unsignedInteger('id_curso');
            $table->unsignedInteger('id_docente');
            $table->unsignedInteger('id_horario')->nullable();
            $table->unsignedInteger('id_periodo');
            $table->date('fecha_sesion');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->string('tema', 180)->nullable();
            $table->enum('estado', ['PROGRAMADA', 'REALIZADA', 'SUSPENDIDA'])->default('PROGRAMADA');

            $table->foreign('id_curso')->references('id_curso')->on('cursos');
            $table->foreign('id_docente')->references('id_docente')->on('docentes');
            $table->foreign('id_horario')->references('id_horario')->on('horarios')->nullOnDelete();
            $table->foreign('id_periodo')->references('id_periodo')->on('periodos_academicos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencia_sesiones');
    }
};
