<?php

namespace App\Services\Academic;

use App\Models\Curso;
use Illuminate\Database\Eloquent\Collection;

class CursoService
{
    public function listar(
        ?string $semestre = null,
        ?int $idDocente = null,
        ?string $q = null,
        ?string $modulo = null,
        ?int $idPrograma = null,
    ): Collection {
        return Curso::query()
            ->when($semestre, fn ($qq) => $qq->where('semestre', $semestre))
            ->when($idDocente, fn ($qq) => $qq->where('id_docente', $idDocente))
            ->when($modulo, fn ($qq) => $qq->where('modulo', $modulo))
            ->when($idPrograma, fn ($qq) => $qq->where('id_programa', $idPrograma))
            ->when($q, fn ($qq) => $qq->where('nombre_curso', 'like', "%{$q}%"))
            ->with(['docente.usuario', 'programa'])
            ->orderBy('nombre_curso')
            ->get();
    }

    public function crear(array $datos): Curso
    {
        return Curso::create($datos);
    }

    public function actualizar(Curso $curso, array $datos): Curso
    {
        $curso->update($datos);

        return $curso->fresh(['docente.usuario', 'programa']);
    }
}
