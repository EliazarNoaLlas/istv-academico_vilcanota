<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Services\Academic\CursoService;
use App\Services\Academic\DocenteService;
use App\Services\Portafolios\PortafolioDocumentoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoordinadorPortafolioController extends Controller
{
    public function __construct(
        private PortafolioDocumentoService $documentos,
        private CursoService $cursos,
        private DocenteService $docentes,
    ) {}

    public function page(): View
    {
        return view('coordinador.portafolio.index', [
            'cursos' => $this->cursos->listar(),
            'docentes' => $this->docentes->listar(),
        ]);
    }

    /** Vista agregada de portafolios de todos los docentes, para revision. */
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
