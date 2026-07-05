<?php

namespace App\Http\Controllers\Portafolios;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portafolios\ValidarPortafolioRequest;
use App\Models\PortafolioDocumento;
use App\Services\Portafolios\PortafolioReviewService;
use Illuminate\Http\JsonResponse;

class PortafolioRevisionController extends Controller
{
    public function __construct(private PortafolioReviewService $revision) {}

    public function validar(ValidarPortafolioRequest $request, PortafolioDocumento $documento): JsonResponse
    {
        $documento = $this->revision->validar(
            $documento,
            $request->validated('estado'),
            $request->validated('observacion'),
        );

        return response()->json(['ok' => true, 'documento' => $documento]);
    }
}
