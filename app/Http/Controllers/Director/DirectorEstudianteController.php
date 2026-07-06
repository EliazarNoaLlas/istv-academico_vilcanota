<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Http\Requests\Director\StoreEstudianteRequest;
use App\Models\ProgramaEstudio;
use App\Services\Academic\EstudianteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DirectorEstudianteController extends Controller
{
    public function __construct(private readonly EstudianteService $estudiantes) {}

    public function page(): View
    {
        return view('director.estudiantes.index', [
            'programas' => ProgramaEstudio::orderBy('nombre')->get(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $estudiantes = $this->estudiantes->listarConPromedio(
            $request->query('id_programa') ? (int) $request->query('id_programa') : null,
            $request->query('ciclo'),
        );

        return response()->json(['ok' => true, 'estudiantes' => $estudiantes]);
    }

    public function store(StoreEstudianteRequest $request): JsonResponse
    {
        $estudiante = $this->estudiantes->crear($request->validated());

        return response()->json(['ok' => true, 'estudiante' => $estudiante->load('programa')], 201);
    }
}
