<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Services\Coordinador\CoordinadorAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CoordinadorAnalyticsController extends Controller
{
    public function __construct(private readonly CoordinadorAnalyticsService $analytics) {}

    public function page(): View
    {
        return view('coordinador.analitica.index');
    }

    public function data(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'rendimiento_cursos' => $this->analytics->rendimientoPorCurso(),
            'portafolio' => $this->analytics->entregaPortafolio(),
            'silabo_por_ciclo' => $this->analytics->cumplimientoSilaboPorCiclo(),
            'riesgo_vs_asistencia' => $this->analytics->riesgoVsAsistenciaPorCiclo(),
        ]);
    }
}
