<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Services\Academic\ConsolidadoService;
use App\Services\RiesgoAcademico\RiesgoAcademicoCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoordinadorConsolidadoController extends Controller
{
    public function __construct(
        private ConsolidadoService $consolidado,
        private RiesgoAcademicoCalculatorService $riesgo,
    ) {}

    public function page(): View
    {
        return view('coordinador.consolidado.index');
    }

    public function index(Request $request): JsonResponse
    {
        $riesgo = $this->riesgo->calcularParaPeriodo($request->query('periodo'));

        return response()->json([
            'ok' => true,
            'cursos' => $this->consolidado->porCurso(),
            'cursos_baja_aprobacion' => $this->consolidado->cursosBajaAprobacion(),
            'riesgo' => $riesgo,
        ]);
    }
}
