<?php

namespace App\Http\Controllers\Portafolios;

use App\Http\Controllers\Controller;
use App\Models\PortafolioDocumento;
use App\Services\Portafolios\PortafolioIAService;
use Illuminate\Http\JsonResponse;

class PortafolioIAController extends Controller
{
    public function __construct(private PortafolioIAService $ia) {}

    public function validar(PortafolioDocumento $documento): JsonResponse
    {
        return response()->json($this->ia->analizar($documento));
    }

    public function analizar(PortafolioDocumento $documento): JsonResponse
    {
        return response()->json($this->ia->analizar($documento));
    }
}
