<?php

namespace App\Services\Director;

use App\Models\Estudiante;
use App\Models\PortafolioDocente;
use App\Services\RiesgoAcademico\RiesgoAcademicoCalculatorService;
use Illuminate\Support\Collection;

class DirectorAnalyticsService
{
    public function __construct(
        private readonly DirectorDashboardService $dashboard,
        private readonly RiesgoAcademicoCalculatorService $riesgo,
    ) {}

    public function rendimientoPorPrograma(): Collection
    {
        return $this->dashboard->rendimientoPorPrograma();
    }

    public function entregaPortafolio(): array
    {
        return $this->dashboard->estadoPortafolio();
    }

    /** % de sílabos aprobados por ciclo, calculado desde portafolio_docente.silabo real. */
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

    /** Riesgo academico promedio vs asistencia promedio, agrupado por programa. */
    public function riesgoVsAsistencia(): Collection
    {
        $resultado = $this->riesgo->calcularParaPeriodo(null);
        $porId = collect($resultado['estudiantes'])->keyBy('id_estudiante');

        return Estudiante::with('programa')
            ->get()
            ->groupBy(fn ($e) => $e->programa?->nombre ?? 'Sin programa')
            ->map(function ($grupo, $programa) use ($porId) {
                $datos = $grupo->map(fn ($e) => $porId->get($e->id_estudiante))->filter();

                $asistencias = $datos->pluck('asistencia_pct')->filter(fn ($a) => $a !== null);
                $riesgos = $datos->pluck('score_riesgo')->filter(fn ($r) => $r !== null);

                return [
                    'programa' => $programa,
                    'asistencia_promedio' => $asistencias->isNotEmpty() ? round($asistencias->avg(), 1) : null,
                    'riesgo_promedio' => $riesgos->isNotEmpty() ? round($riesgos->avg(), 1) : null,
                ];
            })
            ->values();
    }
}
