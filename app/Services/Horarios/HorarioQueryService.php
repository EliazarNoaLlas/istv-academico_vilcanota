<?php

namespace App\Services\Horarios;

use App\Models\Horario;
use Illuminate\Database\Eloquent\Collection;

class HorarioQueryService
{
    /**
     * @param int|null $idPrograma filtra via la relacion horarios->curso->id_programa
     *                             (no existe id_programa directo en horarios, ver reporte de esquema)
     */
    public function listar(
        ?int $idDocente = null,
        ?int $idCurso = null,
        ?string $semestre = null,
        ?int $idPrograma = null,
    ): Collection {
        return Horario::query()
            ->when($idDocente, fn ($q) => $q->where('id_docente', $idDocente))
            ->when($idCurso, fn ($q) => $q->where('id_curso', $idCurso))
            ->when($semestre || $idPrograma, function ($q) use ($semestre, $idPrograma) {
                $q->whereHas('curso', function ($qc) use ($semestre, $idPrograma) {
                    $qc->when($semestre, fn ($qq) => $qq->where('semestre', $semestre))
                        ->when($idPrograma, fn ($qq) => $qq->where('id_programa', $idPrograma));
                });
            })
            ->with(['curso', 'docente.usuario'])
            ->orderBy('dia')
            ->orderBy('hora_inicio')
            ->get();
    }
}
