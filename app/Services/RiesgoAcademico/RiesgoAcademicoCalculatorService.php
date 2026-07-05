<?php

namespace App\Services\RiesgoAcademico;

use App\Models\ConfiguracionSistema;
use App\Models\Estudiante;
use App\Models\PeriodoAcademico;
use Illuminate\Support\Facades\Log;

class RiesgoAcademicoCalculatorService
{
    public function __construct(
        private RiskFactorAnalyzerService $factores,
        private RecommendationGeneratorService $recomendaciones,
    ) {}

    /**
     * Calcula el riesgo academico por reglas (sin IA/LLM) para todos los
     * estudiantes matriculados en el periodo indicado.
     */
    public function calcularParaPeriodo(?string $codigoPeriodo): array
    {
        try {
            $periodo = $codigoPeriodo
                ? PeriodoAcademico::where('codigo', $codigoPeriodo)->first()
                : PeriodoAcademico::where('estado', 'ACTIVO')->first();

            if (! $periodo) {
                return ['periodo' => $codigoPeriodo, 'estudiantes' => [], 'resumen' => $this->resumenVacio()];
            }

            $notaMinima = (float) (ConfiguracionSistema::where('clave', 'nota_minima_aprobatoria')->value('valor') ?? 10.5);
            $umbralAsistencia = (float) (ConfiguracionSistema::where('clave', 'porcentaje_riesgo_asistencia')->value('valor') ?? 70);

            $estudiantes = Estudiante::with([
                'matriculas' => fn ($q) => $q->where('id_periodo', $periodo->id_periodo),
                'matriculas.matriculaCursos.notas',
                'asistenciaDetalle',
            ])->get();

            $resultado = [];

            foreach ($estudiantes as $estudiante) {
                $matricula = $estudiante->matriculas->first();
                if (! $matricula) {
                    continue;
                }

                $promedios = $matricula->matriculaCursos
                    ->flatMap(fn ($mc) => $mc->notas)
                    ->pluck('promedio')
                    ->filter(fn ($p) => $p !== null)
                    ->map(fn ($p) => (float) $p);

                $promedioGeneral = $promedios->isNotEmpty() ? round($promedios->avg(), 2) : null;

                $totalAsistencia = $estudiante->asistenciaDetalle->count();
                $asistenciaPct = $totalAsistencia > 0
                    ? round($estudiante->asistenciaDetalle->whereIn('estado', ['PRESENTE', 'TARDANZA'])->count() / $totalAsistencia * 100, 1)
                    : null;

                $score = $this->calcularScore($promedioGeneral, $asistenciaPct, $notaMinima, $umbralAsistencia);
                $nivel = $this->nivelDesdeScore($score);
                $factores = $this->factores->analizar($promedioGeneral, $asistenciaPct, $notaMinima, $umbralAsistencia);

                $resultado[] = [
                    'id_estudiante' => $estudiante->id_estudiante,
                    'nombres' => trim("{$estudiante->nombres} {$estudiante->apellido_paterno} {$estudiante->apellido_materno}"),
                    'promedio_general' => $promedioGeneral,
                    'asistencia_pct' => $asistenciaPct,
                    'score_riesgo' => $score,
                    'nivel' => $nivel,
                    'factores' => $factores,
                    'recomendacion' => $this->recomendaciones->generar($factores, $nivel),
                ];
            }

            usort($resultado, fn ($a, $b) => $b['score_riesgo'] <=> $a['score_riesgo']);

            return [
                'periodo' => $periodo->codigo,
                'estudiantes' => $resultado,
                'resumen' => $this->resumen($resultado),
            ];
        } catch (\Throwable $e) {
            Log::error('Error al calcular riesgo academico', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function calcularScore(?float $promedio, ?float $asistencia, float $notaMinima, float $umbralAsistencia): float
    {
        $riesgoNota = $promedio !== null ? max(0, ($notaMinima - $promedio) / $notaMinima * 100) : 0;
        $riesgoAsistencia = $asistencia !== null ? max(0, ($umbralAsistencia - $asistencia) / $umbralAsistencia * 100) : 0;

        return round(min(100, ($riesgoNota * 0.6) + ($riesgoAsistencia * 0.4)), 2);
    }

    private function nivelDesdeScore(float $score): string
    {
        return match (true) {
            $score >= 70 => 'CRITICO',
            $score >= 50 => 'ALTO',
            $score >= 25 => 'MEDIO',
            default => 'BAJO',
        };
    }

    private function resumen(array $estudiantes): array
    {
        $porNivel = collect($estudiantes)->countBy('nivel');

        return [
            'total_evaluados' => count($estudiantes),
            'criticos' => $porNivel->get('CRITICO', 0),
            'altos' => $porNivel->get('ALTO', 0),
            'medios' => $porNivel->get('MEDIO', 0),
            'bajos' => $porNivel->get('BAJO', 0),
        ];
    }

    private function resumenVacio(): array
    {
        return ['total_evaluados' => 0, 'criticos' => 0, 'altos' => 0, 'medios' => 0, 'bajos' => 0];
    }
}
