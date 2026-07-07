<?php

namespace App\Services\Docente;

use App\Models\AsistenciaDetalle;
use App\Models\AsistenciaSesion;
use App\Models\ConfiguracionSistema;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\Horario;
use App\Models\MatriculaCurso;
use App\Models\Nota;
use App\Models\PeriodoAcademico;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DocentePortalService
{
    /** Resuelve el docente autenticado y valida que su perfil academico este activo. */
    public function getDocenteActual(User $usuario): Docente
    {
        $docente = $usuario->docente;

        abort_if(! $docente || $docente->estado_academico !== 'ACTIVO', 403, 'No tienes un perfil docente activo.');

        return $docente;
    }

    public function getPeriodoActivo(): ?PeriodoAcademico
    {
        return PeriodoAcademico::where('estado', 'ACTIVO')->first();
    }

    /** Cursos asignados directamente al docente (cursos.id_docente), con metricas reales por curso. */
    public function getCursosAsignados(Docente $docente, ?PeriodoAcademico $periodo = null): Collection
    {
        return Curso::where('id_docente', $docente->id_docente)
            ->where('estado', 'ACTIVO')
            ->withCount(['matriculaCursos as estudiantes_count' => fn ($q) => $q->where('matricula_cursos.estado', 'EN_CURSO')])
            ->withCount('sesionesAprendizaje')
            ->withAvg('notas', 'promedio')
            ->withCount('asistenciaDetalles as asistencia_total')
            ->withCount(['asistenciaDetalles as asistencia_presentes' => fn ($q) => $q->where('asistencia_detalle.estado', 'PRESENTE')])
            ->with([
                'programa',
                'horarios.aulaAsignada',
                'portafolios' => function ($q) use ($periodo) {
                    $q->when($periodo, fn ($qp) => $qp->where('id_periodo', $periodo->id_periodo));
                },
            ])
            ->orderBy('nombre_curso')
            ->get()
            ->map(function (Curso $curso) {
                $curso->asistencia_promedio = $curso->asistencia_total > 0
                    ? round($curso->asistencia_presentes / $curso->asistencia_total * 100, 1)
                    : null;
                $curso->portafolio_estado = $curso->portafolios->first()->estado ?? 'SIN_INICIAR';

                $primerHorario = $curso->horarios->first();
                $curso->aula_principal = $primerHorario ? ($primerHorario->aulaAsignada->nombre ?? $primerHorario->aula) : null;

                return $curso;
            });
    }

    /** Horario semanal real del docente (horarios.id_docente), sin inventar clases. */
    public function getHorarioSemanal(Docente $docente, ?PeriodoAcademico $periodo = null): Collection
    {
        return Horario::where('id_docente', $docente->id_docente)
            ->when($periodo, fn ($q) => $q->where('id_periodo', $periodo->id_periodo))
            ->with(['curso', 'aulaAsignada', 'periodo'])
            ->orderByRaw("FIELD(dia, 'Lunes','Martes','Miercoles','Miércoles','Jueves','Viernes','Sabado','Sábado')")
            ->orderBy('hora_inicio')
            ->get();
    }

    /** Quita tildes para poder comparar 'Miercoles' (BD) con 'Miércoles' (Carbon en español). */
    private function normalizarDia(string $dia): string
    {
        return strtr(mb_strtolower(trim($dia)), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);
    }

    public function getDashboardData(Docente $docente): array
    {
        $periodo = $this->getPeriodoActivo();
        $cursos = $this->getCursosAsignados($docente, $periodo);
        $horarioSemanal = $this->getHorarioSemanal($docente, $periodo);

        $diasEs = ['Sunday' => 'Domingo', 'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado'];
        $hoy = $this->normalizarDia($diasEs[now()->format('l')]);

        $clasesHoy = $horarioSemanal->filter(fn ($h) => $h->dia && $this->normalizarDia($h->dia) === $hoy);

        $promedios = $cursos->pluck('notas_avg_promedio')->filter(fn ($p) => $p !== null)->map(fn ($p) => (float) $p);
        $asistenciasConDatos = $cursos->filter(fn ($c) => $c->asistencia_promedio !== null)->pluck('asistencia_promedio');

        $horasSemanales = Horario::where('id_docente', $docente->id_docente)
            ->when($periodo, fn ($q) => $q->where('id_periodo', $periodo->id_periodo))
            ->get()
            ->sum(function (Horario $h) {
                if (! $h->hora_inicio || ! $h->hora_fin) {
                    return 0;
                }

                return $h->hora_inicio->diffInMinutes($h->hora_fin) / 60;
            });

        $portafolioDistribucion = $cursos->groupBy('portafolio_estado')->map->count();

        return [
            'docente' => [
                'nombre' => trim($docente->usuario->nombres.' '.$docente->usuario->apellidos),
                'codigo_docente' => $docente->codigo_docente,
                'especialidad' => $docente->especialidad,
                'tipo_docente' => $docente->tipo_docente,
            ],
            'periodo_activo' => $periodo ? ['codigo' => $periodo->codigo, 'nombre' => $periodo->nombre] : null,
            'kpis' => [
                'cursos_asignados' => $cursos->count(),
                'total_estudiantes' => (int) $cursos->sum('estudiantes_count'),
                'horas_semanales' => round($horasSemanales, 1),
                'promedio_general' => $promedios->isNotEmpty() ? round($promedios->avg(), 1) : null,
                'asistencia_promedio' => $asistenciasConDatos->isNotEmpty() ? round($asistenciasConDatos->avg(), 1) : null,
            ],
            'rendimiento_por_curso' => $cursos->map(fn ($c) => [
                'curso' => $c->nombre_curso,
                'promedio' => $c->notas_avg_promedio !== null ? round((float) $c->notas_avg_promedio, 1) : null,
            ])->values(),
            'asistencia_por_curso' => $cursos->map(fn ($c) => [
                'curso' => $c->nombre_curso,
                'asistencia_promedio' => $c->asistencia_promedio,
            ])->values(),
            'portafolio' => [
                'total' => $cursos->count(),
                'distribucion' => [
                    'COMPLETO' => (int) ($portafolioDistribucion['COMPLETO'] ?? 0),
                    'EN_REVISION' => (int) ($portafolioDistribucion['EN_REVISION'] ?? 0),
                    'INCOMPLETO' => (int) ($portafolioDistribucion['INCOMPLETO'] ?? 0),
                    'OBSERVADO' => (int) ($portafolioDistribucion['OBSERVADO'] ?? 0),
                    'SIN_INICIAR' => (int) ($portafolioDistribucion['SIN_INICIAR'] ?? 0),
                ],
            ],
            'ultimas_sesiones' => $docente->sesionesAprendizaje()
                ->with('curso')
                ->orderByDesc('fecha_subida')
                ->limit(5)
                ->get()
                ->map(fn ($s) => [
                    'curso' => $s->curso?->nombre_curso,
                    'titulo' => $s->titulo,
                    'numero_sesion' => $s->numero_sesion,
                    'estado' => $s->estado,
                    'fecha_subida' => $s->fecha_subida?->format('d/m/Y'),
                ]),
            'clases_hoy' => $clasesHoy->map(fn ($h) => [
                'curso' => $h->curso?->nombre_curso,
                'dia' => $h->dia,
                'hora_inicio' => $h->hora_inicio?->format('H:i'),
                'hora_fin' => $h->hora_fin?->format('H:i'),
                'aula' => $h->aulaAsignada?->nombre ?? $h->aula,
            ])->values(),
            'alertas' => [
                'cursos_sin_notas' => $cursos->filter(fn ($c) => $c->notas_avg_promedio === null)->count(),
                'cursos_sin_asistencia' => $cursos->filter(fn ($c) => $c->asistencia_total === 0)->count(),
                'portafolio_incompleto' => $cursos->filter(fn ($c) => $c->portafolio_estado !== 'COMPLETO')->count(),
            ],
        ];
    }

    /** Verifica que el curso pertenezca al docente autenticado antes de dejarlo ver/editar nada de el. */
    public function verificarCursoPerteneceAlDocente(Docente $docente, int $idCurso): Curso
    {
        $curso = Curso::where('id_curso', $idCurso)->where('id_docente', $docente->id_docente)->first();

        abort_if(! $curso, 403, 'Este curso no pertenece a tu carga académica.');

        return $curso;
    }

    /** Estudiantes matriculados (EN_CURSO) en el curso, con su nota real de la unidad indicada. */
    public function getEstudiantesPorCurso(Curso $curso, string $unidad): array
    {
        $notaMinima = (float) (ConfiguracionSistema::where('clave', 'nota_minima_aprobatoria')->value('valor') ?? 10.5);

        $estudiantes = MatriculaCurso::where('id_curso', $curso->id_curso)
            ->where('estado', 'EN_CURSO')
            ->with(['matricula.estudiante', 'notas' => fn ($q) => $q->where('unidad', $unidad)])
            ->get()
            ->map(function (MatriculaCurso $mc) {
                $estudiante = $mc->matricula->estudiante;
                $nota = $mc->notas->first();

                return [
                    'id_matricula_curso' => $mc->id_matricula_curso,
                    'id_estudiante' => $estudiante->id_estudiante,
                    'codigo_estudiante' => $estudiante->codigo_estudiante,
                    'dni' => $estudiante->dni,
                    'nombre_completo' => trim($estudiante->apellido_paterno.' '.$estudiante->apellido_materno).', '.$estudiante->nombres,
                    'nota' => $nota ? [
                        'id_nota' => $nota->id_nota,
                        'practica' => $nota->practica,
                        'teoria' => $nota->teoria,
                        'examen' => $nota->examen,
                        'promedio' => $nota->promedio,
                        'estado' => $nota->estado,
                    ] : null,
                ];
            })
            ->sortBy('nombre_completo')
            ->values();

        $notasExistentes = $estudiantes->pluck('nota')->filter();
        $actaCerrada = $notasExistentes->contains(fn ($n) => $n['estado'] === 'CERRADO');
        $promedios = $notasExistentes->pluck('promedio')->filter(fn ($p) => $p !== null)->map(fn ($p) => (float) $p);

        return [
            'estudiantes' => $estudiantes,
            'acta_cerrada' => $actaCerrada,
            'resumen' => [
                'total' => $estudiantes->count(),
                'con_nota' => $notasExistentes->count(),
                'aprobados' => $promedios->filter(fn ($p) => $p >= $notaMinima)->count(),
                'desaprobados' => $promedios->filter(fn ($p) => $p < $notaMinima)->count(),
                'promedio_general' => $promedios->isNotEmpty() ? round($promedios->avg(), 1) : null,
                'nota_minima' => $notaMinima,
            ],
        ];
    }

    /**
     * Guarda notas de una unidad como borrador (estado ABIERTO). Rechaza el guardado completo
     * si el acta ya esta cerrada o si algun id_matricula_curso no pertenece al curso.
     */
    public function guardarNotas(Curso $curso, string $unidad, array $notas): void
    {
        $idsValidos = MatriculaCurso::where('id_curso', $curso->id_curso)->where('estado', 'EN_CURSO')->pluck('id_matricula_curso');

        $actaCerrada = Nota::whereIn('id_matricula_curso', $idsValidos)->where('unidad', $unidad)->where('estado', 'CERRADO')->exists();
        abort_if($actaCerrada, 422, 'El acta de esta unidad ya fue cerrada y no se puede editar.');

        DB::transaction(function () use ($idsValidos, $unidad, $notas) {
            foreach ($notas as $fila) {
                $idMatriculaCurso = (int) $fila['id_matricula_curso'];
                abort_unless($idsValidos->contains($idMatriculaCurso), 403, 'Uno de los estudiantes no pertenece a este curso.');

                Nota::updateOrCreate(
                    ['id_matricula_curso' => $idMatriculaCurso, 'unidad' => $unidad],
                    [
                        'practica' => $fila['practica'] ?? null,
                        'teoria' => $fila['teoria'] ?? null,
                        'examen' => $fila['examen'] ?? null,
                    ]
                );
            }
        });
    }

    /** Cierra el acta de una unidad: bloquea edicion futura de todas las notas de esa unidad en el curso. */
    public function cerrarActa(Curso $curso, string $unidad): int
    {
        $idsValidos = MatriculaCurso::where('id_curso', $curso->id_curso)->where('estado', 'EN_CURSO')->pluck('id_matricula_curso');
        abort_if($idsValidos->isEmpty(), 422, 'No hay estudiantes matriculados en este curso.');

        $tieneNotas = Nota::whereIn('id_matricula_curso', $idsValidos)->where('unidad', $unidad)->exists();
        abort_unless($tieneNotas, 422, 'No hay notas registradas en esta unidad todavía.');

        return DB::transaction(fn () => Nota::whereIn('id_matricula_curso', $idsValidos)->where('unidad', $unidad)->update(['estado' => 'CERRADO']));
    }

    /** Sesion de asistencia real para curso+fecha. No duplica: una sola sesion por curso+docente+fecha. */
    public function getOrCrearSesionAsistencia(Curso $curso, string $fecha): AsistenciaSesion
    {
        $periodo = $this->getPeriodoActivo();
        abort_if(! $periodo, 422, 'No hay un periodo académico activo.');

        return AsistenciaSesion::firstOrCreate(
            ['id_curso' => $curso->id_curso, 'id_docente' => $curso->id_docente, 'fecha_sesion' => $fecha],
            ['id_periodo' => $periodo->id_periodo, 'estado' => 'REALIZADA']
        );
    }

    /** Estudiantes matriculados con su estado de asistencia de la fecha, resumen del dia y alerta de asistencia historica baja. */
    public function getAsistenciaPorCursoFecha(Curso $curso, string $fecha): array
    {
        $sesion = AsistenciaSesion::where('id_curso', $curso->id_curso)
            ->where('id_docente', $curso->id_docente)
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

        $estudiantes = MatriculaCurso::where('id_curso', $curso->id_curso)
            ->where('estado', 'EN_CURSO')
            ->with('matricula.estudiante')
            ->get()
            ->map(function (MatriculaCurso $mc) use ($detallePorEstudiante, $historico) {
                $estudiante = $mc->matricula->estudiante;
                $detalle = $detallePorEstudiante->get($estudiante->id_estudiante);
                $hist = $historico->get($estudiante->id_estudiante);
                $pctHistorico = $hist && $hist->total > 0 ? round($hist->presentes / $hist->total * 100, 1) : null;

                return [
                    'id_estudiante' => $estudiante->id_estudiante,
                    'codigo_estudiante' => $estudiante->codigo_estudiante,
                    'dni' => $estudiante->dni,
                    'nombre_completo' => trim($estudiante->apellido_paterno.' '.$estudiante->apellido_materno).', '.$estudiante->nombres,
                    'estado' => $detalle->estado ?? null,
                    'observacion' => $detalle->observacion ?? null,
                    'asistencia_historica_pct' => $pctHistorico,
                ];
            })
            ->sortBy('nombre_completo')
            ->values();

        $conEstado = $estudiantes->filter(fn ($e) => $e['estado'] !== null);

        return [
            'sesion' => $sesion ? ['id_sesion' => $sesion->id_sesion, 'estado' => $sesion->estado, 'tema' => $sesion->tema] : null,
            'estudiantes' => $estudiantes,
            'resumen' => [
                'total' => $estudiantes->count(),
                'presentes' => $conEstado->filter(fn ($e) => $e['estado'] === 'PRESENTE')->count(),
                'ausentes' => $conEstado->filter(fn ($e) => $e['estado'] === 'AUSENTE')->count(),
                'tardanzas' => $conEstado->filter(fn ($e) => $e['estado'] === 'TARDANZA')->count(),
                'justificados' => $conEstado->filter(fn ($e) => $e['estado'] === 'JUSTIFICADO')->count(),
                'porcentaje_asistencia' => $conEstado->isNotEmpty()
                    ? round($conEstado->filter(fn ($e) => $e['estado'] === 'PRESENTE')->count() / $conEstado->count() * 100, 1)
                    : null,
            ],
            'alertas_bajo_70' => $estudiantes->filter(fn ($e) => $e['asistencia_historica_pct'] !== null && $e['asistencia_historica_pct'] < 70)->values(),
        ];
    }

    /** Guarda asistencia de una fecha: crea la sesion si no existe (no duplica) y hace upsert por estudiante en transaccion. */
    public function guardarAsistencia(Curso $curso, string $fecha, ?string $tema, array $registros): void
    {
        $idsEstudiantesValidos = MatriculaCurso::where('id_curso', $curso->id_curso)
            ->where('estado', 'EN_CURSO')
            ->with('matricula')
            ->get()
            ->pluck('matricula.id_estudiante');

        DB::transaction(function () use ($curso, $fecha, $tema, $registros, $idsEstudiantesValidos) {
            $sesion = $this->getOrCrearSesionAsistencia($curso, $fecha);

            if ($tema) {
                $sesion->update(['tema' => $tema]);
            }

            foreach ($registros as $fila) {
                $idEstudiante = (int) $fila['id_estudiante'];
                abort_unless($idsEstudiantesValidos->contains($idEstudiante), 403, 'Uno de los estudiantes no pertenece a este curso.');

                AsistenciaDetalle::updateOrCreate(
                    ['id_sesion' => $sesion->id_sesion, 'id_estudiante' => $idEstudiante],
                    ['estado' => $fila['estado'], 'observacion' => $fila['observacion'] ?? null]
                );
            }
        });
    }
}
