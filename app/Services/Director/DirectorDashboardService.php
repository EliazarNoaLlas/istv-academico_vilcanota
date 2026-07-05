<?php

namespace App\Services\Director;

use App\Models\AlertaAcademica;
use App\Models\AuditoriaSistema;
use App\Models\Docente;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\PortafolioDocente;
use App\Models\ProgramaEstudio;
use Illuminate\Support\Collection;

class DirectorDashboardService
{
    public function kpis(): array
    {
        return [
            'total_estudiantes' => Estudiante::whereNotIn('estado', ['RETIRADO', 'EGRESADO'])->count(),
            'docentes_activos' => Docente::where('estado_academico', 'ACTIVO')->count(),
            'cursos_activos' => Curso::where('estado', 'ACTIVO')->count(),
            'alertas_abiertas' => AlertaAcademica::where('estado', 'ABIERTA')->count(),
        ];
    }

    /** Rendimiento por programa: promedio de notas de sus estudiantes, escalado a porcentaje (nota/20). */
    public function rendimientoPorPrograma(): Collection
    {
        return ProgramaEstudio::with([
            'estudiantes.matriculas.matriculaCursos.notas',
        ])->get()->map(function ($programa) {
            $promedios = $programa->estudiantes
                ->flatMap(fn ($estudiante) => $estudiante->matriculas)
                ->flatMap(fn ($matricula) => $matricula->matriculaCursos)
                ->flatMap(fn ($matriculaCurso) => $matriculaCurso->notas)
                ->pluck('promedio')
                ->filter(fn ($promedio) => $promedio !== null)
                ->map(fn ($promedio) => (float) $promedio);

            return [
                'programa' => $programa->nombre,
                'porcentaje' => $promedios->isNotEmpty() ? round($promedios->avg() / 20 * 100, 1) : null,
                'estudiantes' => $programa->estudiantes->count(),
            ];
        })->values();
    }

    public function estadoPortafolio(): array
    {
        $distribucion = PortafolioDocente::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $total = $distribucion->sum();

        return [
            'total_docentes' => Docente::where('estado_academico', 'ACTIVO')->count(),
            'total_registros' => $total,
            'distribucion' => [
                'COMPLETO' => (int) $distribucion->get('COMPLETO', 0),
                'EN_REVISION' => (int) $distribucion->get('EN_REVISION', 0),
                'INCOMPLETO' => (int) $distribucion->get('INCOMPLETO', 0),
                'OBSERVADO' => (int) $distribucion->get('OBSERVADO', 0),
            ],
        ];
    }

    public function alertasRecientes(int $limite = 5): Collection
    {
        return AlertaAcademica::where('estado', 'ABIERTA')
            ->with(['estudiante', 'docente.usuario', 'curso'])
            ->orderByDesc('fecha_creacion')
            ->limit($limite)
            ->get();
    }

    /** Actividad reciente del sistema. Vacio hasta que algun flujo escriba en auditoria_sistema. */
    public function actividadReciente(int $limite = 5): Collection
    {
        return AuditoriaSistema::with('usuario')
            ->orderByDesc('fecha_accion')
            ->limit($limite)
            ->get();
    }
}
