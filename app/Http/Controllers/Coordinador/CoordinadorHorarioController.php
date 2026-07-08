<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Http\Requests\Horarios\ClearHorarioRequest;
use App\Http\Requests\Horarios\DetectHorarioConflictsRequest;
use App\Http\Requests\Horarios\GenerateHorarioIaRequest;
use App\Http\Requests\Horarios\GenerateSemestreDsiRequest;
use App\Http\Requests\Horarios\GenerateSemestresPendientesDsiRequest;
use App\Http\Requests\Horarios\StoreHorarioRequest;
use App\Models\Docente;
use App\Models\HorarioIaGenerado;
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

class CoordinadorHorarioController extends Controller
{
    public function __construct(
        private HorarioQueryService       $consultas,
        private HorarioConflictService    $conflictos,
        private HorarioValidationService  $reglas,
        private HorarioPersistenceService $persistencia,
        private HorarioCatalogService     $catalogos,
        private HorarioColorService       $colores,
        private HorarioAiGeneratorService $generadorIa,
    )
    {
    }

    public function page(): View
    {
        return view('coordinador.horarios.index', $this->catalogosPropios());
    }

    /** Igual que HorarioCatalogService::obtener() pero con el selector de programa
     *  restringido al unico programa del coordinador (nunca ve los demas). */
    private function catalogosPropios(): array
    {
        $catalogos = $this->catalogos->obtener();
        $catalogos['programas'] = array_values(array_filter(
            $catalogos['programas']->all(),
            fn($programa) => $programa->id_programa === auth()->user()->id_programa,
        ));

        // Si el coordinador tambien dicta clases, debe poder asignarse a si
        // mismo un curso en el horario aunque su docente_programa aun no
        // exista (recien se crea al guardar un curso u horario a su nombre).
        $miDocente = auth()->user()->miDocentePropio();
        if ($miDocente && !$catalogos['docentes']->contains('id_docente', $miDocente->id_docente)) {
            $catalogos['docentes']->push($miDocente->load('usuario'));
        }

        return $catalogos;
    }

    public function index(Request $request): JsonResponse
    {
        $horarios = $this->consultas->listar(
            $request->query('id_docente') ? (int)$request->query('id_docente') : null,
            $request->query('id_curso') ? (int)$request->query('id_curso') : null,
            $request->query('semestre'),
            $request->query('id_programa') ? (int)$request->query('id_programa') : null,
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
        return response()->json(['ok' => true, ...$this->catalogosPropios()]);
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
            // Nunca se toma del cliente: el coordinador solo guarda horarios de su propio programa.
            'id_programa' => auth()->user()->id_programa,
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
            // Nunca se toma del cliente: el coordinador solo limpia horarios de su propio programa.
            'id_programa' => auth()->user()->id_programa,
        ]);

        $eliminados = $this->persistencia->eliminarPorFiltro($filtros);

        return response()->json(['ok' => true, 'eliminados' => $eliminados]);
    }

    /** Genera con IA el horario de un programa restringido al semestre indicado. */
    public function generateSemester(GenerateHorarioIaRequest $request): JsonResponse
    {
        return response()->json($this->generadorIa->generar([
            ...$request->validated(),
            // Nunca se toma del cliente: solo puede generar horarios de su propio programa.
            'id_programa' => auth()->user()->id_programa,
            'id_usuario' => $request->user()?->id_usuario,
        ]));
    }

    /** Genera con IA el horario de un programa completo, sin restringir por semestre. */
    public function generateAllSemesters(GenerateHorarioIaRequest $request): JsonResponse
    {
        return response()->json($this->generadorIa->generar([
            ...$request->validated(),
            'id_programa' => auth()->user()->id_programa,
            'semestre' => null,
            'id_usuario' => $request->user()?->id_usuario,
        ]));
    }

    public function aprobarGeneracionIa(int $idGeneracion): JsonResponse
    {
        $this->verificarGeneracionPropia($idGeneracion);

        return response()->json($this->generadorIa->aprobar($idGeneracion));
    }

    public function descartarGeneracionIa(int $idGeneracion): JsonResponse
    {
        $this->verificarGeneracionPropia($idGeneracion);

        return response()->json($this->generadorIa->descartar($idGeneracion));
    }

    public function repararGeneracionIa(Request $request, int $idGeneracion): JsonResponse
    {
        $this->verificarGeneracionPropia($idGeneracion);

        return response()->json($this->generadorIa->reparar($idGeneracion, $request->integer('max_intentos_reparacion') ?: null));
    }

    public function estadoGeneracionIa(int $idGeneracion): JsonResponse
    {
        $this->verificarGeneracionPropia($idGeneracion);

        return response()->json($this->generadorIa->estado($idGeneracion));
    }

    /**
     * HorarioIaGenerado no tiene scope global (no cuelga de un curso todavia
     * guardado): se identifica el programa de la generacion por el filtro
     * con el que se creo y se compara contra el del coordinador autenticado.
     */
    private function verificarGeneracionPropia(int $idGeneracion): void
    {
        $generacion = HorarioIaGenerado::find($idGeneracion);
        $idProgramaGeneracion = $generacion?->metadata_json['filtro']['id_programa'] ?? null;

        if (!$generacion || (int)$idProgramaGeneracion !== (int)auth()->user()->id_programa) {
            abort(404);
        }
    }

    /** Fase 3: genera de forma determinista (sin LLM) el horario de un solo semestre DSI. Nunca toma id_programa del cliente: solo el propio. */
    public function generateSemesterDsi(GenerateSemestreDsiRequest $request): JsonResponse
    {
        return response()->json($this->generadorIa->generarSemestreDsi(
            auth()->user()->id_programa,
            $request->integer('id_periodo'),
            (string)$request->validated('semestre'),
            (string)($request->validated('modo') ?? 'normal'),
            $request->integer('seed') ?: null,
        ));
    }

    /** Fase 3: genera en secuencia II, IV, V y VI del programa propio. No toca I ni III. */
    public function generatePendingSemestersDsi(GenerateSemestresPendientesDsiRequest $request): JsonResponse
    {
        return response()->json($this->generadorIa->generarSemestresPendientesDsi(
            auth()->user()->id_programa,
            $request->integer('id_periodo'),
        ));
    }

    /** Fase 4: regenera un semestre limpiando solo lo generado por IA; nunca toca bloques fuente MANUAL. */
    public function regenerateSemesterDsi(GenerateSemestreDsiRequest $request): JsonResponse
    {
        return response()->json($this->generadorIa->regenerarSemestreDsi(
            auth()->user()->id_programa,
            $request->integer('id_periodo'),
            (string)$request->validated('semestre'),
        ));
    }

    /** Fase 3.2: repara dirigidamente el BORRADOR pendiente de un semestre (no regenera desde cero). */
    public function repairSemesterDsi(GenerateSemestreDsiRequest $request): JsonResponse
    {
        return response()->json($this->generadorIa->repararSemestreDsi(
            auth()->user()->id_programa,
            $request->integer('id_periodo'),
            (string)$request->validated('semestre'),
        ));
    }

    /** Fase 4: estado de solo lectura por semestre (I-VI) del programa propio. */
    public function estadoSemestresDsi(GenerateSemestresPendientesDsiRequest $request): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'semestres' => $this->consultas->resumenPorSemestre(
                auth()->user()->id_programa,
                $request->integer('id_periodo'),
            ),
        ]);
    }
}
