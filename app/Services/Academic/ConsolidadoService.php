<?php

namespace App\Services\Academic;

use App\Models\ConfiguracionSistema;
use App\Models\Curso;
use App\Models\PortafolioDocente;

class ConsolidadoService
{
    /** Consolidado por curso: promedio real, aprobados/desaprobados y estado real de actas. */
    public function porCurso(): array
    {
        $notaMinima = (float) (ConfiguracionSistema::where('clave', 'nota_minima_aprobatoria')->value('valor') ?? 10.5);

        $cursos = Curso::with(['docente.usuario', 'matriculaCursos.notas'])->get();

        return $cursos->map(function ($curso) use ($notaMinima) {
            $promedios = $curso->matriculaCursos
                ->flatMap(fn ($mc) => $mc->notas)
                ->pluck('promedio')
                ->filter(fn ($p) => $p !== null)
                ->map(fn ($p) => (float) $p);

            $aprobados = $promedios->filter(fn ($p) => $p >= $notaMinima)->count();
            $desaprobados = $promedios->filter(fn ($p) => $p < $notaMinima)->count();

            $actas = PortafolioDocente::where('id_curso', $curso->id_curso)
                ->when($curso->id_docente, fn ($q) => $q->where('id_docente', $curso->id_docente))
                ->value('actas');

            return [
                'id_curso' => $curso->id_curso,
                'nombre_curso' => $curso->nombre_curso,
                'semestre' => $curso->semestre,
                'docente' => $curso->docente?->usuario ? trim("{$curso->docente->usuario->nombres} {$curso->docente->usuario->apellidos}") : null,
                'promedio' => $promedios->isNotEmpty() ? round($promedios->avg(), 1) : null,
                'aprobados' => $aprobados,
                'desaprobados' => $desaprobados,
                'estado_actas' => $actas ?? 'PENDIENTE',
            ];
        })->values()->all();
    }

    /** Cursos con aprobacion por debajo del umbral institucional (real, sin datos demo). */
    public function cursosBajaAprobacion(float $umbralPct = 60.0): array
    {
        return array_values(array_filter($this->porCurso(), function ($curso) use ($umbralPct) {
            $total = $curso['aprobados'] + $curso['desaprobados'];
            if ($total === 0) {
                return false;
            }

            return ($curso['aprobados'] / $total * 100) < $umbralPct;
        }));
    }
}
