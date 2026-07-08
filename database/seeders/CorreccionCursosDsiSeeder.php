<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Correccion de datos del programa DSI (Fase 1 y Fase 2 del diagnostico de
 * horarios): archiva cursos duplicados/obsoletos y asigna los docentes
 * faltantes del semestre VI. Resuelve todo por nombre_curso/semestre/
 * codigo_docente (nunca por id_curso/id_docente fijo) para funcionar igual
 * en cualquier base de datos, sin importar el orden de auto_increment con
 * que haya quedado cada instalacion. Idempotente: cada paso solo actua si
 * el curso sigue en el estado que motivo la correccion.
 */
class CorreccionCursosDsiSeeder extends Seeder
{
    private const ID_PROGRAMA_DSI = 1;

    /** [nombre_curso, codigo_docente] del semestre VI. */
    private const ASIGNACIONES_VI = [
        ['Experiencias formativas en situaciones reales de trabajo (ESRT)', 'DOC013'], // Emiliano Mendoza
        ['Gestión de redes informáticas', 'DOC013'], // Emiliano Mendoza
        ['Auditoría de software', 'DOC013'], // Emiliano Mendoza
        ['Solución de problemas', 'DOC013'], // Emiliano Mendoza
        ['Gestión de servidores', 'DOC014'], // Vladimir Florez
        ['Soporte de auditoría de TI', 'DOC014'], // Vladimir Florez
        ['Inteligencia artificial', 'DOC014'], // Vladimir Florez
        ['Innovación tecnológica', 'DOC014'], // Vladimir Florez
    ];

    /** Nombres de los cursos legado de VI con id_programa NULL, ya reemplazados por sus pares en id_programa = 1. */
    private const NOMBRES_CURSOS_VI_LEGACY = [
        'Gestión de servidores',
        'Gestión de redes informáticas',
        'Soporte de auditoría de TI',
        'Auditoría de software',
        'Inteligencia artificial',
        'Solución de problemas',
        'Innovación tecnológica',
    ];

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
     * nombre_curso exacto (salvo tildes, la collation es insensible a
     * ellas). El nombre legado "Interpretacion y produccion textos" (sin
     * "de") no coincide con el oficial ("... de textos"): si existe, se
     * renombra para unificarlo con el curso real y que el duplicado no
     * vuelva a crearse en un futuro re-seed del itinerario.
     */
    private function renombrarCursoOficial(): void
    {
        DB::table('cursos')
            ->where('id_programa', self::ID_PROGRAMA_DSI)
            ->where('semestre', 'II')
            ->where('nombre_curso', 'Interpretación y producción textos')
            ->update(['nombre_curso' => 'Interpretación y producción de textos']);
    }

    /**
     * Si tras renombrar quedan dos (o mas) cursos activos con el mismo
     * nombre oficial en semestre II, conserva el que tiene docente
     * asignado (el real, con matricula) y archiva el resto con nombre
     * distinto para que no vuelvan a coincidir con el itinerario oficial.
     */
    private function archivarDuplicadoInterpretacionTextos(): void
    {
        $duplicados = DB::table('cursos')
            ->where('id_programa', self::ID_PROGRAMA_DSI)
            ->where('semestre', 'II')
            ->where('nombre_curso', 'Interpretación y producción de textos')
            ->where('estado', '!=', 'ARCHIVADO')
            ->orderBy('id_curso')
            ->get(['id_curso', 'id_docente']);

        if ($duplicados->count() < 2) {
            return;
        }

        $aConservar = $duplicados->first(fn ($c) => $c->id_docente !== null) ?? $duplicados->first();

        DB::table('cursos')
            ->where('id_programa', self::ID_PROGRAMA_DSI)
            ->where('semestre', 'II')
            ->where('nombre_curso', 'Interpretación y producción de textos')
            ->where('id_curso', '!=', $aConservar->id_curso)
            ->update([
                'nombre_curso' => 'Interpretación y producción de textos (duplicado archivado)',
                'estado' => 'ARCHIVADO',
                'deleted_at' => now(),
            ]);
    }

    /**
     * Cursos de VI con id_programa NULL: version antigua ya reemplazada
     * por sus pares en id_programa = 1. Verificado sin referencias en
     * horarios, matricula_cursos ni portafolio_docente.
     */
    private function archivarCursosSinProgramaVI(): void
    {
        DB::table('cursos')
            ->whereIn('nombre_curso', self::NOMBRES_CURSOS_VI_LEGACY)
            ->whereNull('id_programa')
            ->where('semestre', 'VI')
            ->where('estado', '!=', 'ARCHIVADO')
            ->update([
                'estado' => 'ARCHIVADO',
                'deleted_at' => now(),
            ]);
    }

    /**
     * Reparto balanceado entre los dos docentes con 0 cursos previos y
     * especialidad Desarrollo de Software (Emiliano Mendoza, Vladimir
     * Florez), sin superar 20 bloques semanales para ninguno. Hernan
     * Palomino se excluye a proposito: ya esta en el limite de 20 bloques.
     */
    private function asignarDocentesSemestreVI(): void
    {
        $docentesPorCodigo = DB::table('docentes')->pluck('id_docente', 'codigo_docente');

        foreach (self::ASIGNACIONES_VI as [$nombreCurso, $codigoDocente]) {
            $idDocente = $docentesPorCodigo[$codigoDocente] ?? null;

            if (! $idDocente) {
                continue;
            }

            DB::table('cursos')
                ->where('id_programa', self::ID_PROGRAMA_DSI)
                ->where('semestre', 'VI')
                ->where('nombre_curso', $nombreCurso)
                ->whereNull('id_docente')
                ->update(['id_docente' => $idDocente]);
        }
    }
}
