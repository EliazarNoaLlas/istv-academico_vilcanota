<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas_academicas', function (Blueprint $table) {
            $table->increments('id_alerta');
            $table->unsignedInteger('id_estudiante')->nullable();
            $table->unsignedInteger('id_docente')->nullable();
            $table->unsignedInteger('id_curso')->nullable();
            $table->enum('tipo', ['RIESGO_ACADEMICO', 'INASISTENCIA', 'PORTAFOLIO', 'HORARIO', 'SISTEMA']);
            $table->enum('severidad', ['BAJA', 'MEDIA', 'ALTA', 'CRITICA'])->default('MEDIA');
            $table->string('titulo', 150);
            $table->text('detalle')->nullable();
            $table->enum('estado', ['ABIERTA', 'EN_SEGUIMIENTO', 'CERRADA'])->default('ABIERTA');
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->dateTime('fecha_cierre')->nullable();

            $table->unique(['id_estudiante', 'tipo', 'titulo'], 'uk_alertas_contexto');
            $table->index('estado');
            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiantes')->nullOnDelete();
            $table->foreign('id_docente')->references('id_docente')->on('docentes')->nullOnDelete();
            $table->foreign('id_curso')->references('id_curso')->on('cursos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas_academicas');
    }
};
