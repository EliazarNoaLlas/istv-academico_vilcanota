<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Services\Academic\CursoService;
use App\Services\Academic\DocenteService;
use App\Services\Portafolios\PortafolioDocumentoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DirectorPortafolioController extends Controller
{
    public function __construct(
        private readonly PortafolioDocumentoService $documentos,
        private readonly CursoService $cursos,
        private readonly DocenteService $docentes,
    ) {}

    public function page(): View
    {
        return view('director.portafolio.index', [
            'cursos' => $this->cursos->listar(),
            'docentes' => $this->docentes->listar(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $documentos = $this->documentos->listar(
            null,
            $request->query('id_curso') ? (int) $request->query('id_curso') : null,
            $request->query('tipo'),
            $request->query('id_docente') ? (int) $request->query('id_docente') : null,
            $request->query('estado'),
        );

        return response()->json(['ok' => true, 'documentos' => $documentos]);
    }
}
