<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Services\Academic\DocenteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocenteCursoController extends Controller
{
    public function __construct(private DocenteService $docentes) {}

    /** Reemplaza a docente_cursos.php: usa la sesion autenticada, no un parametro libre. */
    public function index(Request $request): JsonResponse
    {
        $idDocente = $request->user()->docente?->id_docente;

        if (! $idDocente) {
            return response()->json(['ok' => false, 'mensaje' => 'El usuario no tiene un docente asociado.'], 422);
        }

        return response()->json(['ok' => true, 'cursos' => $this->docentes->cursosDe($idDocente)]);
    }
}
