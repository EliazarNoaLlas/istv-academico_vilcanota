<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cursos\StoreCursoRequest;
use App\Http\Requests\Cursos\UpdateCursoRequest;
use App\Models\Curso;
use App\Models\ProgramaEstudio;
use App\Services\Academic\CursoService;
use App\Services\Academic\DocenteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoordinadorCursoController extends Controller
{
    public function __construct(
        private CursoService $cursos,
        private DocenteService $docentes,
    ) {}

    public function page(): View
    {
        return view('coordinador.cursos.index', [
            'docentes' => $this->docentes->listar(),
            'programas' => ProgramaEstudio::where('id_programa', auth()->user()->id_programa)->get(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $cursos = $this->cursos->listar(
            $request->query('semestre'),
            null,
            $request->query('q'),
            $request->query('modulo'),
            $request->query('id_programa') ? (int) $request->query('id_programa') : null,
        );

        return response()->json(['ok' => true, 'cursos' => $cursos]);
    }

    public function store(StoreCursoRequest $request): JsonResponse
    {
        // El programa nunca se toma del cliente: un coordinador solo puede
        // crear cursos dentro de su propio programa asignado.
        $datos = [...$request->validated(), 'id_programa' => auth()->user()->id_programa];

        $curso = $this->cursos->crear($datos);

        return response()->json(['ok' => true, 'curso' => $curso], 201);
    }

    public function update(UpdateCursoRequest $request, Curso $curso): JsonResponse
    {
        // El scope global ya garantiza que $curso pertenece a su programa;
        // se descarta ademas cualquier intento de mover el curso a otro.
        $datos = collect($request->validated())->except('id_programa')->all();

        $curso = $this->cursos->actualizar($curso, $datos);

        return response()->json(['ok' => true, 'curso' => $curso]);
    }
}
