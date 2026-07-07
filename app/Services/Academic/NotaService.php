<?php

namespace App\Services\Academic;

use App\Models\ConfiguracionSistema;
use App\Models\MatriculaCurso;
use App\Models\Nota;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class NotaService
{
    /** Roster del curso con la nota de la unidad indicada (si existe) por cada estudiante matriculado. */
    public function estudiantesDeCurso(int $idCurso, string $unidad = 'I'): SupportCollection
    {
        return MatriculaCurso::where('id_curso', $idCurso)
            ->with(['matricula.estudiante', 'notas' => fn ($q) => $q->where('unidad', $unidad)])
            ->get()
            ->map(function ($mc) use ($unidad) {
                $nota = $mc->notas->first();

                return [
                    'id_matricula_curso' => $mc->id_matricula_curso,
                    'estudiante' => $mc->matricula->estudiante,
                    'unidad' => $unidad,
                    'id_nota' => $nota?->id_nota,
                    'practica' => $nota?->practica,
                    'teoria' => $nota?->teoria,
                    'examen' => $nota?->examen,
                    'promedio' => $nota?->promedio,
                ];
            })
            ->values();
    }

    public function guardarNota(int $idMatriculaCurso, string $unidad, ?float $practica, ?float $teoria, ?float $examen): Nota
    {
        return Nota::updateOrCreate(
            ['id_matricula_curso' => $idMatriculaCurso, 'unidad' => $unidad],
            ['practica' => $practica, 'teoria' => $teoria, 'examen' => $examen],
        );
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
