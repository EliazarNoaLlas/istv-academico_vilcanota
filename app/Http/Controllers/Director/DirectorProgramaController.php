<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Services\Academic\ProgramaService;
use Illuminate\Http\JsonResponse;

class DirectorProgramaController extends Controller
{
    public function __construct(private readonly ProgramaService $programas) {}

    public function index(): JsonResponse
    {
        return response()->json(['ok' => true, 'programas' => $this->programas->listarConResumen()]);
    }
}
