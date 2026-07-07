<?php

namespace App\Services\Academic;

use App\Models\Curso;
use App\Models\DocentePrograma;
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
        $curso = Curso::create($datos);
        $this->sincronizarDocentePrograma($curso);

        return $curso;
    }

    public function actualizar(Curso $curso, array $datos): Curso
    {
        $curso->update($datos);
        $this->sincronizarDocentePrograma($curso);

        return $curso->fresh(['docente.usuario', 'programa']);
    }

    /**
     * Si el curso queda con docente y programa, garantiza que exista el
     * vinculo docente_programa correspondiente (con valores por defecto si
     * es la primera vez). Sin esto, un docente recien asignado a un curso
     * queda invisible en cualquier listado filtrado por programa, incluida
     * la propia cuenta de un coordinador que tambien dicta clases.
     */
    private function sincronizarDocentePrograma(Curso $curso): void
    {
        if (! $curso->id_docente || ! $curso->id_programa) {
            return;
        }

        DocentePrograma::firstOrCreate([
            'id_docente' => $curso->id_docente,
            'id_programa' => $curso->id_programa,
        ]);
    }
}
