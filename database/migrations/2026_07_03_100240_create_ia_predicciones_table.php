<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ninguno de los 4 SQL legacy declara FKs para esta tabla; se agregan aqui
        // como mejora del esquema consolidado. La tabla esta vacia en produccion.
        Schema::create('ia_predicciones', function (Blueprint $table) {
            $table->increments('id_prediccion');
            $table->unsignedInteger('id_estudiante')->nullable();
            $table->unsignedInteger('id_curso')->nullable();
            $table->unsignedInteger('id_periodo')->nullable();
            $table->string('modelo', 80)->default('reglas-academicas-v1');
            $table->decimal('score_riesgo', 5, 2)->default(0);
            $table->decimal('probabilidad_aprobar', 5, 2)->nullable();
            $table->enum('nivel', ['BAJO', 'MEDIO', 'ALTO', 'CRITICO'])->default('MEDIO');
            $table->json('factores_json')->nullable();
            $table->json('simulacion_json')->nullable();
            $table->text('recomendacion')->nullable();
            $table->timestamp('fecha_prediccion')->useCurrent();

            $table->index('nivel');
            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiantes')->nullOnDelete();
            $table->foreign('id_curso')->references('id_curso')->on('cursos')->nullOnDelete();
            $table->foreign('id_periodo')->references('id_periodo')->on('periodos_academicos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ia_predicciones');
    }
};
