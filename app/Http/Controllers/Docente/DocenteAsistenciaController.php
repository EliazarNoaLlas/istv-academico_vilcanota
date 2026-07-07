<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Docente\GuardarAsistenciaRequest;
use App\Services\Docente\DocentePortalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocenteAsistenciaController extends Controller
{
    public function __construct(private DocentePortalService $portal) {}

    public function page(): View
    {
        return view('docente.asistencia.index');
    }

    /** Estudiantes matriculados y asistencia real de la fecha, solo si el curso pertenece al docente. */
    public function data(Request $request): JsonResponse
    {
        $docente = $this->portal->getDocenteActual($request->user());
        $idCurso = (int) $request->query('id_curso');

        abort_if(! $idCurso, 422, 'Debes indicar un curso.');

        $fecha = (string) $request->query('fecha', now()->toDateString());
        $curso = $this->portal->verificarCursoPerteneceAlDocente($docente, $idCurso);

        return response()->json([
            'ok' => true,
            'curso' => ['id_curso' => $curso->id_curso, 'nombre_curso' => $curso->nombre_curso],
            'fecha' => $fecha,
        ] + $this->portal->getAsistenciaPorCursoFecha($curso, $fecha));
    }

    public function guardar(GuardarAsistenciaRequest $request): JsonResponse
    {
        $docente = $this->portal->getDocenteActual($request->user());
        $curso = $this->portal->verificarCursoPerteneceAlDocente($docente, (int) $request->validated('id_curso'));

        $this->portal->guardarAsistencia(
            $curso,
            $request->validated('fecha'),
            $request->validated('tema'),
            $request->validated('registros'),
        );

        return response()->json(['ok' => true]);
    }
}
