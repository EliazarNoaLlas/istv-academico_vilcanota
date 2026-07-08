<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Normaliza los 60 horarios existentes (Semestre I y III, programa DSI) que
 * quedaron con id_programa, semestre, id_periodo e id_aula en NULL porque el
 * backfill de la migracion 2026_07_06_000040_add_columns_to_horarios_table
 * corrio antes de que cursos.id_programa tuviera datos. No crea, borra ni
 * mueve bloques: solo completa columnas de metadata vacias. Idempotente:
 * cada paso solo actua sobre filas que siguen NULL.
 */
class BackfillHorariosDsiSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->backfillProgramaYSemestre();
            $this->backfillPeriodoActivo();
            $this->backfillAula();
        });

        $this->reportarAulasSinCoincidencia();
    }

    /**
     * Copia id_programa y semestre desde el curso vinculado. Excluye cursos
     * archivados/eliminados y cursos sin id_programa (regla: no usar cursos
     * con id_programa NULL para completar horarios DSI).
     */
    private function backfillProgramaYSemestre(): void
    {
        DB::statement("
            UPDATE horarios h
            INNER JOIN cursos c ON c.id_curso = h.id_curso
            SET h.id_programa = c.id_programa,
                h.semestre = c.semestre
            WHERE (h.id_programa IS NULL OR h.semestre IS NULL)
              AND c.id_programa IS NOT NULL
              AND c.estado <> 'ARCHIVADO'
              AND c.deleted_at IS NULL
        ");
    }

    /** Asigna el periodo academico activo (2026-I) a los horarios que no tienen periodo. */
    private function backfillPeriodoActivo(): void
    {
        $idPeriodoActivo = DB::table('periodos_academicos')->where('estado', 'ACTIVO')->value('id_periodo');

        if ($idPeriodoActivo) {
            DB::table('horarios')->whereNull('id_periodo')->update(['id_periodo' => $idPeriodoActivo]);
        }
    }

    /**
     * Cruza horarios.aula (texto libre) contra aulas.codigo o aulas.nombre.
     * Donde no hay coincidencia exacta, id_aula queda en NULL a proposito
     * (no se inventa una relacion que no existe) y se reporta al final.
     */
    private function backfillAula(): void
    {
        DB::statement("
            UPDATE horarios h
            INNER JOIN aulas a ON (a.codigo = h.aula OR a.nombre = h.aula)
            SET h.id_aula = a.id_aula
            WHERE h.id_aula IS NULL
        ");
    }

    private function reportarAulasSinCoincidencia(): void
    {
        $sinCoincidencia = DB::table('horarios')
            ->whereNull('id_aula')
            ->whereNotNull('aula')
            ->distinct()
            ->pluck('aula');

        if ($sinCoincidencia->isNotEmpty()) {
            $this->command?->warn(
                'horarios.aula sin coincidencia en tabla aulas (id_aula quedo NULL): '.$sinCoincidencia->implode(', ')
            );
        }
    }
}
