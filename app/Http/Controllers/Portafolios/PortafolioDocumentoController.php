<?php

namespace App\Http\Controllers\Portafolios;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portafolios\UploadPortafolioRequest;
use App\Models\Curso;
use App\Models\PortafolioDocumento;
use App\Services\Portafolios\PortafolioDocumentoService;
use App\Services\Portafolios\PortafolioUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PortafolioDocumentoController extends Controller
{
    public function __construct(
        private PortafolioDocumentoService $documentos,
        private PortafolioUploadService $subida,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $documentos = $this->documentos->listar(
            $request->query('id_portafolio') ? (int) $request->query('id_portafolio') : null,
            $request->query('id_curso') ? (int) $request->query('id_curso') : null,
            $request->query('tipo'),
        );

        return response()->json(['ok' => true, 'documentos' => $documentos]);
    }

    public function store(UploadPortafolioRequest $request): JsonResponse
    {
        // La regla "exists" del FormRequest consulta la tabla directamente y
        // no respeta el scope de coordinador; se revalida aqui contra el
        // modelo Eloquent para no permitir subir documentos a un curso de
        // otro programa manipulando el id_curso enviado.
        if (! Curso::find($request->validated('id_curso'))) {
            return response()->json(['ok' => false, 'mensaje' => 'El curso indicado no existe o no pertenece a su programa.'], 404);
        }

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

    public function destroy(PortafolioDocumento $documento): JsonResponse
    {
        try {
            $this->documentos->eliminar($documento);
        } catch (\Throwable $e) {
            Log::error('Error al eliminar documento de portafolio', ['error' => $e->getMessage()]);

            return response()->json(['ok' => false, 'mensaje' => 'No se pudo eliminar el documento.'], 500);
        }

        return response()->json(['ok' => true]);
    }
}
