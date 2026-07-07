<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Docente\SubirPortafolioDocenteRequest;
use App\Models\PortafolioDocumento;
use App\Services\Docente\DocentePortalService;
use App\Services\Portafolios\PortafolioDocumentoService;
use App\Services\Portafolios\PortafolioUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocentePortafolioController extends Controller
{
    public function __construct(
        private DocentePortalService $portal,
        private PortafolioDocumentoService $documentos,
        private PortafolioUploadService $subida,
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

    /** Documentos (silabo/evidencia) del docente autenticado, para la grilla de tarjetas de Mi Portafolio. */
    public function index(Request $request): JsonResponse
    {
        $docente = $this->portal->getDocenteActual($request->user());
        $idCurso = $request->query('id_curso') ? (int) $request->query('id_curso') : null;
        $documentos = $this->documentos->listar(null, $idCurso, $request->query('tipo'), $docente->id_docente);

        return response()->json(['ok' => true, 'documentos' => $documentos]);
    }

    /** El docente y el periodo se resuelven en el servidor: nunca se confia en el valor enviado por el cliente. */
    public function store(SubirPortafolioDocenteRequest $request): JsonResponse
    {
        $docente = $this->portal->getDocenteActual($request->user());
        $curso = $this->portal->verificarCursoPerteneceAlDocente($docente, (int) $request->validated('id_curso'));
        $periodo = $this->portal->getPeriodoActivo();

        abort_if(! $periodo, 422, 'No hay un periodo académico activo.');

        try {
            $documento = $this->subida->subir(
                $request->file('documento'),
                $docente->id_docente,
                $curso->id_curso,
                $periodo->id_periodo,
                $request->validated('tipo'),
                $request->validated('titulo'),
            );
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['ok' => false, 'mensaje' => 'No se pudo subir el documento.'], 500);
        }

        return response()->json(['ok' => true, 'documento' => $documento], 201);
    }

    public function destroy(Request $request, PortafolioDocumento $documento): JsonResponse
    {
        $docente = $this->portal->getDocenteActual($request->user());

        abort_unless($documento->portafolio?->id_docente === $docente->id_docente, 403, 'Este documento no pertenece a tu portafolio.');

        $this->documentos->eliminar($documento);

        return response()->json(['ok' => true]);
    }

    public function descargar(Request $request, PortafolioDocumento $documento): StreamedResponse
    {
        $docente = $this->portal->getDocenteActual($request->user());

        abort_unless($documento->portafolio?->id_docente === $docente->id_docente, 403, 'Este documento no pertenece a tu portafolio.');
        abort_unless($documento->archivo, 404, 'Este documento no tiene un archivo asociado.');

        return Storage::disk('local')->download($documento->archivo, $documento->titulo);
    }
}
