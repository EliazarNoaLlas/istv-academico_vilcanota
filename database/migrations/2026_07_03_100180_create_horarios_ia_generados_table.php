<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_ia_generados', function (Blueprint $table) {
            $table->increments('id_generacion');
            $table->unsignedInteger('id_usuario')->nullable();
            $table->unsignedInteger('id_periodo')->nullable();
            $table->string('programa', 150)->nullable();
            $table->string('modelo', 80)->nullable();
            $table->text('prompt_resumen')->nullable();
            $table->json('resultado_json')->nullable();
            $table->enum('estado', ['BORRADOR', 'APROBADO', 'DESCARTADO'])->default('BORRADOR');
            $table->timestamp('fecha_generacion')->useCurrent();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->nullOnDelete();
            $table->foreign('id_periodo')->references('id_periodo')->on('periodos_academicos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_ia_generados');
    }
};
