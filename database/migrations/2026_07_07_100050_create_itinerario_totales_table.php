<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itinerario_totales', function (Blueprint $table) {
            $table->increments('id_total');
            $table->unsignedInteger('id_itinerario');
            $table->unsignedInteger('id_modulo')->nullable();
            $table->unsignedInteger('id_bloque')->nullable();
            $table->enum('tipo_total', ['POR_CICLO', 'POR_BLOQUE', 'POR_MODULO', 'GENERAL']);
            $table->enum('ciclo', ['I', 'II', 'III', 'IV', 'V', 'VI'])->nullable();
            $table->integer('total_creditos')->default(0);
            $table->integer('total_horas_teoria')->default(0);
            $table->integer('total_horas_practica')->default(0);
            $table->integer('total_horas_ud')->default(0);
            $table->timestamps();

            $table->foreign('id_itinerario')->references('id_itinerario')->on('itinerarios_formativos')
                ->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreign('id_modulo')->references('id_modulo')->on('itinerario_modulos')
                ->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreign('id_bloque')->references('id_bloque')->on('itinerario_bloques')
                ->cascadeOnDelete()->cascadeOnUpdate();

            $table->index(['id_itinerario', 'tipo_total'], 'idx_total_itinerario_tipo');
            $table->index('id_modulo', 'idx_total_modulo');
            $table->index('id_bloque', 'idx_total_bloque');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itinerario_totales');
    }
};
