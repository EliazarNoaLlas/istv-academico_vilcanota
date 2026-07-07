<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\SesionAprendizaje;
use App\Services\Academic\AsistenciaService;
use App\Services\Academic\NotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Revision de Notas/Asistencia/Sesiones de OTROS docentes (no el propio) desde "Revisar portafolios".
 * Curso::findOrFail() ya viene acotado al programa del coordinador via CoordinadorProgramaDirectoScope,
 * asi que basta con que el curso exista para saber que pertenece a su programa.
 */
class CoordinadorRevisionController extends Controller
{
    public function __construct(
        private readonly NotaService $notas,
        private readonly AsistenciaService $asistencia,
    ) {}

    public function sesiones(Request $request): JsonResponse
    {
        $curso = Curso::findOrFail((int) $request->query('id_curso'));

        $sesiones = SesionAprendizaje::where('id_curso', $curso->id_curso)
            ->orderByDesc('fecha_subida')
            ->get();

        return response()->json(['ok' => true, 'sesiones' => $sesiones]);
    }

    public function validarSesion(Request $request, SesionAprendizaje $sesion): JsonResponse
    {
        $datos = $request->validate([
            'estado' => ['required', 'string', Rule::in(['APROBADO', 'RECHAZADO'])],
        ]);

        $sesion->update(['estado' => $datos['estado']]);

        return response()->json(['ok' => true, 'sesion' => $sesion]);
    }

    public function fechasAsistencia(Request $request): JsonResponse
    {
        $curso = Curso::findOrFail((int) $request->query('id_curso'));

        return response()->json(['ok' => true, 'fechas' => $this->asistencia->fechasConSesion($curso, $curso->id_docente)]);
    }

    public function asistencia(Request $request): JsonResponse
    {
        $curso = Curso::findOrFail((int) $request->query('id_curso'));
        $fecha = (string) $request->query('fecha', now()->toDateString());

        return response()->json(['ok' => true, 'fecha' => $fecha] + $this->asistencia->estudiantesPorFecha($curso, $curso->id_docente, $fecha));
    }

    public function notas(Request $request): JsonResponse
    {
        $curso = Curso::findOrFail((int) $request->query('id_curso'));
        $unidad = (string) $request->query('unidad', 'I');

        return response()->json(['ok' => true] + $this->notas->estudiantesDeCurso($curso, $unidad));
    }

    /** El coordinador puede editar directamente las notas de cualquier docente de su programa. */
    public function guardarNotas(Request $request): JsonResponse
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
        $curso = Curso::findOrFail($datos['id_curso']);

        $this->notas->guardarNotasLote($curso, $datos['unidad'], $datos['filas']);

        return response()->json(['ok' => true]);
    }

    /** Reabre el acta (ABIERTO) para que el docente pueda modificar notas que ya habia cerrado. */
    public function reabrirNotas(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'id_curso' => ['required', 'integer'],
            'unidad' => ['required', 'string', 'in:I,II,III'],
        ]);
        $curso = Curso::findOrFail($datos['id_curso']);

        $actualizadas = $this->notas->reabrirActa($curso, $datos['unidad']);

        return response()->json(['ok' => true, 'actualizadas' => $actualizadas]);
    }
}
