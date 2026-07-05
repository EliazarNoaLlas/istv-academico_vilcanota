<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Services\Academic\DocenteService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CoordinadorDocenteController extends Controller
{
    public function __construct(private DocenteService $docentes) {}

    public function page(): View
    {
        return view('coordinador.docentes.index');
    }

    public function index(): JsonResponse
    {
        return response()->json(['ok' => true, 'docentes' => $this->docentes->listarConCarga()]);
    }
}
