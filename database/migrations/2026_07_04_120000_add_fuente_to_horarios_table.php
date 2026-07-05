<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Unico campo que el esquema realmente necesitaba (ver Fase 7 /
        // reporte de Horarios): permite distinguir bloques generados por IA
        // de los manuales cuando la Fase 8 implemente la generacion real.
        // No se agrega id_programa/id_periodo/semestre/color: todos se
        // derivan sin duplicar datos via la relacion horarios->cursos.
        Schema::table('horarios', function (Blueprint $table) {
            $table->enum('fuente', ['MANUAL', 'IA'])->default('MANUAL')->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropColumn('fuente');
        });
    }
};
