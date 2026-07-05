<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Services\Academic\DocenteService;
use Illuminate\Http\JsonResponse;

class DocenteController extends Controller
{
    public function __construct(private DocenteService $docentes) {}

    public function index(): JsonResponse
    {
        return response()->json(['ok' => true, 'docentes' => $this->docentes->listar()]);
    }
}
