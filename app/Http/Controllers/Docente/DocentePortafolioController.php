<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portafolios\UploadPortafolioRequest;
use App\Services\Portafolios\PortafolioDocumentoService;
use App\Services\Portafolios\PortafolioUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocentePortafolioController extends Controller
{
    public function __construct(
        private PortafolioDocumentoService $documentos,
        private PortafolioUploadService $subida,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $idCurso = $request->query('id_curso') ? (int) $request->query('id_curso') : null;
        $documentos = $this->documentos->listar(null, $idCurso, $request->query('tipo'));

        return response()->json(['ok' => true, 'documentos' => $documentos]);
    }

    public function store(UploadPortafolioRequest $request): JsonResponse
    {
        try {
            $documento = $this->subida->subir(
                $request->file('documento'),
                (int) $request->validated('id_docente'),
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
