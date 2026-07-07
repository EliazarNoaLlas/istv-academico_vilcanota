<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Docente;
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

    /** Estudiantes del curso (segun su semestre) con la nota del parcial indicado y el resumen de la clase. */
    public function estudiantesNotas(Request $request): JsonResponse
    {
        $idCurso = (int) $request->query('id_curso');
        $unidad = (string) $request->query('unidad', 'I');
        [, $curso] = $this->miDocenteDeCurso($idCurso);

        return response()->json(['ok' => true] + $this->notas->estudiantesDeCurso($curso, $unidad));
    }

    /** Guarda todas las filas del registro de notas en una sola peticion/transaccion (no una por estudiante). */
    public function guardarNotasLote(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'id_curso' => ['required', 'integer'],
            'unidad' => ['required', 'string', 'in:I,II,III'],
            'filas' => ['required', 'array', 'min:1'],
            'filas.*.id_matricula_curso' => ['required', 'integer', 'exists:matricula_cursos,id_matricula_curso'],
            'filas.*.practica' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'filas.*.teoria' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'filas.*.examen' => ['nullable', 'numeric', 'min:0', 'max:20'],
        ]);
        [, $curso] = $this->miDocenteDeCurso($datos['id_curso']);

        $this->notas->guardarNotasLote($curso, $datos['unidad'], $datos['filas']);

        return response()->json(['ok' => true]);
    }

    /** Estudiantes del curso (segun su semestre) y asistencia real de la fecha, con resumen y alerta de asistencia historica baja. */
    public function asistenciaPorFecha(Request $request): JsonResponse
    {
        $idCurso = (int) $request->query('id_curso');
        $fecha = (string) $request->query('fecha', now()->toDateString());
        [$miDocente, $curso] = $this->miDocenteDeCurso($idCurso);

        return response()->json(['ok' => true, 'fecha' => $fecha] + $this->asistencia->estudiantesPorFecha($curso, $miDocente->id_docente, $fecha));
    }

    public function guardarAsistencia(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'id_curso' => ['required', 'integer'],
            'fecha' => ['required', 'date'],
            'registros' => ['required', 'array', 'min:1'],
            'registros.*.id_estudiante' => ['required', 'integer'],
            'registros.*.estado' => ['required', 'string', 'in:PRESENTE,TARDANZA,AUSENTE,JUSTIFICADO'],
        ]);
        [$miDocente, $curso] = $this->miDocenteDeCurso($datos['id_curso']);

        $this->asistencia->guardarAsistencia($curso, $miDocente->id_docente, $datos['fecha'], $datos['registros']);

        return response()->json(['ok' => true]);
    }
}
