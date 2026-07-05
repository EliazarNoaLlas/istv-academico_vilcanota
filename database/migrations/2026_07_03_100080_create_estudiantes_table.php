<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->increments('id_estudiante');
            $table->string('codigo_estudiante', 20)->unique();
            $table->char('dni', 8)->nullable()->unique();
            $table->string('nombres', 120);
            $table->string('apellido_paterno', 80)->nullable();
            $table->string('apellido_materno', 80)->nullable();
            $table->string('correo', 150)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->unsignedInteger('id_programa');
            $table->enum('ciclo', ['I', 'II', 'III', 'IV', 'V', 'VI'])->default('I');
            $table->enum('estado', ['REGULAR', 'OBSERVADO', 'RIESGO', 'RETIRADO', 'EGRESADO'])->default('REGULAR');
            $table->timestamp('fecha_registro')->useCurrent();
            $table->softDeletes();

            $table->foreign('id_programa')->references('id_programa')->on('programas_estudio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
