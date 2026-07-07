<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Services\Docente\DocentePortalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocenteCursoController extends Controller
{
    public function __construct(private DocentePortalService $portal) {}

    public function page(): View
    {
        return view('docente.cursos.index');
    }

    /** Cursos asignados por cursos.id_docente, con metricas reales (estudiantes, notas, asistencia, portafolio). */
    public function index(Request $request): JsonResponse
    {
        $docente = $this->portal->getDocenteActual($request->user());
        $periodo = $this->portal->getPeriodoActivo();

        return response()->json([
            'ok' => true,
            'periodo_activo' => $periodo ? ['codigo' => $periodo->codigo, 'nombre' => $periodo->nombre] : null,
            'cursos' => $this->portal->getCursosAsignados($docente, $periodo),
        ]);
    }
}
