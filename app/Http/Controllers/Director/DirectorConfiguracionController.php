<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Http\Requests\Director\UpdateConfiguracionRequest;
use App\Services\Director\ConfiguracionAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DirectorConfiguracionController extends Controller
{
    public function __construct(private readonly ConfiguracionAdminService $configuracion) {}

    public function page(): View
    {
        return view('director.configuracion.index');
    }

    public function index(): JsonResponse
    {
        return response()->json(['ok' => true, 'configuracion' => $this->configuracion->listar()]);
    }

    public function update(UpdateConfiguracionRequest $request): JsonResponse
    {
        $configuracion = $this->configuracion->actualizar($request->validated('valores'), $request->user());

        return response()->json(['ok' => true, 'configuracion' => $configuracion]);
    }
}
