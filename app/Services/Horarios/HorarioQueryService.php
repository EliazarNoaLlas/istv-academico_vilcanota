<?php

namespace App\Services\Horarios;

use App\Models\Curso;
use App\Models\Horario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

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

    /**
     * Resumen por semestre (I-VI) de un programa: cursos activos, bloques
     * requeridos y bloques ya guardados en horarios.
     *
     * bloques_requeridos = SUM(cursos.horas_ud) excluyendo ESRT: la practica
     * en situacion real de trabajo no ocupa la tabla semanal regular, solo
     * se reporta aparte (ver excluirEsrt()).
     */
    public function resumenPorSemestre(int $idPrograma, ?int $idPeriodo = null): BaseCollection
    {
        $requeridos = $this->excluirEsrt(
            Curso::query()
                ->where('id_programa', $idPrograma)
                ->where('estado', 'ACTIVO')
                ->whereNull('deleted_at')
        )
            ->selectRaw('semestre, COUNT(*) as cursos, SUM(horas_ud) as bloques_requeridos')
            ->groupBy('semestre')
            ->get()
            ->keyBy('semestre');

        $sinDocente = Curso::query()
            ->where('id_programa', $idPrograma)
            ->where('estado', 'ACTIVO')
            ->whereNull('deleted_at')
            ->whereNull('id_docente')
            ->selectRaw('semestre, COUNT(*) as n')
            ->groupBy('semestre')
            ->pluck('n', 'semestre');

        $generados = Horario::query()
            ->where('id_programa', $idPrograma)
            ->when($idPeriodo, fn ($q) => $q->where('id_periodo', $idPeriodo))
            ->selectRaw('semestre, COUNT(*) as bloques_generados')
            ->groupBy('semestre')
            ->pluck('bloques_generados', 'semestre');

        return collect(['I', 'II', 'III', 'IV', 'V', 'VI'])->map(function ($semestre) use ($requeridos, $sinDocente, $generados) {
            $req = $requeridos->get($semestre);
            $cursos = (int) ($req->cursos ?? 0);
            $bloquesGenerados = (int) ($generados->get($semestre) ?? 0);

            return [
                'semestre' => $semestre,
                'cursos' => $cursos,
                'cursos_sin_docente' => (int) ($sinDocente->get($semestre) ?? 0),
                'bloques_requeridos' => (int) ($req->bloques_requeridos ?? 0),
                'bloques_generados' => $bloquesGenerados,
                'listo_para_generar' => $cursos > 0
                    && (int) ($sinDocente->get($semestre) ?? 0) === 0
                    && $bloquesGenerados === 0,
            ];
        });
    }

    /**
     * Cursos activos de un semestre listos para distribuir en la tabla
     * semanal (excluye ESRT, igual que resumenPorSemestre). Ordenados por
     * orden_malla para mantener el orden del itinerario oficial.
     *
     * @return BaseCollection<int, array{id_curso:int,nombre_curso:string,semestre:string,id_docente:?int,bloques_requeridos:int,horas_practica:int}>
     */
    public function cursosSemestre(int $idPrograma, string $semestre): BaseCollection
    {
        $query = Curso::query()
            ->where('id_programa', $idPrograma)
            ->where('semestre', $semestre)
            ->where('estado', 'ACTIVO')
            ->whereNull('deleted_at');

        return $this->excluirEsrt($query)
            ->orderByRaw('COALESCE(orden_malla, id_curso)')
            ->get(['id_curso', 'nombre_curso', 'semestre', 'id_docente', 'horas_ud', 'horas_teoria', 'horas_practica'])
            ->map(fn (Curso $c) => [
                'id_curso' => $c->id_curso,
                'nombre_curso' => $c->nombre_curso,
                'semestre' => $c->semestre,
                'id_docente' => $c->id_docente,
                'bloques_requeridos' => (int) $c->horas_ud,
                'horas_teoria' => (int) $c->horas_teoria,
                'horas_practica' => (int) $c->horas_practica,
            ]);
    }

    /**
     * Excluye del calculo de bloques semanales los cursos ESRT: por
     * tipo_formacion = 'ESRT', por ser TRANSVERSAL con nombre "Experiencias
     * formativas...", o por traer "(ESRT)" en el nombre.
     */
    private function excluirEsrt(Builder $query): Builder
    {
        return $query
            ->where('tipo_formacion', '!=', 'ESRT')
            ->where(function ($q) {
                $q->where('tipo_curso', '!=', 'TRANSVERSAL')
                    ->orWhere('nombre_curso', 'not like', '%Experiencias formativas%');
            })
            ->where('nombre_curso', 'not like', '%(ESRT)%');
    }
}
