<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\ProgramaEstudio;
use App\Services\Academic\CursoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DirectorCursoController extends Controller
{
    public function __construct(private readonly CursoService $cursos) {}

    public function page(): View
    {
        return view('director.cursos.index', [
            'programas' => ProgramaEstudio::orderBy('nombre')->get(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $cursos = $this->cursos->listar(
            $request->query('semestre'),
            null,
            $request->query('q'),
            null,
            $request->query('id_programa') ? (int) $request->query('id_programa') : null,
        );

        return response()->json(['ok' => true, 'cursos' => $cursos]);
    }
}
