<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Models\AsistenciaSesion;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\MatriculaCurso;
use App\Models\PeriodoAcademico;
use App\Models\Scopes\CoordinadorProgramaDirectoScope;
use App\Services\Academic\AsistenciaService;
use App\Services\Academic\CursoService;
use App\Services\Academic\DocenteService;
use App\Services\Academic\NotaService;
use App\Services\Portafolios\PortafolioDocumentoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoordinadorPortafolioController extends Controller
{
    public function __construct(
        private PortafolioDocumentoService $documentos,
        private CursoService $cursos,
        private DocenteService $docentes,
        private NotaService $notas,
        private AsistenciaService $asistencia,
    ) {}

    public function page(): View
    {
        // El coordinador puede ademas tener perfil docente propio (dicta
        // cursos ademas de coordinar): si lo tiene, ve una seccion propia
        // "Mi portafolio" separada del panel de revision de todos. Se usa
        // miDocentePropio()/withoutGlobalScope porque el scope de aislamiento
        // por programa esta pensado para OTROS docentes, no para ocultarle a
        // alguien su propia cuenta.
        $miDocente = auth()->user()->miDocentePropio();

        if ($miDocente) {
            $miDocente->setRelation(
                'cursos',
                Curso::withoutGlobalScope(CoordinadorProgramaDirectoScope::class)->where('id_docente', $miDocente->id_docente)->get(),
            );
        }

        return view('coordinador.portafolio.index', [
            'cursos' => $this->cursos->listar(),
            'docentes' => $this->docentes->listar(),
            'miDocente' => $miDocente,
            'periodoActivo' => PeriodoAcademico::where('estado', 'ACTIVO')->first(),
        ]);
    }

    /** Vista agregada de portafolios de todos los docentes, para revision. */
    public function index(Request $request): JsonResponse
    {
        $documentos = $this->documentos->listar(
            null,
            $request->query('id_curso') ? (int) $request->query('id_curso') : null,
            $request->query('tipo'),
            $request->query('id_docente') ? (int) $request->query('id_docente') : null,
            $request->query('estado'),
        );

        return response()->json(['ok' => true, 'documentos' => $documentos]);
    }

    /** @return array{0: Docente, 1: Curso} */
    private function miDocenteDeCurso(int $idCurso): array
    {
        $miDocente = auth()->user()->miDocentePropio();
        abort_if(! $miDocente, 403, 'Su cuenta no tiene un perfil docente asociado.');

        $curso = Curso::find($idCurso);
        abort_if(! $curso || $curso->id_docente !== $miDocente->id_docente, 404, 'El curso no existe o no te pertenece.');

        return [$miDocente, $curso];
    }

    public function estudiantesNotas(Request $request): JsonResponse
    {
        $idCurso = (int) $request->query('id_curso');
        $unidad = (string) $request->query('unidad', 'I');
        $this->miDocenteDeCurso($idCurso);

        return response()->json(['ok' => true, 'estudiantes' => $this->notas->estudiantesDeCurso($idCurso, $unidad)]);
    }

    public function guardarNota(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'id_matricula_curso' => ['required', 'integer', 'exists:matricula_cursos,id_matricula_curso'],
            'unidad' => ['required', 'string', 'in:I,II,III,IV,V,VI'],
            'practica' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'teoria' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'examen' => ['nullable', 'numeric', 'min:0', 'max:20'],
        ]);

        $matriculaCurso = MatriculaCurso::findOrFail($datos['id_matricula_curso']);
        $this->miDocenteDeCurso($matriculaCurso->id_curso);

        $nota = $this->notas->guardarNota(
            $datos['id_matricula_curso'],
            $datos['unidad'],
            $datos['practica'] ?? null,
            $datos['teoria'] ?? null,
            $datos['examen'] ?? null,
        );

        return response()->json(['ok' => true, 'nota' => $nota]);
    }

    public function sesionesAsistencia(Request $request): JsonResponse
    {
        $idCurso = (int) $request->query('id_curso');
        [$miDocente] = $this->miDocenteDeCurso($idCurso);

        return response()->json(['ok' => true, 'sesiones' => $this->asistencia->sesionesDeCurso($idCurso, $miDocente->id_docente)]);
    }

    public function crearSesionAsistencia(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'id_curso' => ['required', 'integer'],
            'fecha_sesion' => ['required', 'date'],
            'tema' => ['nullable', 'string', 'max:180'],
        ]);
        [$miDocente] = $this->miDocenteDeCurso($datos['id_curso']);

        $sesion = $this->asistencia->crearSesion($datos['id_curso'], $miDocente->id_docente, $datos['fecha_sesion'], $datos['tema'] ?? null);

        return response()->json(['ok' => true, 'sesion' => $sesion], 201);
    }

    public function estudiantesAsistencia(Request $request): JsonResponse
    {
        $idCurso = (int) $request->query('id_curso');
        $idSesion = (int) $request->query('id_sesion');
        $this->miDocenteDeCurso($idCurso);

        return response()->json(['ok' => true, 'estudiantes' => $this->asistencia->estudiantesDeSesion($idCurso, $idSesion)]);
    }

    public function guardarAsistencia(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'id_sesion' => ['required', 'integer', 'exists:asistencia_sesiones,id_sesion'],
            'registros' => ['required', 'array'],
            'registros.*.id_estudiante' => ['required', 'integer'],
            'registros.*.estado' => ['required', 'string', 'in:PRESENTE,TARDANZA,AUSENTE,JUSTIFICADO'],
        ]);

        $sesion = AsistenciaSesion::findOrFail($datos['id_sesion']);
        $this->miDocenteDeCurso($sesion->id_curso);

        $this->asistencia->guardarAsistencia($datos['id_sesion'], $datos['registros']);

        return response()->json(['ok' => true]);
    }
}
