<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Services\Academic\CursoService;
use App\Services\Academic\NotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DirectorNotaController extends Controller
{
    public function __construct(
        private readonly NotaService  $notas,
        private readonly CursoService $cursos,
    )
    {
    }

    public function page(): View
    {
        return view('director.notas.index', [
            'cursos' => $this->cursos->listar(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $idCurso = $request->query('id_curso') ? (int)$request->query('id_curso') : null;
        $unidad = $request->query('unidad');

        return response()->json([
            'ok' => true,
            'notas' => $this->notas->listar($idCurso, $unidad),
            'resumen' => $this->notas->resumen($idCurso),
        ]);
    }
}
