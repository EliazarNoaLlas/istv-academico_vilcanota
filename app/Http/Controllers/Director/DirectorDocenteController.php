<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Http\Requests\Docentes\AsignarCursosRequest;
use App\Models\Docente;
use App\Models\ProgramaEstudio;
use App\Services\Academic\DocenteExportService;
use App\Services\Academic\DocenteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DirectorDocenteController extends Controller
{
    public function __construct(
        private readonly DocenteService $docentes,
        private readonly DocenteExportService $exportador,
    ) {}

    public function page(): View
    {
        return view('director.docentes.index', [
            'programas' => ProgramaEstudio::orderBy('nombre')->get(),
        ]);
    }

    public function index(): JsonResponse
    {
        return response()->json(['ok' => true, 'docentes' => $this->docentes->listarConCarga()]);
    }

    public function detalle(Docente $docente): JsonResponse
    {
        return response()->json(['ok' => true, ...$this->docentes->detalle($docente)]);
    }

    public function cursosDisponibles(Docente $docente): JsonResponse
    {
        return response()->json(['ok' => true, ...$this->docentes->cursosDisponibles($docente)]);
    }

    public function asignarCursos(AsignarCursosRequest $request, Docente $docente): JsonResponse
    {
        $cursos = $this->docentes->asignarCursos($docente, $request->validated('cursos'));

        return response()->json(['ok' => true, 'cursos' => $cursos]);
    }

    public function exportExcel(): StreamedResponse
    {
        return $this->exportador->exportExcel();
    }

    public function exportPdf(Request $request): Response
    {
        return $this->exportador->exportPdf($request->user());
    }
}
