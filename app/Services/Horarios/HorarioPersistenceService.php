<?php

namespace App\Services\Horarios;

use App\Models\Aula;
use App\Models\Curso;
use App\Models\Horario;
use App\Models\PeriodoAcademico;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HorarioPersistenceService
{
    /**
     * Reemplaza el horario dentro de una transaccion. A diferencia de la
     * version original (Fase 5/7), esto ya NO borra siempre toda la tabla:
     * si se pasan $filtros (el mismo filtro con el que el usuario esta
     * viendo el horario), solo se reemplaza ese subconjunto — nunca borra
     * bloques fuera del filtro activo. Sin filtros, se asume vista completa
     * y se reemplaza todo (mismo comportamiento historico).
     *
     * @param array<int, array<string, mixed>> $bloques ya validados
     * @param array{id_docente?:int,semestre?:string,id_programa?:int} $filtros
     */
    public function guardar(array $bloques, array $filtros = []): void
    {
        try {
            DB::transaction(function () use ($bloques, $filtros) {
                $this->eliminarPorFiltro($filtros);

                $cursos = Curso::whereIn('id_curso', array_column($bloques, 'id_curso'))->get()->keyBy('id_curso');
                $idPeriodoActivo = PeriodoAcademico::where('estado', 'ACTIVO')->value('id_periodo');

                // Mapa plano (no Eloquent\Collection): Eloquent\Collection::merge()
                // fusiona por clave primaria e ignora las claves de keyBy(), asi
                // que aqui se arma a mano por codigo y por nombre en minusculas.
                $aulasPorTexto = [];
                foreach (Aula::all() as $aula) {
                    $aulasPorTexto[mb_strtolower($aula->codigo)] = $aula;
                    $aulasPorTexto[mb_strtolower($aula->nombre)] = $aula;
                }

                foreach ($bloques as $bloque) {
                    $curso = $cursos->get($bloque['id_curso']);
                    $aulaTexto = $bloque['aula'] ?? null;
                    $aula = $aulaTexto ? ($aulasPorTexto[mb_strtolower($aulaTexto)] ?? null) : null;

                    Horario::create([
                        'id_curso' => $bloque['id_curso'],
                        'id_docente' => $bloque['id_docente'],
                        'id_aula' => $aula?->id_aula,
                        'id_periodo' => $idPeriodoActivo,
                        'id_programa' => $curso?->id_programa,
                        'semestre' => $curso?->semestre,
                        'dia' => $bloque['dia'],
                        'hora_inicio' => $bloque['hora_inicio'],
                        'hora_fin' => $bloque['hora_fin'],
                        'aula' => $aulaTexto,
                        'estado' => $bloque['estado'] ?? 'Confirmado',
                        'fuente' => $bloque['fuente'] ?? 'MANUAL',
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Error al guardar horarios', ['error' => $e->getMessage(), 'filtros' => $filtros]);
            throw $e;
        }
    }

    /** Elimina solo el subconjunto de horarios que coincide con el filtro activo. */
    public function eliminarPorFiltro(array $filtros = []): int
    {
        $query = Horario::query();

        if (! empty($filtros['id_docente'])) {
            $query->where('id_docente', $filtros['id_docente']);
        }

        if (! empty($filtros['semestre']) || ! empty($filtros['id_programa'])) {
            $query->whereHas('curso', function ($qc) use ($filtros) {
                $qc->when(! empty($filtros['semestre']), fn ($qq) => $qq->where('semestre', $filtros['semestre']))
                    ->when(! empty($filtros['id_programa']), fn ($qq) => $qq->where('id_programa', $filtros['id_programa']));
            });
        }

        return $query->delete();
    }
}
