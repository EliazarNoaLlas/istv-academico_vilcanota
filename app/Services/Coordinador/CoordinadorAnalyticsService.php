<?php

namespace App\Services\Coordinador;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\PortafolioDocente;
use App\Services\RiesgoAcademico\RiesgoAcademicoCalculatorService;
use Illuminate\Support\Collection;

class CoordinadorAnalyticsService
{
    public function __construct(private readonly RiesgoAcademicoCalculatorService $riesgo) {}

    /**
     * Rendimiento por curso (no por programa: el coordinador ya solo ve su
     * propio programa via CoordinadorProgramaDirectoScope en Curso).
     */
    public function rendimientoPorCurso(): Collection
    {
        return Curso::with('notas')
            ->where('estado', 'ACTIVO')
            ->get()
            ->map(function ($curso) {
                $promedios = $curso->notas
                    ->pluck('promedio')
                    ->filter(fn ($promedio) => $promedio !== null)
                    ->map(fn ($promedio) => (float) $promedio);

                return [
                    'curso' => $curso->nombre_curso,
                    'porcentaje' => $promedios->isNotEmpty() ? round($promedios->avg() / 20 * 100, 1) : null,
                ];
            })
            ->values();
    }

    /** Docentes y portafolios ya quedan acotados al programa del coordinador por el scope de Docente/PortafolioDocente. */
    public function entregaPortafolio(): array
    {
        $distribucion = PortafolioDocente::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'total_docentes' => Docente::where('estado_academico', 'ACTIVO')->count(),
            'total_registros' => $distribucion->sum(),
            'distribucion' => [
                'COMPLETO' => (int) $distribucion->get('COMPLETO', 0),
                'EN_REVISION' => (int) $distribucion->get('EN_REVISION', 0),
                'INCOMPLETO' => (int) $distribucion->get('INCOMPLETO', 0),
                'OBSERVADO' => (int) $distribucion->get('OBSERVADO', 0),
            ],
        ];
    }

    /** % de silabos aprobados por ciclo, dentro del programa del coordinador. */
    public function cumplimientoSilaboPorCiclo(): Collection
    {
        return PortafolioDocente::with('curso')
            ->get()
            ->groupBy(fn ($p) => $p->curso?->semestre ?? 'Sin ciclo')
            ->map(function ($grupo, $ciclo) {
                $aprobados = $grupo->where('silabo', 'APROBADO')->count();

                return [
                    'ciclo' => $ciclo,
                    'total' => $grupo->count(),
                    'aprobados' => $aprobados,
                    'porcentaje' => $grupo->count() > 0 ? round($aprobados / $grupo->count() * 100, 1) : 0,
                ];
            })
            ->sortKeys()
            ->values();
    }

    /** Riesgo academico promedio vs asistencia promedio, agrupado por ciclo (el programa ya es uno solo). */
    public function riesgoVsAsistenciaPorCiclo(): Collection
    {
        $resultado = $this->riesgo->calcularParaPeriodo(null);
        $porId = collect($resultado['estudiantes'])->keyBy('id_estudiante');

        return Estudiante::get()
            ->groupBy(fn ($estudiante) => $estudiante->ciclo ?: 'Sin ciclo')
            ->map(function ($grupo, $ciclo) use ($porId) {
                $datos = $grupo->map(fn ($estudiante) => $porId->get($estudiante->id_estudiante))->filter();

                $asistencias = $datos->pluck('asistencia_pct')->filter(fn ($a) => $a !== null);
                $riesgos = $datos->pluck('score_riesgo')->filter(fn ($r) => $r !== null);

                return [
                    'ciclo' => $ciclo,
                    'asistencia_promedio' => $asistencias->isNotEmpty() ? round($asistencias->avg(), 1) : null,
                    'riesgo_promedio' => $riesgos->isNotEmpty() ? round($riesgos->avg(), 1) : null,
                ];
            })
            ->sortKeys()
            ->values();
    }
}
