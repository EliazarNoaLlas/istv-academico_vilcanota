<?php

namespace App\Services\Horarios;

class HorarioColorService
{
    /**
     * Paleta pastel institucional (la misma que ya usaba el legacy). Se
     * calcula en el momento a partir del id_curso — no se guarda un
     * color_hex en base de datos porque el color es 100% derivable y
     * guardarlo solo duplicaria informacion sin necesidad real.
     */
    private const PALETA = [
        ['border' => '#439dc4', 'bg' => '#7ec8e3'],
        ['border' => '#c67a7a', 'bg' => '#e8a0a0'],
        ['border' => '#5fafa0', 'bg' => '#7fd1c0'],
        ['border' => '#d4a84a', 'bg' => '#f0c96e'],
        ['border' => '#b87a9a', 'bg' => '#d4a0b5'],
        ['border' => '#8f8f8f', 'bg' => '#b5b5b5'],
        ['border' => '#7a8fc6', 'bg' => '#a0b0e8'],
        ['border' => '#8fb84a', 'bg' => '#b5d46e'],
    ];

    public function paraCurso(int $idCurso): array
    {
        return self::PALETA[$idCurso % count(self::PALETA)];
    }
}
