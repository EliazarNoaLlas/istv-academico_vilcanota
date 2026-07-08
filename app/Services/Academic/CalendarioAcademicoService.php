<?php

namespace App\Services\Academic;

use App\Models\CalendarioEvento;
use App\Models\PeriodoAcademico;
use Illuminate\Database\Eloquent\Collection;

class CalendarioAcademicoService
{
    public const TIPOS = [
        'EVALUACION' => ['etiqueta' => 'Evaluación', 'color' => 'blue'],
        'FERIADO' => ['etiqueta' => 'Feriado', 'color' => 'red'],
        'PLAZO_ADMINISTRATIVO' => ['etiqueta' => 'Plazo administrativo', 'color' => 'gold'],
        'MATRICULA' => ['etiqueta' => 'Matrícula', 'color' => 'teal'],
        'REUNION_CAPACITACION' => ['etiqueta' => 'Reunión / Capacitación', 'color' => 'purple'],
    ];

    public function listarPorMes(int $anio, int $mes): Collection
    {
        return CalendarioEvento::whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->orderBy('fecha')
            ->get();
    }

    public function kpis(): array
    {
        $periodoActivo = PeriodoAcademico::where('estado', 'ACTIVO')->first();

        $delSemestre = CalendarioEvento::query()
            ->when($periodoActivo, fn ($q) => $q->where(function ($qq) use ($periodoActivo) {
                $qq->where('id_periodo', $periodoActivo->id_periodo)
                    ->orWhereBetween('fecha', [$periodoActivo->fecha_inicio, $periodoActivo->fecha_fin]);
            }));

        return [
            'eventos_semestre' => (clone $delSemestre)->count(),
            'evaluaciones' => (clone $delSemestre)->where('tipo', 'EVALUACION')->count(),
            'plazos_administrativos' => (clone $delSemestre)->where('tipo', 'PLAZO_ADMINISTRATIVO')->count(),
            'proximos_7_dias' => CalendarioEvento::whereBetween('fecha', [now()->toDateString(), now()->addDays(7)->toDateString()])->count(),
        ];
    }

    public function proximosEventos(int $limite = 8): Collection
    {
        return CalendarioEvento::where('fecha', '>=', now()->toDateString())
            ->orderBy('fecha')
            ->limit($limite)
            ->get();
    }

    public function crear(array $datos, ?int $idUsuario): CalendarioEvento
    {
        return CalendarioEvento::create([
            'titulo' => $datos['titulo'],
            'tipo' => $datos['tipo'],
            'fecha' => $datos['fecha'],
            'descripcion' => $datos['descripcion'] ?? null,
            'id_periodo' => $datos['id_periodo'] ?? PeriodoAcademico::where('estado', 'ACTIVO')->value('id_periodo'),
            'id_usuario_creador' => $idUsuario,
        ]);
    }

    public function actualizar(CalendarioEvento $evento, array $datos): CalendarioEvento
    {
        $evento->update([
            'titulo' => $datos['titulo'],
            'tipo' => $datos['tipo'],
            'fecha' => $datos['fecha'],
            'descripcion' => $datos['descripcion'] ?? null,
        ]);

        return $evento;
    }

    public function eliminar(CalendarioEvento $evento): void
    {
        $evento->delete();
    }
}
