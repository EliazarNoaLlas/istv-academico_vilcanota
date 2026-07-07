<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Services\Docente\DocentePortalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocenteDashboardController extends Controller
{
    public function __construct(private DocentePortalService $portal) {}

    public function index(): View
    {
        return view('docente.dashboard.index');
    }

    public function data(Request $request): JsonResponse
    {
        $docente = $this->portal->getDocenteActual($request->user());

        return response()->json(['ok' => true] + $this->portal->getDashboardData($docente));
    }
}
