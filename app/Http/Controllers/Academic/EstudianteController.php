<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Services\Academic\EstudianteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EstudianteController extends Controller
{
    public function __construct(private EstudianteService $estudiantes) {}

    public function index(Request $request): JsonResponse
    {
        $estudiantes = $this->estudiantes->listar(
            $request->query('id_programa') ? (int) $request->query('id_programa') : null,
            $request->query('ciclo')
        );

        return response()->json(['ok' => true, 'estudiantes' => $estudiantes]);
    }
}
