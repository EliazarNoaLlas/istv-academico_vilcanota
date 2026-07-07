<?php

namespace App\Services\Academic;

use App\Models\AsistenciaDetalle;
use App\Models\AsistenciaSesion;
use App\Models\MatriculaCurso;
use App\Models\PeriodoAcademico;
use Carbon\Carbon;
use Illuminate\Support\Collection as SupportCollection;

class AsistenciaService
{
    /** Matriz estudiantes x dias del mes indicado, con el estado de asistencia (o null si ese dia no se tomo sesion). */
    public function matrizDeCurso(int $idCurso, int $idDocente, string $mes): array
    {
        $inicio = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
        $fin = $inicio->copy()->endOfMonth();

        $dias = [];
        for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {
            $dias[] = $fecha->format('Y-m-d');
        }

        $sesiones = AsistenciaSesion::where('id_curso', $idCurso)
            ->where('id_docente', $idDocente)
            ->whereBetween('fecha_sesion', [$inicio->format('Y-m-d'), $fin->format('Y-m-d')])
            ->with('detalle')
            ->get()
            ->keyBy(fn ($sesion) => $sesion->fecha_sesion->format('Y-m-d'));

        $estudiantes = MatriculaCurso::where('id_curso', $idCurso)
            ->with('matricula.estudiante')
            ->get()
            ->map(function ($mc) use ($dias, $sesiones) {
                $estudiante = $mc->matricula->estudiante;

                $asistencias = [];
                foreach ($dias as $dia) {
                    $sesion = $sesiones->get($dia);
                    $asistencias[$dia] = $sesion
                        ? ($sesion->detalle->firstWhere('id_estudiante', $estudiante->id_estudiante)?->estado ?? 'PRESENTE')
                        : null;
                }

                return [
                    'id_estudiante' => $estudiante->id_estudiante,
                    'estudiante' => $estudiante,
                    'asistencias' => $asistencias,
                ];
            })
            ->values();

        return ['dias' => $dias, 'estudiantes' => $estudiantes];
    }

    /** @param array<string, array<int, array{id_estudiante:int, estado:string}>> $cambiosPorFecha */
    public function guardarCambiosMatriz(int $idCurso, int $idDocente, array $cambiosPorFecha): void
    {
        $idPeriodo = PeriodoAcademico::where('estado', 'ACTIVO')->value('id_periodo');

        foreach ($cambiosPorFecha as $fecha => $registros) {
            $sesion = AsistenciaSesion::firstOrCreate(
                ['id_curso' => $idCurso, 'id_docente' => $idDocente, 'fecha_sesion' => $fecha],
                ['id_periodo' => $idPeriodo, 'estado' => 'REALIZADA'],
            );

            foreach ($registros as $registro) {
                AsistenciaDetalle::updateOrCreate(
                    ['id_sesion' => $sesion->id_sesion, 'id_estudiante' => $registro['id_estudiante']],
                    ['estado' => $registro['estado']],
                );
            }
        }
    }
}
