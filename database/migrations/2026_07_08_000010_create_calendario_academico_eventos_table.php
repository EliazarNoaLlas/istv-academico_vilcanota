<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendario_academico_eventos', function (Blueprint $table) {
            $table->increments('id_evento');
            $table->unsignedInteger('id_periodo')->nullable();
            $table->unsignedInteger('id_usuario_creador')->nullable();
            $table->string('titulo', 180);
            $table->enum('tipo', ['EVALUACION', 'FERIADO', 'PLAZO_ADMINISTRATIVO', 'MATRICULA', 'REUNION_CAPACITACION'])->default('REUNION_CAPACITACION');
            $table->date('fecha');
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->foreign('id_periodo')->references('id_periodo')->on('periodos_academicos')
                ->nullOnDelete();
            $table->foreign('id_usuario_creador')->references('id_usuario')->on('usuarios')
                ->nullOnDelete();

            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendario_academico_eventos');
    }
};
