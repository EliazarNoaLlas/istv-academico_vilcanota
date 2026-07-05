<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Services\Academic\EstudianteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoordinadorEstudianteController extends Controller
{
    public function __construct(private EstudianteService $estudiantes) {}

    public function page(): View
    {
        return view('coordinador.estudiantes.index');
    }

    public function index(Request $request): JsonResponse
    {
        $estudiantes = $this->estudiantes->listarConPromedio(
            $request->query('id_programa') ? (int) $request->query('id_programa') : null,
            $request->query('ciclo'),
        );

        return response()->json(['ok' => true, 'estudiantes' => $estudiantes]);
    }
}
