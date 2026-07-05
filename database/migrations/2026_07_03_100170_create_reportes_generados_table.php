<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_generados', function (Blueprint $table) {
            $table->increments('id_reporte');
            $table->unsignedInteger('id_usuario')->nullable();
            $table->enum('tipo', [
                'CURSOS', 'DOCENTES', 'ESTUDIANTES', 'HORARIOS', 'NOTAS',
                'PORTAFOLIO', 'CONSOLIDADO', 'IA_PREDICTIVA',
            ]);
            $table->string('titulo', 180);
            $table->enum('formato', ['PDF', 'EXCEL', 'CSV'])->default('PDF');
            $table->json('filtros_json')->nullable();
            $table->string('archivo', 255)->nullable();
            $table->timestamp('fecha_generacion')->useCurrent();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_generados');
    }
};
