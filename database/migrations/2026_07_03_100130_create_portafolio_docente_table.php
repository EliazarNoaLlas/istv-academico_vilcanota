<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portafolio_docente', function (Blueprint $table) {
            $table->increments('id_portafolio');
            $table->unsignedInteger('id_docente');
            $table->unsignedInteger('id_curso');
            $table->unsignedInteger('id_periodo');
            $table->enum('silabo', ['PENDIENTE', 'EN_REVISION', 'APROBADO', 'OBSERVADO'])->default('PENDIENTE');
            $table->enum('sesiones', ['PENDIENTE', 'EN_REVISION', 'APROBADO', 'OBSERVADO'])->default('PENDIENTE');
            $table->enum('registro_asistencia', ['PENDIENTE', 'EN_REVISION', 'APROBADO', 'OBSERVADO'])->default('PENDIENTE');
            $table->enum('registro_notas', ['PENDIENTE', 'EN_REVISION', 'APROBADO', 'OBSERVADO'])->default('PENDIENTE');
            $table->enum('actas', ['PENDIENTE', 'EN_REVISION', 'APROBADO', 'OBSERVADO'])->default('PENDIENTE');
            $table->enum('estado', ['INCOMPLETO', 'EN_REVISION', 'COMPLETO', 'OBSERVADO'])->default('INCOMPLETO');
            $table->text('observacion')->nullable();
            $table->timestamp('fecha_actualizacion')->nullable()->useCurrentOnUpdate();

            $table->unique(['id_docente', 'id_curso', 'id_periodo'], 'uk_portafolio_docente_curso_periodo');
            $table->foreign('id_docente')->references('id_docente')->on('docentes');
            $table->foreign('id_curso')->references('id_curso')->on('cursos');
            $table->foreign('id_periodo')->references('id_periodo')->on('periodos_academicos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portafolio_docente');
    }
};
