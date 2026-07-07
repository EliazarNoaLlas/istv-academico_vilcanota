<?php

namespace App\Services\Academic;

use App\Models\AsistenciaDetalle;
use App\Models\AsistenciaSesion;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\PeriodoAcademico;
use Illuminate\Support\Facades\DB;

class AsistenciaService
{
    /**
     * Estudiantes del curso segun su semestre/ciclo: el curso.semestre indica en que ciclo se dicta
     * (p.ej. "III"), y se listan los estudiantes de ese mismo programa y ciclo. No depende de
     * matricula_cursos (todavia no hay una funcionalidad de matricula real en el sistema).
     */
    private function estudiantesDelCurso(Curso $curso)
    {
        return Estudiante::where('id_programa', $curso->id_programa)
            ->where('ciclo', $curso->semestre)
            ->whereNotIn('estado', ['RETIRADO', 'EGRESADO'])
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombres')
            ->get();
    }

    /** Estudiantes del curso (por semestre) con su estado de asistencia de la fecha, resumen del dia y alerta de asistencia historica baja. */
    public function estudiantesPorFecha(Curso $curso, int $idDocente, string $fecha): array
    {
        $sesion = AsistenciaSesion::where('id_curso', $curso->id_curso)
            ->where('id_docente', $idDocente)
            ->where('fecha_sesion', $fecha)
            ->first();

        $detallePorEstudiante = $sesion
            ? AsistenciaDetalle::where('id_sesion', $sesion->id_sesion)->get()->keyBy('id_estudiante')
            : collect();

        $historico = AsistenciaDetalle::query()
            ->join('asistencia_sesiones', 'asistencia_sesiones.id_sesion', '=', 'asistencia_detalle.id_sesion')
            ->where('asistencia_sesiones.id_curso', $curso->id_curso)
            ->selectRaw('asistencia_detalle.id_estudiante, COUNT(*) as total, SUM(asistencia_detalle.estado = "PRESENTE") as presentes')
            ->groupBy('asistencia_detalle.id_estudiante')
            ->get()
            ->keyBy('id_estudiante');

        $estudiantes = $this->estudiantesDelCurso($curso)
            ->map(function (Estudiante $estudiante) use ($detallePorEstudiante, $historico) {
                $detalle = $detallePorEstudiante->get($estudiante->id_estudiante);
                $hist = $historico->get($estudiante->id_estudiante);
                $pctHistorico = $hist && $hist->total > 0 ? round($hist->presentes / $hist->total * 100, 1) : null;

                return [
                    'id_estudiante' => $estudiante->id_estudiante,
                    'codigo_estudiante' => $estudiante->codigo_estudiante,
                    'nombre_completo' => trim($estudiante->apellido_paterno.' '.$estudiante->apellido_materno).', '.$estudiante->nombres,
                    'estado' => $detalle?->estado,
                    'asistencia_historica_pct' => $pctHistorico,
                ];
            })
            ->values();

        $conEstado = $estudiantes->filter(fn ($e) => $e['estado'] !== null);

        return [
            'estudiantes' => $estudiantes,
            'resumen' => [
                'total' => $estudiantes->count(),
                'presentes' => $conEstado->filter(fn ($e) => $e['estado'] === 'PRESENTE')->count(),
                'ausentes' => $conEstado->filter(fn ($e) => $e['estado'] === 'AUSENTE')->count(),
                'tardanzas' => $conEstado->filter(fn ($e) => $e['estado'] === 'TARDANZA')->count(),
                'justificados' => $conEstado->filter(fn ($e) => $e['estado'] === 'JUSTIFICADO')->count(),
            ],
            'alertas_bajo_70' => $estudiantes->filter(fn ($e) => $e['asistencia_historica_pct'] !== null && $e['asistencia_historica_pct'] < 70)->values(),
        ];
    }

    /** Fechas con asistencia ya tomada para el curso (para que la revision no dependa de adivinar una fecha), filtrables por periodo. */
    public function fechasConSesion(Curso $curso, int $idDocente, ?int $idPeriodo = null): array
    {
        return AsistenciaSesion::where('id_curso', $curso->id_curso)
            ->where('id_docente', $idDocente)
            ->when($idPeriodo, fn ($q) => $q->where('id_periodo', $idPeriodo))
            ->orderByDesc('fecha_sesion')
            ->pluck('fecha_sesion')
            ->map(fn ($fecha) => $fecha->format('Y-m-d'))
            ->values()
            ->all();
    }

    /** @param array<int, array{id_estudiante:int, estado:string}> $registros */
    public function guardarAsistencia(Curso $curso, int $idDocente, string $fecha, array $registros): void
    {
        $idsValidos = $this->estudiantesDelCurso($curso)->pluck('id_estudiante');
        $idPeriodo = PeriodoAcademico::where('estado', 'ACTIVO')->value('id_periodo');

        DB::transaction(function () use ($curso, $idDocente, $fecha, $registros, $idPeriodo, $idsValidos) {
            $sesion = AsistenciaSesion::firstOrCreate(
                ['id_curso' => $curso->id_curso, 'id_docente' => $idDocente, 'fecha_sesion' => $fecha],
                ['id_periodo' => $idPeriodo, 'estado' => 'REALIZADA'],
            );

            foreach ($registros as $registro) {
                abort_unless($idsValidos->contains($registro['id_estudiante']), 403, 'Uno de los estudiantes no pertenece al semestre de este curso.');

                AsistenciaDetalle::updateOrCreate(
                    ['id_sesion' => $sesion->id_sesion, 'id_estudiante' => $registro['id_estudiante']],
                    ['estado' => $registro['estado']],
                );
            }
        });
    }
}
