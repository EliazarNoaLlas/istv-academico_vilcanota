<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\PeriodoAcademico;
use App\Models\Scopes\CoordinadorProgramaDirectoScope;
use App\Services\Academic\CursoService;
use App\Services\Academic\DocenteService;
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
}
