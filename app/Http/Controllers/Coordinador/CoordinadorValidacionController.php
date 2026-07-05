<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Services\Academic\ValidacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CoordinadorValidacionController extends Controller
{
    public function __construct(private ValidacionService $validaciones) {}

    public function page(): View
    {
        return view('coordinador.validaciones.index');
    }

    public function index(): JsonResponse
    {
        return response()->json(['ok' => true, 'pendientes' => $this->validaciones->pendientes()]);
    }
}
