<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesiones_aprendizaje', function (Blueprint $table) {
            $table->increments('id_sesion');
            $table->unsignedInteger('id_curso');
            $table->unsignedInteger('id_docente');
            $table->string('titulo', 255);
            $table->string('archivo', 255);
            $table->integer('numero_sesion')->nullable();
            $table->enum('estado', ['PENDIENTE', 'EN_REVISION', 'APROBADO', 'RECHAZADO'])->default('PENDIENTE');
            $table->dateTime('fecha_subida')->useCurrent();

            $table->foreign('id_curso', 'fk_sa_curso')->references('id_curso')->on('cursos')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('id_docente', 'fk_sa_docente')->references('id_docente')->on('docentes')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesiones_aprendizaje');
    }
};
