<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Docente\CerrarActaRequest;
use App\Http\Requests\Docente\GuardarNotaRequest;
use App\Services\Docente\DocentePortalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocenteNotaController extends Controller
{
    public function __construct(private DocentePortalService $portal) {}

    public function page(): View
    {
        return view('docente.notas.index');
    }

    /** Estudiantes matriculados y notas reales de la unidad, solo si el curso pertenece al docente. */
    public function data(Request $request): JsonResponse
    {
        $docente = $this->portal->getDocenteActual($request->user());
        $idCurso = (int) $request->query('id_curso');

        abort_if(! $idCurso, 422, 'Debes indicar un curso.');

        $unidad = (string) $request->query('unidad', 'I');
        $curso = $this->portal->verificarCursoPerteneceAlDocente($docente, $idCurso);

        return response()->json([
            'ok' => true,
            'curso' => ['id_curso' => $curso->id_curso, 'nombre_curso' => $curso->nombre_curso],
            'unidad' => $unidad,
        ] + $this->portal->getEstudiantesPorCurso($curso, $unidad));
    }

    public function guardar(GuardarNotaRequest $request): JsonResponse
    {
        $docente = $this->portal->getDocenteActual($request->user());
        $curso = $this->portal->verificarCursoPerteneceAlDocente($docente, (int) $request->validated('id_curso'));

        $this->portal->guardarNotas($curso, $request->validated('unidad'), $request->validated('notas'));

        return response()->json(['ok' => true]);
    }

    public function cerrarActa(CerrarActaRequest $request): JsonResponse
    {
        $docente = $this->portal->getDocenteActual($request->user());
        $curso = $this->portal->verificarCursoPerteneceAlDocente($docente, (int) $request->validated('id_curso'));

        $actualizadas = $this->portal->cerrarActa($curso, $request->validated('unidad'));

        return response()->json(['ok' => true, 'actualizadas' => $actualizadas]);
    }
}
