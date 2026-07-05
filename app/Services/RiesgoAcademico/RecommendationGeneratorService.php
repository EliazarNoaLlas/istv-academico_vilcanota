<?php

namespace App\Services\RiesgoAcademico;

class RecommendationGeneratorService
{
    /**
     * @param array<int, string> $factores
     */
    public function generar(array $factores, string $nivel): string
    {
        if ($nivel === 'CRITICO') {
            return 'Derivar a tutoria academica de forma inmediata y notificar al coordinador del programa.';
        }

        if ($nivel === 'ALTO') {
            return 'Programar tutoria de seguimiento y contactar al estudiante en las proximas dos semanas.';
        }

        if ($nivel === 'MEDIO') {
            return 'Monitorear la evolucion del estudiante en la siguiente unidad.';
        }

        if ($factores === []) {
            return 'Sin factores de riesgo detectados; continuar seguimiento regular.';
        }

        return 'Revisar los factores detectados en la proxima evaluacion parcial.';
    }
}
