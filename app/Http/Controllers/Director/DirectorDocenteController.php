<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Services\Academic\DocenteService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DirectorDocenteController extends Controller
{
    public function __construct(private readonly DocenteService $docentes) {}

    public function page(): View
    {
        return view('director.docentes.index');
    }

    public function index(): JsonResponse
    {
        return response()->json(['ok' => true, 'docentes' => $this->docentes->listarConCarga()]);
    }
}
