<?php

namespace App\Services\Director;

use App\Services\Academic\ConsolidadoService;
use App\Services\Academic\CursoService;
use App\Services\Academic\DocenteService;
use App\Services\Academic\EstudianteService;
use App\Services\Academic\NotaService;
use App\Services\Horarios\HorarioQueryService;
use App\Services\Portafolios\PortafolioDocumentoService;
use App\Services\RiesgoAcademico\RiesgoAcademicoCalculatorService;
use InvalidArgumentException;

class ReporteDataService
{
    public function __construct(
        private readonly CursoService $cursos,
        private readonly DocenteService $docentes,
        private readonly EstudianteService $estudiantes,
        private readonly HorarioQueryService $horarios,
        private readonly NotaService $notas,
        private readonly PortafolioDocumentoService $portafolio,
        private readonly ConsolidadoService $consolidado,
        private readonly RiesgoAcademicoCalculatorService $riesgo,
    ) {}

    /** @return array{titulo:string, columnas:array<string>, filas:array<int, array<int, mixed>>} */
    public function obtener(string $tipo): array
    {
        return match ($tipo) {
            'CURSOS' => $this->cursosReporte(),
            'DOCENTES' => $this->docentesReporte(),
            'ESTUDIANTES' => $this->estudiantesReporte(),
            'HORARIOS' => $this->horariosReporte(),
            'NOTAS' => $this->notasReporte(),
            'PORTAFOLIO' => $this->portafolioReporte(),
            'CONSOLIDADO' => $this->consolidadoReporte(),
            'IA_PREDICTIVA' => $this->iaPredictivaReporte(),
            default => throw new InvalidArgumentException("Tipo de reporte no soportado: {$tipo}"),
        };
    }

    private function cursosReporte(): array
    {
        $filas = $this->cursos->listar()->map(fn ($c) => [
            $c->nombre_curso,
            $c->programa?->nombre ?? '—',
            $c->semestre,
            $c->total_horas,
            $c->docente?->usuario ? "{$c->docente->usuario->nombres} {$c->docente->usuario->apellidos}" : 'Sin asignar',
            $c->estado,
        ])->all();

        return ['titulo' => 'Consolidado de Cursos', 'columnas' => ['Curso', 'Programa', 'Semestre', 'Horas', 'Docente', 'Estado'], 'filas' => $filas];
    }

    private function docentesReporte(): array
    {
        $tipoDocente = ['ESPECIFICO' => 'Específico', 'GENERAL' => 'General'];
        $estadoCarga = [
            'SIN_CARGA' => 'Sin carga',
            'NORMAL' => 'Carga normal',
            'MODERADA' => 'Carga moderada',
            'ALTA' => 'Carga alta',
            'SOBRECARGA' => 'Sobrecarga',
        ];

        $filas = $this->docentes->listarConCarga()->map(fn ($d) => [
            "{$d->usuario->nombres} {$d->usuario->apellidos}",
            $d->especialidad ?? '—',
            $tipoDocente[$d->tipo_docente] ?? $d->tipo_docente,
            $d->cursos_count,
            $d->carga_semanal,
            $estadoCarga[$d->estado_carga] ?? $d->estado_carga,
        ])->all();

        return [
            'titulo' => 'Informe de Docentes',
            'columnas' => ['Docente', 'Especialidad', 'Tipo docente', 'Cursos asignados', 'Carga semanal (h)', 'Estado'],
            'filas' => $filas,
        ];
    }

    private function estudiantesReporte(): array
    {
        $filas = $this->estudiantes->listarConPromedio()->map(fn ($e) => [
            $e->codigo_estudiante,
            "{$e->nombres} {$e->apellido_paterno} {$e->apellido_materno}",
            $e->programa?->nombre ?? '—',
            $e->ciclo,
            $e->promedio_general ?? '—',
            $e->estado,
        ])->all();

        return ['titulo' => 'Informe de Estudiantes', 'columnas' => ['Código', 'Estudiante', 'Programa', 'Ciclo', 'Promedio', 'Estado'], 'filas' => $filas];
    }

    private function horariosReporte(): array
    {
        $filas = $this->horarios->listar()->map(fn ($h) => [
            $h->curso?->nombre_curso ?? '—',
            $h->docente?->usuario ? "{$h->docente->usuario->nombres} {$h->docente->usuario->apellidos}" : '—',
            $h->dia,
            substr((string) $h->hora_inicio, 0, 5),
            substr((string) $h->hora_fin, 0, 5),
            $h->aula ?? '—',
        ])->all();

        return ['titulo' => 'Reporte de Horarios', 'columnas' => ['Curso', 'Docente', 'Día', 'Inicio', 'Fin', 'Aula'], 'filas' => $filas];
    }

    private function notasReporte(): array
    {
        $filas = $this->notas->listar()->map(function ($n) {
            $estudiante = $n->matriculaCurso?->matricula?->estudiante;

            return [
                $estudiante ? "{$estudiante->nombres} {$estudiante->apellido_paterno}" : '—',
                $n->matriculaCurso?->curso?->nombre_curso ?? '—',
                $n->unidad,
                $n->promedio ?? '—',
                $n->estado,
            ];
        })->all();

        return ['titulo' => 'Consolidado de Notas', 'columnas' => ['Estudiante', 'Curso', 'Unidad', 'Promedio', 'Estado'], 'filas' => $filas];
    }

    private function portafolioReporte(): array
    {
        $filas = $this->portafolio->listar()->map(fn ($d) => [
            $d->titulo,
            $d->portafolio?->docente?->usuario ? "{$d->portafolio->docente->usuario->nombres} {$d->portafolio->docente->usuario->apellidos}" : '—',
            $d->portafolio?->curso?->nombre_curso ?? '—',
            $d->tipo,
            $d->estado,
        ])->all();

        return ['titulo' => 'Reporte de Portafolio Docente', 'columnas' => ['Documento', 'Docente', 'Curso', 'Tipo', 'Estado'], 'filas' => $filas];
    }

    private function consolidadoReporte(): array
    {
        $filas = collect($this->consolidado->porCurso())->map(fn ($c) => [
            $c['nombre_curso'],
            $c['semestre'],
            $c['docente'] ?? '—',
            $c['promedio'] ?? '—',
            $c['aprobados'],
            $c['desaprobados'],
            $c['estado_actas'],
        ])->all();

        return ['titulo' => 'Consolidado Académico Institucional', 'columnas' => ['Curso', 'Semestre', 'Docente', 'Promedio', 'Aprobados', 'Desaprobados', 'Actas'], 'filas' => $filas];
    }

    private function iaPredictivaReporte(): array
    {
        $resultado = $this->riesgo->calcularParaPeriodo(null);

        $filas = collect($resultado['estudiantes'])->map(fn ($e) => [
            $e['nombres'],
            $e['promedio_general'] ?? '—',
            $e['asistencia_pct'] ?? '—',
            $e['score_riesgo'],
            $e['nivel'],
        ])->all();

        return ['titulo' => 'Reporte de IA Predictiva (Riesgo Académico)', 'columnas' => ['Estudiante', 'Promedio', 'Asistencia %', 'Score riesgo', 'Nivel'], 'filas' => $filas];
    }
}
