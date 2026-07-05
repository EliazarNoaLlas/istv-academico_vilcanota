<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Http\Requests\Horarios\GenerateHorarioIaRequest;
use App\Services\Horarios\HorarioAiGeneratorService;
use App\Services\Horarios\HorarioCatalogService;
use App\Services\Horarios\HorarioQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DirectorHorarioController extends Controller
{
    public function __construct(
        private readonly HorarioQueryService $consultas,
        private readonly HorarioCatalogService $catalogos,
        private readonly HorarioAiGeneratorService $generadorIa,
    ) {}

    public function page(): View
    {
        return view('director.horarios.index', $this->catalogos->obtener());
    }

    public function index(Request $request): JsonResponse
    {
        $horarios = $this->consultas->listar(
            $request->query('id_docente') ? (int) $request->query('id_docente') : null,
            $request->query('id_curso') ? (int) $request->query('id_curso') : null,
            $request->query('semestre'),
            $request->query('id_programa') ? (int) $request->query('id_programa') : null,
        );

        return response()->json(['ok' => true, 'horarios' => $horarios]);
    }

    public function catalogs(): JsonResponse
    {
        return response()->json(['ok' => true, ...$this->catalogos->obtener()]);
    }

    /** Genera con IA el horario de un programa restringido al semestre indicado. */
    public function generateSemester(GenerateHorarioIaRequest $request): JsonResponse
    {
        return response()->json($this->generadorIa->generar([
            ...$request->validated(),
            'id_usuario' => $request->user()?->id_usuario,
        ]));
    }

    /** Genera con IA el horario de un programa completo, sin restringir por semestre. */
    public function generateAllSemesters(GenerateHorarioIaRequest $request): JsonResponse
    {
        return response()->json($this->generadorIa->generar([
            ...$request->validated(),
            'semestre' => null,
            'id_usuario' => $request->user()?->id_usuario,
        ]));
    }

    public function aprobarGeneracionIa(int $idGeneracion): JsonResponse
    {
        return response()->json($this->generadorIa->aprobar($idGeneracion));
    }

    public function descartarGeneracionIa(int $idGeneracion): JsonResponse
    {
        return response()->json($this->generadorIa->descartar($idGeneracion));
    }

    public function repararGeneracionIa(Request $request, int $idGeneracion): JsonResponse
    {
        return response()->json($this->generadorIa->reparar($idGeneracion, $request->integer('max_intentos_reparacion') ?: null));
    }

    public function estadoGeneracionIa(int $idGeneracion): JsonResponse
    {
        return response()->json($this->generadorIa->estado($idGeneracion));
    }
}
