<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cursos\StoreCursoRequest;
use App\Http\Requests\Cursos\UpdateCursoRequest;
use App\Models\Curso;
use App\Services\Academic\CursoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function __construct(private readonly CursoService $cursos) {}

    public function index(Request $request): JsonResponse
    {
        $cursos = $this->cursos->listar(
            $request->query('semestre'),
            $request->query('id_docente') ? (int) $request->query('id_docente') : null
        );

        return response()->json(['ok' => true, 'cursos' => $cursos]);
    }

    public function store(StoreCursoRequest $request): JsonResponse
    {
        $curso = $this->cursos->crear($request->validated());

        return response()->json(['ok' => true, 'curso' => $curso], 201);
    }

    public function update(UpdateCursoRequest $request, Curso $curso): JsonResponse
    {
        $curso = $this->cursos->actualizar($curso, $request->validated());

        return response()->json(['ok' => true, 'curso' => $curso]);
    }
}
