<?php

namespace App\Services\Academic;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\DocentePrograma;
use App\Services\Horarios\HorarioConflictService;
use Illuminate\Database\Eloquent\Collection;

class DocenteService
{
    /** Dias canonicos con horario semanal regular (igual que HorarioValidationService::DIAS_CANONICOS, sin domingo). */
    private const DIAS_SEMANA = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    /** Limite institucional de horas semanales por docente, igual al umbral SOBRECARGA de estadoCarga(). */
    private const LIMITE_HORAS_SEMANALES = 40;

    public function __construct(private readonly HorarioConflictService $conflictos) {}

    public function listar(): Collection
    {
        return Docente::query()
            ->where('estado_academico', 'ACTIVO')
            ->with('usuario')
            ->join('usuarios', 'usuarios.id_usuario', '=', 'docentes.id_usuario')
            ->orderBy('usuarios.nombres')
            ->select('docentes.*')
            ->get();
    }

    /**
     * Docentes activos con cursos asignados y carga semanal real (bloques del
     * horario generado, agrupados por dia), para los paneles de coordinador
     * y director.
     */
    public function listarConCarga(): Collection
    {
        return Docente::query()
            ->where('estado_academico', 'ACTIVO')
            ->with(['cursos:id_curso,id_docente,nombre_curso,total_horas', 'usuario', 'programas:id_programa,nombre', 'horarios:id_horario,id_docente,dia'])
            ->join('usuarios', 'usuarios.id_usuario', '=', 'docentes.id_usuario')
            ->orderBy('usuarios.nombres')
            ->select('docentes.*')
            ->get()
            ->map(function ($docente) {
                $docente->cursos_count = $docente->cursos->count();
                $docente->carga_horaria = $docente->cursos->sum('total_horas');

                $porDia = $docente->horarios->countBy('dia');
                $docente->carga_por_dia = collect(self::DIAS_SEMANA)
                    ->map(fn ($dia) => $porDia->get($dia, 0))
                    ->values();
                $docente->carga_semanal = $docente->carga_por_dia->sum();
                $docente->estado_carga = $this->estadoCarga($docente->carga_semanal);

                return $docente;
            });
    }

    public function cursosDe(int $idDocente): Collection
    {
        $docente = Docente::with(['cursos' => function ($q) {
            $q->withCount('sesionesAprendizaje')->orderBy('nombre_curso');
        }])->findOrFail($idDocente);

        return $docente->cursos;
    }

    /** Cursos ya asignados a un docente y cursos activos sin docente que puede tomar (segun sus programas). */
    public function cursosDisponibles(Docente $docente): array
    {
        $idsProgramas = $docente->programas()->pluck('programas_estudio.id_programa')->all();

        $disponibles = Curso::query()
            ->whereNull('id_docente')
            ->where('estado', 'ACTIVO')
            ->when($idsProgramas, fn ($q) => $q->whereIn('id_programa', $idsProgramas))
            ->with('programa')
            ->orderBy('nombre_curso')
            ->get();

        return [
            'asignados' => $docente->cursos()->with('programa')->orderBy('nombre_curso')->get(),
            'disponibles' => $disponibles,
        ];
    }

    /** Asigna cursos sin docente (whereNull protege de pisar una asignacion previa) a este docente. */
    public function asignarCursos(Docente $docente, array $idsCursos): Collection
    {
        Curso::whereIn('id_curso', $idsCursos)
            ->whereNull('id_docente')
            ->update(['id_docente' => $docente->id_docente]);

        $idsProgramas = Curso::whereIn('id_curso', $idsCursos)->whereNotNull('id_programa')->pluck('id_programa')->unique();
        foreach ($idsProgramas as $idPrograma) {
            DocentePrograma::firstOrCreate(['id_docente' => $docente->id_docente, 'id_programa' => $idPrograma]);
        }

        return $docente->cursos()->with('programa')->orderBy('nombre_curso')->get();
    }

    /**
     * Detalle completo de un docente para el modal "Ver docente": cursos con
     * aula/periodo (tomados del horario ya generado, si existe), distribucion
     * semanal real y conflictos de horario detectados con el mismo motor que
     * usa el generador de horarios.
     */
    public function detalle(Docente $docente): array
    {
        $docente->loadMissing(['usuario', 'cursos.programa', 'horarios.periodo']);

        $porDia = $docente->horarios->countBy('dia');
        $cargaPorDia = collect(self::DIAS_SEMANA)->map(fn ($dia) => $porDia->get($dia, 0))->values();
        $cargaSemanal = $cargaPorDia->sum();

        $cursos = $docente->cursos->map(function (Curso $curso) use ($docente) {
            $bloquesCurso = $docente->horarios->where('id_curso', $curso->id_curso);
            $horario = $bloquesCurso->first();

            return [
                'id_curso' => $curso->id_curso,
                'nombre_curso' => $curso->nombre_curso,
                'programa' => $curso->programa?->nombre,
                'ciclo' => $curso->ciclo,
                // Bloques semanales realmente generados en el horario (no horas_ud, que es la exigencia
                // curricular): asi el total de la tabla coincide con carga_semanal mostrada arriba.
                'horas_semana' => $bloquesCurso->count(),
                'periodo' => $horario?->periodo?->codigo,
                'aula' => $horario?->aula,
            ];
        })->values();

        $bloques = $docente->horarios->map(fn ($h) => [
            'id_curso' => $h->id_curso,
            'id_docente' => $h->id_docente,
            'dia' => $h->dia,
            'hora_inicio' => $h->hora_inicio?->format('H:i'),
            'hora_fin' => $h->hora_fin?->format('H:i'),
            'aula' => $h->aula,
        ])->all();

        return [
            'docente' => $docente,
            'cursos_count' => $docente->cursos->count(),
            'carga_semanal' => $cargaSemanal,
            'limite_horas' => self::LIMITE_HORAS_SEMANALES,
            'disponible' => self::LIMITE_HORAS_SEMANALES - $cargaSemanal,
            'estado_carga' => $this->estadoCarga($cargaSemanal),
            'carga_por_dia' => $cargaPorDia,
            'cursos' => $cursos,
            'conflictos' => $this->conflictos->detectar($bloques),
        ];
    }

    public function porUsuario(string $nombreUsuario): ?Docente
    {
        $usuario = \App\Models\User::where('usuario', $nombreUsuario)->first();

        return $usuario?->docente;
    }

    /** Umbrales alineados a la leyenda de carga semanal mostrada en el panel de director. */
    private function estadoCarga(int $carga): string
    {
        return match (true) {
            $carga === 0 => 'SIN_CARGA',
            $carga <= 20 => 'NORMAL',
            $carga <= 30 => 'MODERADA',
            $carga <= 40 => 'ALTA',
            default => 'SOBRECARGA',
        };
    }
}
