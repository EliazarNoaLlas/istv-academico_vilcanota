<?php

namespace App\Services\Academic;

use App\Models\AsistenciaDetalle;
use App\Models\AsistenciaSesion;
use App\Models\MatriculaCurso;
use App\Models\PeriodoAcademico;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class AsistenciaService
{
    public function sesionesDeCurso(int $idCurso, int $idDocente): Collection
    {
        return AsistenciaSesion::where('id_curso', $idCurso)
            ->where('id_docente', $idDocente)
            ->orderByDesc('fecha_sesion')
            ->get();
    }

    public function crearSesion(int $idCurso, int $idDocente, string $fecha, ?string $tema = null): AsistenciaSesion
    {
        $idPeriodo = PeriodoAcademico::where('estado', 'ACTIVO')->value('id_periodo');

        return AsistenciaSesion::firstOrCreate(
            ['id_curso' => $idCurso, 'id_docente' => $idDocente, 'fecha_sesion' => $fecha],
            ['id_periodo' => $idPeriodo, 'tema' => $tema, 'estado' => 'REALIZADA'],
        );
    }

    /** Roster del curso con el estado de asistencia de la sesion indicada (si ya se tomo) por cada estudiante matriculado. */
    public function estudiantesDeSesion(int $idCurso, int $idSesion): SupportCollection
    {
        $detalles = AsistenciaDetalle::where('id_sesion', $idSesion)->get()->keyBy('id_estudiante');

        return MatriculaCurso::where('id_curso', $idCurso)
            ->with('matricula.estudiante')
            ->get()
            ->map(function ($mc) use ($detalles) {
                $estudiante = $mc->matricula->estudiante;
                $detalle = $detalles->get($estudiante->id_estudiante);

                return [
                    'id_estudiante' => $estudiante->id_estudiante,
                    'estudiante' => $estudiante,
                    'estado' => $detalle?->estado ?? 'PRESENTE',
                ];
            })
            ->values();
    }

    /** @param array<int, array{id_estudiante:int, estado:string}> $registros */
    public function guardarAsistencia(int $idSesion, array $registros): void
    {
        foreach ($registros as $registro) {
            AsistenciaDetalle::updateOrCreate(
                ['id_sesion' => $idSesion, 'id_estudiante' => $registro['id_estudiante']],
                ['estado' => $registro['estado']],
            );
        }
    }
}
