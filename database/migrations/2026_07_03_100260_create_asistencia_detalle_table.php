<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia_detalle', function (Blueprint $table) {
            $table->increments('id_asistencia');
            $table->unsignedInteger('id_sesion');
            $table->unsignedInteger('id_estudiante');
            $table->enum('estado', ['PRESENTE', 'TARDANZA', 'AUSENTE', 'JUSTIFICADO'])->default('PRESENTE');
            $table->string('observacion', 255)->nullable();
            $table->timestamp('fecha_registro')->useCurrent();

            $table->unique(['id_sesion', 'id_estudiante'], 'uk_asistencia_sesion_estudiante');
            $table->foreign('id_sesion')->references('id_sesion')->on('asistencia_sesiones')->cascadeOnDelete();
            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiantes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencia_detalle');
    }
};
