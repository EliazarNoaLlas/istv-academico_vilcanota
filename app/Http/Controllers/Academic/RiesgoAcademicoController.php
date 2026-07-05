<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Services\RiesgoAcademico\RiesgoAcademicoCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RiesgoAcademicoController extends Controller
{
    public function __construct(private RiesgoAcademicoCalculatorService $calculadora) {}

    public function index(Request $request): JsonResponse
    {
        $resultado = $this->calculadora->calcularParaPeriodo($request->query('periodo'));

        return response()->json(['ok' => true, ...$resultado]);
    }
}
