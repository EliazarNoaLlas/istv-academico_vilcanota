<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itinerarios_formativos', function (Blueprint $table) {
            $table->increments('id_itinerario');
            $table->unsignedInteger('id_programa');
            $table->string('codigo', 50);
            $table->string('nombre', 180);
            $table->string('resolucion_oficio', 180)->nullable();
            $table->text('descripcion')->nullable();
            $table->tinyInteger('duracion_ciclos')->default(6);
            $table->integer('total_creditos')->default(0);
            $table->integer('total_horas')->default(0);
            $table->string('version', 30)->default('2026');
            $table->enum('estado', ['BORRADOR', 'ACTIVO', 'ARCHIVADO'])->default('BORRADOR');
            $table->date('fecha_aprobacion')->nullable();
            $table->timestamps();

            $table->foreign('id_programa')->references('id_programa')->on('programas_estudio')
                ->cascadeOnDelete()->cascadeOnUpdate();

            $table->unique(['id_programa', 'codigo', 'version'], 'uq_itinerario_programa_codigo_version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itinerarios_formativos');
    }
};
