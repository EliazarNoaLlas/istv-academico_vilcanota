<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\PeriodoAcademico;
use App\Services\Docente\DocentePortalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocenteHorarioController extends Controller
{
    public function __construct(private DocentePortalService $portal) {}

    public function page(): View
    {
        return view('docente.horario.index');
    }

    /** Horario real del docente (horarios.id_docente). Sin datos, no se inventan clases. */
    public function data(Request $request): JsonResponse
    {
        $docente = $this->portal->getDocenteActual($request->user());

        $idPeriodo = $request->query('id_periodo') ? (int) $request->query('id_periodo') : null;
        $periodo = $idPeriodo ? PeriodoAcademico::find($idPeriodo) : $this->portal->getPeriodoActivo();

        return response()->json([
            'ok' => true,
            'periodos' => PeriodoAcademico::orderByDesc('id_periodo')->get(['id_periodo', 'codigo', 'nombre', 'estado']),
            'periodo_seleccionado' => $periodo ? ['id_periodo' => $periodo->id_periodo, 'codigo' => $periodo->codigo, 'nombre' => $periodo->nombre] : null,
            'horario' => $this->portal->getHorarioSemanal($docente, $periodo),
        ]);
    }
}
