<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas', function (Blueprint $table) {
            $table->increments('id_nota');
            $table->unsignedInteger('id_matricula_curso');
            $table->string('unidad', 20)->default('I');
            $table->decimal('practica', 5, 2)->nullable();
            $table->decimal('teoria', 5, 2)->nullable();
            $table->decimal('examen', 5, 2)->nullable();
            $table->decimal('promedio', 5, 2)->storedAs(
                'ROUND((COALESCE(practica,0) * 0.20) + (COALESCE(teoria,0) * 0.30) + (COALESCE(examen,0) * 0.50), 2)'
            );
            $table->enum('estado', ['ABIERTO', 'CERRADO', 'RECTIFICADO'])->default('ABIERTO');
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamp('fecha_actualizacion')->nullable()->useCurrentOnUpdate();

            $table->unique(['id_matricula_curso', 'unidad'], 'uk_notas_matricula_unidad');
            $table->foreign('id_matricula_curso')->references('id_matricula_curso')->on('matricula_cursos')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas');
    }
};
