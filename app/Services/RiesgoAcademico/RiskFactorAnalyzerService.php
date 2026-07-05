<?php

namespace App\Services\RiesgoAcademico;

class RiskFactorAnalyzerService
{
    /**
     * @return array<int, string>
     */
    public function analizar(?float $promedio, ?float $asistenciaPct, float $notaMinima, float $umbralAsistencia): array
    {
        $factores = [];

        if ($promedio !== null && $promedio < $notaMinima) {
            $factores[] = sprintf('Promedio general (%.2f) por debajo de la nota minima aprobatoria (%.1f).', $promedio, $notaMinima);
        }

        if ($asistenciaPct !== null && $asistenciaPct < $umbralAsistencia) {
            $factores[] = sprintf('Asistencia (%.1f%%) por debajo del umbral institucional (%.0f%%).', $asistenciaPct, $umbralAsistencia);
        }

        if ($promedio === null) {
            $factores[] = 'Sin notas registradas todavia en el periodo evaluado.';
        }

        if ($asistenciaPct === null) {
            $factores[] = 'Sin registros de asistencia todavia en el periodo evaluado.';
        }

        return $factores;
    }
}
