<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->unsignedInteger('id_aula')->nullable()->after('id_docente');
            $table->unsignedInteger('id_periodo')->nullable()->after('id_aula');
            $table->unsignedInteger('id_programa')->nullable()->after('id_periodo');
            // varchar(10), igual que cursos.semestre (esa tabla no usa enum para este campo).
            $table->string('semestre', 10)->nullable()->after('id_programa');
            $table->text('observacion')->nullable()->after('fuente');

            $table->foreign('id_aula')->references('id_aula')->on('aulas')->nullOnDelete();
            $table->foreign('id_periodo')->references('id_periodo')->on('periodos_academicos')->nullOnDelete();
            $table->foreign('id_programa')->references('id_programa')->on('programas_estudio')->nullOnDelete();

            $table->index(['id_docente', 'dia', 'hora_inicio', 'hora_fin'], 'idx_horarios_docente_slot');
            $table->index(['id_aula', 'dia', 'hora_inicio', 'hora_fin'], 'idx_horarios_aula_slot');
            $table->index(['id_programa', 'semestre', 'dia'], 'idx_horarios_programa_semestre_dia');
        });

        $this->poblarDesdeCursosYAulas();
    }

    private function poblarDesdeCursosYAulas(): void
    {
        DB::statement('
            UPDATE horarios h
            INNER JOIN cursos c ON c.id_curso = h.id_curso
            SET h.id_programa = c.id_programa, h.semestre = c.semestre
        ');

        // Coincidencia exacta con aulas.codigo o aulas.nombre. Los valores de
        // horarios.aula que no tengan fila real en aulas quedan con id_aula
        // NULL a proposito: no se inventa una relacion que no existe.
        DB::statement('
            UPDATE horarios h
            INNER JOIN aulas a ON a.codigo = h.aula OR a.nombre = h.aula
            SET h.id_aula = a.id_aula
        ');

        $idPeriodoActivo = DB::table('periodos_academicos')->where('estado', 'ACTIVO')->value('id_periodo');

        if ($idPeriodoActivo) {
            DB::table('horarios')->whereNull('id_periodo')->update(['id_periodo' => $idPeriodoActivo]);
        }
    }

    public function down(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropForeign(['id_aula']);
            $table->dropForeign(['id_periodo']);
            $table->dropForeign(['id_programa']);

            $table->dropIndex('idx_horarios_docente_slot');
            $table->dropIndex('idx_horarios_aula_slot');
            $table->dropIndex('idx_horarios_programa_semestre_dia');

            $table->dropColumn(['id_aula', 'id_periodo', 'id_programa', 'semestre', 'observacion']);
        });
    }
};
