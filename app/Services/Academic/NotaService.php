<?php

namespace App\Services\Academic;

use App\Models\ConfiguracionSistema;
use App\Models\Nota;
use Illuminate\Database\Eloquent\Collection;

class NotaService
{
    public function listar(?int $idCurso = null, ?string $unidad = null): Collection
    {
        return Nota::query()
            ->when($idCurso, fn ($q) => $q->whereHas('matriculaCurso', fn ($mc) => $mc->where('id_curso', $idCurso)))
            ->when($unidad, fn ($q) => $q->where('unidad', $unidad))
            ->with(['matriculaCurso.curso', 'matriculaCurso.matricula.estudiante'])
            ->orderByDesc('fecha_registro')
            ->get();
    }

    public function resumen(?int $idCurso = null): array
    {
        $notaMinima = (float) (ConfiguracionSistema::where('clave', 'nota_minima_aprobatoria')->value('valor') ?? 10.5);
        $notas = $this->listar($idCurso);

        $aprobados = $notas->filter(fn ($n) => $n->promedio !== null && (float) $n->promedio >= $notaMinima);
        $desaprobados = $notas->filter(fn ($n) => $n->promedio !== null && (float) $n->promedio < $notaMinima);
        $promedios = $notas->pluck('promedio')->filter(fn ($p) => $p !== null)->map(fn ($p) => (float) $p);

        return [
            'total' => $notas->count(),
            'aprobados' => $aprobados->count(),
            'desaprobados' => $desaprobados->count(),
            'promedio_general' => $promedios->isNotEmpty() ? round($promedios->avg(), 1) : null,
            'nota_minima' => $notaMinima,
        ];
    }
}
