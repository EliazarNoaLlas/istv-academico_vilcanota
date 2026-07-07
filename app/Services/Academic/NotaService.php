<?php

namespace App\Services\Academic;

use App\Models\ConfiguracionSistema;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\MatriculaCurso;
use App\Models\Nota;
use App\Models\PeriodoAcademico;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class NotaService
{
    /**
     * Estudiantes del curso segun su semestre/ciclo (igual que Asistencia): el curso.semestre indica
     * en que ciclo se dicta, y se listan los estudiantes de ese mismo programa y ciclo. Como la tabla
     * notas exige un id_matricula_curso real (llave foranea), se asegura una matricula minima aqui
     * mismo (no hay todavia una pantalla de matricula real en el sistema).
     */
    private function asegurarMatriculaCurso(Curso $curso, Estudiante $estudiante): MatriculaCurso
    {
        $idPeriodo = PeriodoAcademico::where('estado', 'ACTIVO')->value('id_periodo');

        $matricula = Matricula::firstOrCreate(
            ['id_estudiante' => $estudiante->id_estudiante, 'id_periodo' => $idPeriodo],
            ['ciclo' => $estudiante->ciclo, 'estado' => 'MATRICULADO', 'fecha_matricula' => now()],
        );

        return MatriculaCurso::firstOrCreate(
            ['id_matricula' => $matricula->id_matricula, 'id_curso' => $curso->id_curso],
            ['estado' => 'EN_CURSO'],
        );
    }

    /** Roster del curso (por semestre) con la nota del parcial indicado (si existe) por cada estudiante, y el resumen de la clase. */
    public function estudiantesDeCurso(Curso $curso, string $unidad = 'I'): array
    {
        $estudiantesDelCurso = Estudiante::where('id_programa', $curso->id_programa)
            ->where('ciclo', $curso->semestre)
            ->whereNotIn('estado', ['RETIRADO', 'EGRESADO'])
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombres')
            ->get();

        $estudiantes = $estudiantesDelCurso->map(function (Estudiante $estudiante) use ($curso, $unidad) {
            $matriculaCurso = $this->asegurarMatriculaCurso($curso, $estudiante);
            $nota = Nota::where('id_matricula_curso', $matriculaCurso->id_matricula_curso)->where('unidad', $unidad)->first();

            // La columna "promedio" es una columna generada en BD que da 0.00 (no null) cuando
            // practica/teoria/examen estan las 3 vacias; se corrige aqui para no confundir "sin
            // calificar todavia" con "desaprobado con 0".
            $sinCalificar = ! $nota || ($nota->practica === null && $nota->teoria === null && $nota->examen === null);

            return [
                'id_matricula_curso' => $matriculaCurso->id_matricula_curso,
                'estudiante' => $estudiante,
                'unidad' => $unidad,
                'id_nota' => $nota?->id_nota,
                'practica' => $nota?->practica,
                'teoria' => $nota?->teoria,
                'examen' => $nota?->examen,
                'promedio' => $sinCalificar ? null : $nota->promedio,
                'estado' => $nota?->estado,
            ];
        })->values();

        $notaMinima = (float) (ConfiguracionSistema::where('clave', 'nota_minima_aprobatoria')->value('valor') ?? 10.5);
        $promedios = $estudiantes->pluck('promedio')->filter(fn ($p) => $p !== null)->map(fn ($p) => (float) $p);

        return [
            'estudiantes' => $estudiantes,
            'resumen' => [
                'promedio_clase' => $promedios->isNotEmpty() ? round($promedios->avg(), 1) : null,
                'nota_mas_alta' => $promedios->isNotEmpty() ? round($promedios->max(), 1) : null,
                'nota_mas_baja' => $promedios->isNotEmpty() ? round($promedios->min(), 1) : null,
                'desaprobados' => $promedios->filter(fn ($p) => $p < $notaMinima)->count(),
                'nota_minima' => $notaMinima,
                'acta_cerrada' => $estudiantes->contains(fn ($e) => $e['estado'] === 'CERRADO'),
            ],
        ];
    }

    /**
     * Guarda todas las filas del "Registro de notas" en una sola transaccion (en vez de una peticion
     * por estudiante): mas confiable que disparar N peticiones simultaneas al hacer clic en "Guardar".
     *
     * @param  array<int, array{id_matricula_curso:int, practica:?float, teoria:?float, examen:?float}>  $filas
     */
    public function guardarNotasLote(Curso $curso, string $unidad, array $filas): void
    {
        $idsValidos = MatriculaCurso::where('id_curso', $curso->id_curso)->pluck('id_matricula_curso');

        DB::transaction(function () use ($filas, $unidad, $idsValidos) {
            foreach ($filas as $fila) {
                abort_unless($idsValidos->contains($fila['id_matricula_curso']), 403, 'Uno de los estudiantes no pertenece a este curso.');

                Nota::updateOrCreate(
                    ['id_matricula_curso' => $fila['id_matricula_curso'], 'unidad' => $unidad],
                    ['practica' => $fila['practica'] ?? null, 'teoria' => $fila['teoria'] ?? null, 'examen' => $fila['examen'] ?? null],
                );
            }
        });
    }

    /** El coordinador reabre el acta (vuelve a ABIERTO) para que el docente pueda modificar notas que ya habia cerrado. */
    public function reabrirActa(Curso $curso, string $unidad): int
    {
        $ids = MatriculaCurso::where('id_curso', $curso->id_curso)->pluck('id_matricula_curso');

        return Nota::whereIn('id_matricula_curso', $ids)->where('unidad', $unidad)->update(['estado' => 'ABIERTO']);
    }

    public function listar(?int $idCurso = null, ?string $unidad = null): Collection
    {
        return Nota::query()
            ->when($idCurso, fn ($q) => $q->whereHas('matriculaCurso', fn ($mc) => $mc->where('id_curso', $idCurso)))
            ->when($unidad, fn ($q) => $q->where('unidad', $unidad))
            ->with(['matriculaCurso.curso', 'matriculaCurso.matricula.estudiante'])
            ->orderByDesc('fecha_registro')
            ->get();
    }

    public function resumen(?int $idCurso = null): array
    {
        $notaMinima = (float) (ConfiguracionSistema::where('clave', 'nota_minima_aprobatoria')->value('valor') ?? 10.5);
        $notas = $this->listar($idCurso);

        $aprobados = $notas->filter(fn ($n) => $n->promedio !== null && (float) $n->promedio >= $notaMinima);
        $desaprobados = $notas->filter(fn ($n) => $n->promedio !== null && (float) $n->promedio < $notaMinima);
        $promedios = $notas->pluck('promedio')->filter(fn ($p) => $p !== null)->map(fn ($p) => (float) $p);

        return [
            'total' => $notas->count(),
            'aprobados' => $aprobados->count(),
            'desaprobados' => $desaprobados->count(),
            'promedio_general' => $promedios->isNotEmpty() ? round($promedios->avg(), 1) : null,
            'nota_minima' => $notaMinima,
        ];
    }
}
