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

    public function crear(array $datos): Estudiante
    {
        return Estudiante::create([
            ...$datos,
            'codigo_estudiante' => $this->generarCodigoEstudiante(),
            'estado' => 'REGULAR',
        ]);
    }

    /** Genera codigo_estudiante con patron EST### a partir del ultimo correlativo usado (incluye eliminados, para no reutilizar codigos). */
    private function generarCodigoEstudiante(): string
    {
        $ultimoNumero = Estudiante::withTrashed()
            ->where('codigo_estudiante', 'like', 'EST%')
            ->get()
            ->map(fn ($e) => (int) preg_replace('/\D/', '', $e->codigo_estudiante))
            ->max() ?? 0;

        return 'EST'.str_pad((string) ($ultimoNumero + 1), 3, '0', STR_PAD_LEFT);
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
