<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->unsignedInteger('id_programa')->nullable()->after('id_curso');
            $table->enum('estado', ['ACTIVO', 'INACTIVO', 'ARCHIVADO'])->default('ACTIVO')->after('total_horas');

            $table->foreign('id_programa')->references('id_programa')->on('programas_estudio')->nullOnDelete();
            $table->index('id_programa');
        });
    }

    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropForeign(['id_programa']);
            $table->dropColumn(['id_programa', 'estado']);
        });
    }
};
