<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portafolio_documentos', function (Blueprint $table) {
            $table->increments('id_documento');
            $table->unsignedInteger('id_portafolio');
            $table->enum('tipo', [
                'SILABO', 'PLAN_SESION', 'EVALUACION', 'INSTRUMENTO',
                'ASISTENCIA', 'NOTAS', 'EVIDENCIA', 'ACTA', 'OTRO',
            ]);
            $table->string('titulo', 180);
            $table->string('archivo', 255)->nullable();
            $table->enum('estado', ['PENDIENTE', 'SUBIDO', 'APROBADO', 'OBSERVADO'])->default('PENDIENTE');
            $table->text('observacion')->nullable();
            $table->dateTime('fecha_subida')->nullable();
            $table->softDeletes();

            $table->unique(['id_portafolio', 'tipo', 'titulo'], 'uk_portafolio_documento_tipo');
            $table->foreign('id_portafolio')->references('id_portafolio')->on('portafolio_docente')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portafolio_documentos');
    }
};
