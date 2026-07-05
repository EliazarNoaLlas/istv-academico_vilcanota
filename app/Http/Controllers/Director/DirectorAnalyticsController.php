<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Services\Director\DirectorAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DirectorAnalyticsController extends Controller
{
    public function __construct(private readonly DirectorAnalyticsService $analytics) {}

    public function page(): View
    {
        return view('director.analytics.index');
    }

    public function data(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'rendimiento_programas' => $this->analytics->rendimientoPorPrograma(),
            'portafolio' => $this->analytics->entregaPortafolio(),
            'silabo_por_ciclo' => $this->analytics->cumplimientoSilaboPorCiclo(),
            'riesgo_vs_asistencia' => $this->analytics->riesgoVsAsistencia(),
        ]);
    }
}
