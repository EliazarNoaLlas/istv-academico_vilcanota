<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Http\Requests\Director\GestionarAlertaRequest;
use App\Models\AlertaAcademica;
use App\Services\Director\AlertaAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DirectorAlertaController extends Controller
{
    public function __construct(private readonly AlertaAdminService $alertas) {}

    public function page(): View
    {
        return view('director.alertas.index');
    }

    public function index(Request $request): JsonResponse
    {
        $alertas = $this->alertas->listar(
            $request->query('estado'),
            $request->query('severidad'),
        );

        return response()->json(['ok' => true, 'alertas' => $alertas]);
    }

    public function gestionar(GestionarAlertaRequest $request, AlertaAcademica $alerta): JsonResponse
    {
        $alerta = $this->alertas->gestionar($alerta, $request->validated('estado'), $request->user());

        return response()->json(['ok' => true, 'alerta' => $alerta]);
    }
}
