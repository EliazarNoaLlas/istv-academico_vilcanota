<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Services\Director\DirectorDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DirectorDashboardController extends Controller
{
    public function __construct(private readonly DirectorDashboardService $dashboard) {}

    public function index(): View
    {
        return view('director.dashboard.index');
    }

    public function data(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'kpis' => $this->dashboard->kpis(),
            'rendimiento_programas' => $this->dashboard->rendimientoPorPrograma(),
            'portafolio' => $this->dashboard->estadoPortafolio(),
            'alertas' => $this->dashboard->alertasRecientes(),
            'actividad' => $this->dashboard->actividadReciente(),
        ]);
    }
}
