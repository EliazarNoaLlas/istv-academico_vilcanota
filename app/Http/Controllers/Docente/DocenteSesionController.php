<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sesiones\UploadSesionAprendizajeRequest;
use App\Models\SesionAprendizaje;
use App\Services\Academic\SesionAprendizajeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocenteSesionController extends Controller
{
    public function __construct(private SesionAprendizajeService $sesiones) {}

    public function page(): View
    {
        return view('docente.sesiones.index');
    }

    public function index(Request $request): JsonResponse
    {
        $idDocente = $request->user()->miDocentePropio()?->id_docente;
        $idCurso = (int) $request->query('id_curso');

        if (! $idDocente || ! $idCurso) {
            return response()->json(['ok' => false, 'mensaje' => 'Faltan datos de docente o curso.'], 422);
        }

        return response()->json([
            'ok' => true,
            'sesiones' => $this->sesiones->listarPorCurso($idCurso, $idDocente),
        ]);
    }

    public function store(UploadSesionAprendizajeRequest $request): JsonResponse
    {
        try {
            $sesion = $this->sesiones->subir(
                $request->file('archivo'),
                (int) $request->validated('id_curso'),
                $request->user()->miDocentePropio()?->id_docente,
                $request->validated('titulo'),
                $request->validated('numero_sesion'),
            );
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['ok' => false, 'mensaje' => 'No se pudo subir la sesion.'], 500);
        }

        return response()->json(['ok' => true, 'sesion' => $sesion], 201);
    }

    public function destroy(Request $request, SesionAprendizaje $sesion): JsonResponse
    {
        $eliminado = $this->sesiones->eliminar($sesion, $request->user()->miDocentePropio()?->id_docente);

        if (! $eliminado) {
            return response()->json(['ok' => false, 'mensaje' => 'La sesion no pertenece a este docente.'], 403);
        }

        return response()->json(['ok' => true]);
    }

    public function descargar(Request $request, SesionAprendizaje $sesion): StreamedResponse
    {
        $idDocente = $request->user()->miDocentePropio()?->id_docente;

        abort_unless($sesion->id_docente === $idDocente, 403, 'Esta sesion no pertenece a este docente.');
        abort_unless($sesion->archivo, 404, 'Esta sesion no tiene un archivo asociado.');

        return Storage::disk('local')->download($sesion->archivo, $sesion->titulo);
    }
}
