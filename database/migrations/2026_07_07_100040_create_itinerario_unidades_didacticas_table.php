<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itinerario_unidades_didacticas', function (Blueprint $table) {
            $table->increments('id_unidad');
            $table->unsignedInteger('id_bloque');
            $table->unsignedInteger('id_curso')->nullable();
            $table->string('nombre', 180);
            $table->string('codigo', 50)->nullable();
            $table->enum('ciclo', ['I', 'II', 'III', 'IV', 'V', 'VI']);
            $table->integer('horas_ciclo')->default(0);
            $table->integer('horas_teoricas_semanales')->default(0);
            $table->integer('horas_practicas_semanales')->default(0);
            $table->integer('creditos')->default(0);
            $table->integer('total_horas_teoria')->default(0);
            $table->integer('total_horas_practica')->default(0);
            $table->integer('horas_ud')->default(0);
            $table->integer('orden')->default(1);
            $table->boolean('es_editable')->default(true);
            $table->text('observacion')->nullable();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();

            $table->foreign('id_bloque')->references('id_bloque')->on('itinerario_bloques')
                ->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreign('id_curso')->references('id_curso')->on('cursos')
                ->nullOnDelete()->cascadeOnUpdate();

            $table->index(['id_bloque', 'ciclo'], 'idx_unidad_bloque_ciclo');
            $table->index('ciclo', 'idx_unidad_ciclo');
            $table->index('estado', 'idx_unidad_estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itinerario_unidades_didacticas');
    }
};
