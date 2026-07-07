<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portafolios\UploadPortafolioRequest;
use App\Models\Curso;
use App\Services\Docente\DocentePortalService;
use App\Services\Portafolios\PortafolioDocumentoService;
use App\Services\Portafolios\PortafolioUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocentePortafolioController extends Controller
{
    public function __construct(
        private PortafolioDocumentoService $documentos,
        private PortafolioUploadService $subida,
        private DocentePortalService $portal,
    ) {}

    public function page(): View
    {
        $docente = $this->portal->getDocenteActual(auth()->user());
        $periodo = $this->portal->getPeriodoActivo();

        $docente->setRelation('cursos', $this->portal->getCursosAsignados($docente, $periodo));

        return view('docente.portafolio.index', [
            'miDocente' => $docente,
            'periodoActivo' => $periodo,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        // id_docente nunca se toma del cliente: se usa el perfil docente propio del
        // usuario autenticado, para que un docente no pueda listar documentos de otro.
        $miDocente = $request->user()->miDocentePropio();

        if (! $miDocente) {
            return response()->json(['ok' => false, 'mensaje' => 'Su cuenta no tiene un perfil docente asociado.'], 403);
        }

        $idCurso = $request->query('id_curso') ? (int) $request->query('id_curso') : null;
        $documentos = $this->documentos->listar(null, $idCurso, $request->query('tipo'), $miDocente->id_docente);

        return response()->json(['ok' => true, 'documentos' => $documentos]);
    }

    public function store(UploadPortafolioRequest $request): JsonResponse
    {
        $miDocente = $request->user()->miDocentePropio();

        if (! $miDocente) {
            return response()->json(['ok' => false, 'mensaje' => 'Su cuenta no tiene un perfil docente asociado.'], 403);
        }

        $curso = Curso::find((int) $request->validated('id_curso'));

        if (! $curso || $curso->id_docente !== $miDocente->id_docente) {
            return response()->json(['ok' => false, 'mensaje' => 'El curso no existe o no te pertenece.'], 404);
        }

        try {
            $documento = $this->subida->subir(
                $request->file('documento'),
                $miDocente->id_docente,
                (int) $request->validated('id_curso'),
                (int) $request->validated('id_periodo'),
                $request->validated('tipo'),
                $request->validated('titulo'),
            );
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['ok' => false, 'mensaje' => 'No se pudo subir el documento.'], 500);
        }

        return response()->json(['ok' => true, 'documento' => $documento], 201);
    }
}
