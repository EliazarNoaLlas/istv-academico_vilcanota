<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Http\Requests\Horarios\ClearHorarioRequest;
use App\Http\Requests\Horarios\DetectHorarioConflictsRequest;
use App\Http\Requests\Horarios\GenerateHorarioIaRequest;
use App\Http\Requests\Horarios\StoreHorarioRequest;
use App\Models\Docente;
use App\Services\Horarios\HorarioAiGeneratorService;
use App\Services\Horarios\HorarioCatalogService;
use App\Services\Horarios\HorarioColorService;
use App\Services\Horarios\HorarioConflictService;
use App\Services\Horarios\HorarioPersistenceService;
use App\Services\Horarios\HorarioQueryService;
use App\Services\Horarios\HorarioValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DirectorHorarioController extends Controller
{
    public function __construct(
        private readonly HorarioQueryService $consultas,
        private readonly HorarioCatalogService $catalogos,
        private readonly HorarioAiGeneratorService $generadorIa,
        private readonly HorarioConflictService $conflictos,
        private readonly HorarioValidationService $reglas,
        private readonly HorarioPersistenceService $persistencia,
        private readonly HorarioColorService $colores,
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

        $horarios->each(function ($horario) {
            $horario->color = $this->colores->paraCurso($horario->id_curso);
        });

        return response()->json([
            'ok' => true,
            'horarios' => $horarios,
            'docentes_activos' => Docente::where('estado_academico', 'ACTIVO')->count(),
        ]);
    }

    public function catalogs(): JsonResponse
    {
        return response()->json(['ok' => true, ...$this->catalogos->obtener()]);
    }

    public function store(StoreHorarioRequest $request): JsonResponse
    {
        $bloques = $request->validated('horarios');

        $errores = $this->reglas->validarReglasInstitucionales($bloques);
        if ($errores !== []) {
            return response()->json(['ok' => false, 'errores' => $errores], 422);
        }

        $conflictos = $this->conflictos->detectar($bloques);
        if ($conflictos !== []) {
            return response()->json(['ok' => false, 'conflictos' => $conflictos], 422);
        }

        $filtros = array_filter([
            'id_docente' => $request->validated('filtro_docente'),
            'semestre' => $request->validated('filtro_semestre'),
            'id_programa' => $request->validated('filtro_programa'),
        ]);

        $this->persistencia->guardar($bloques, $filtros);

        return response()->json(['ok' => true]);
    }

    public function detectConflicts(DetectHorarioConflictsRequest $request): JsonResponse
    {
        $bloques = $request->validated('horarios');

        return response()->json([
            'ok' => true,
            'conflictos' => $this->conflictos->detectar($bloques),
            'errores_institucionales' => $this->reglas->validarReglasInstitucionales($bloques),
        ]);
    }

    public function clear(ClearHorarioRequest $request): JsonResponse
    {
        $filtros = array_filter([
            'id_docente' => $request->validated('filtro_docente'),
            'semestre' => $request->validated('filtro_semestre'),
            'id_programa' => $request->validated('filtro_programa'),
        ]);

        $eliminados = $this->persistencia->eliminarPorFiltro($filtros);

        return response()->json(['ok' => true, 'eliminados' => $eliminados]);
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
