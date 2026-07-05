<?php

namespace App\Services\Academic;

use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Collection;

class EstudianteService
{
    public function listar(?int $idPrograma = null, ?string $ciclo = null): Collection
    {
        return Estudiante::query()
            ->when($idPrograma, fn ($q) => $q->where('id_programa', $idPrograma))
            ->when($ciclo, fn ($q) => $q->where('ciclo', $ciclo))
            ->with('programa')
            ->orderBy('apellido_paterno')
            ->get();
    }

    /** Estudiantes con promedio real calculado desde notas, para el panel de coordinador. */
    public function listarConPromedio(?int $idPrograma = null, ?string $ciclo = null): Collection
    {
        return $this->listar($idPrograma, $ciclo)
            ->load('matriculas.matriculaCursos.notas')
            ->map(function ($estudiante) {
                $promedios = $estudiante->matriculas
                    ->flatMap(fn ($m) => $m->matriculaCursos)
                    ->flatMap(fn ($mc) => $mc->notas)
                    ->pluck('promedio')
                    ->filter(fn ($p) => $p !== null);

                $estudiante->promedio_general = $promedios->isNotEmpty() ? round($promedios->avg(), 1) : null;

                return $estudiante;
            });
    }
}
