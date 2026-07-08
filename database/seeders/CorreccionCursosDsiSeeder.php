<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Correccion de datos del programa DSI (Fase 1 y Fase 2 del diagnostico de
 * horarios): archiva cursos duplicados/obsoletos y asigna los docentes
 * faltantes del semestre VI. Idempotente: cada paso solo actua si el curso
 * sigue en el estado que motivo la correccion, por lo que reasignaciones
 * manuales posteriores no se sobrescriben en una segunda ejecucion.
 */
class CorreccionCursosDsiSeeder extends Seeder
{
    private const ID_PROGRAMA_DSI = 1;

    public function run(): void
    {
        DB::transaction(function () {
            $this->renombrarCursoOficial();
            $this->archivarDuplicadoInterpretacionTextos();
            $this->archivarCursosSinProgramaVI();
            $this->asignarDocentesSemestreVI();
        });
    }

    /**
     * ItinerarioProgramaSeeder::sincronizarCursos() vincula unidades por
     * nombre_curso exacto. El curso 11 ("Interpretacion y produccion
     * textos", sin "de") no coincide con el nombre oficial del itinerario
     * ("... de textos"), por eso cada ItinerarioDsiSeeder crea el duplicado
     * 38 de nuevo. Renombrar el 11 al nombre oficial evita que reaparezca.
     */
    private function renombrarCursoOficial(): void
    {
        DB::table('cursos')
            ->where('id_curso', 11)
            ->where('id_programa', self::ID_PROGRAMA_DSI)
            ->update(['nombre_curso' => 'Interpretación y producción de textos']);
    }

    /**
     * Curso 38: duplicado generado por el itinerario, sin docente, sin
     * matriculas ni horarios (verificado). El curso 11 es el real: tiene
     * matricula activa y docente. Se archiva con nombre distinto para que
     * el proximo ItinerarioDsiSeeder no lo reconozca por nombre y lo
     * reactive via su logica de "restore si esta trashed".
     */
    private function archivarDuplicadoInterpretacionTextos(): void
    {
        DB::table('cursos')
            ->where('id_curso', 38)
            ->where('id_programa', self::ID_PROGRAMA_DSI)
            ->whereNull('id_docente')
            ->where('estado', '!=', 'ARCHIVADO')
            ->update([
                'nombre_curso' => 'Interpretación y producción de textos (duplicado archivado)',
                'estado' => 'ARCHIVADO',
                'deleted_at' => now(),
            ]);
    }

    /**
     * Cursos 29-35: version antigua sin id_programa del semestre VI, ya
     * reemplazada por los cursos 40-46 (id_programa = 1). Verificado sin
     * referencias en horarios, matricula_cursos ni portafolio_docente.
     */
    private function archivarCursosSinProgramaVI(): void
    {
        DB::table('cursos')
            ->whereIn('id_curso', [29, 30, 31, 32, 33, 34, 35])
            ->whereNull('id_programa')
            ->where('estado', '!=', 'ARCHIVADO')
            ->update([
                'estado' => 'ARCHIVADO',
                'deleted_at' => now(),
            ]);
    }

    /**
     * Reparto balanceado entre los dos docentes con 0 cursos previos y
     * especialidad Desarrollo de Software (Emiliano Mendoza id 11, Vladimir
     * Florez id 12), sin superar 20 bloques semanales para ninguno
     * (quedan en 12 y 11 respectivamente). Hernan Palomino (id 3) se
     * excluye a proposito: ya esta en el limite de 20 bloques.
     */
    private function asignarDocentesSemestreVI(): void
    {
        $asignaciones = [
            39 => 11, // ESRT -> Emiliano Mendoza
            41 => 11, // Gestion de redes informaticas -> Emiliano Mendoza
            43 => 11, // Auditoria de software -> Emiliano Mendoza
            45 => 11, // Solucion de problemas -> Emiliano Mendoza
            40 => 12, // Gestion de servidores -> Vladimir Florez
            42 => 12, // Soporte de auditoria de TI -> Vladimir Florez
            44 => 12, // Inteligencia artificial -> Vladimir Florez
            46 => 12, // Innovacion tecnologica -> Vladimir Florez
        ];

        foreach ($asignaciones as $idCurso => $idDocente) {
            DB::table('cursos')
                ->where('id_curso', $idCurso)
                ->where('id_programa', self::ID_PROGRAMA_DSI)
                ->whereNull('id_docente')
                ->update(['id_docente' => $idDocente]);
        }
    }
}
