<?php

namespace App\Services\Academic;

use App\Models\Curso;
use App\Models\PortafolioDocente;

class ValidacionService
{
    /**
     * Pendientes de control administrativo, todos calculados desde datos
     * reales (nunca contadores inventados como en el legacy).
     */
    public function pendientes(): array
    {
        $sinDocente = Curso::whereNull('id_docente')->count();
        $sinPrograma = Curso::whereNull('id_programa')->count();
        $sinHorario = Curso::whereDoesntHave('horarios')->count();
        $portafolioIncompleto = PortafolioDocente::where('estado', '!=', 'COMPLETO')->count();
        $actasPendientes = PortafolioDocente::where('actas', '!=', 'APROBADO')->count();

        return [
            'sin_docente' => $sinDocente,
            'sin_horario' => $sinHorario,
            'sin_programa' => $sinPrograma,
            'portafolio_incompleto' => $portafolioIncompleto,
            'actas_pendientes' => $actasPendientes,
        ];
    }
}
