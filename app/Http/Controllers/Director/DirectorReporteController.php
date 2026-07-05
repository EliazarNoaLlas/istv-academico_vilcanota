<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Http\Requests\Director\GenerateReporteRequest;
use App\Models\ReporteGenerado;
use App\Services\Director\ReporteGeneradorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DirectorReporteController extends Controller
{
    public function __construct(private readonly ReporteGeneradorService $generador) {}

    public function page(): View
    {
        return view('director.reportes.index');
    }

    public function index(): JsonResponse
    {
        $reportes = ReporteGenerado::with('usuario')->orderByDesc('fecha_generacion')->limit(30)->get();

        return response()->json(['ok' => true, 'reportes' => $reportes]);
    }

    public function generar(GenerateReporteRequest $request): JsonResponse
    {
        $reporte = $this->generador->generar(
            $request->validated('tipo'),
            $request->validated('formato'),
            $request->user(),
        );

        return response()->json(['ok' => true, 'reporte' => $reporte], 201);
    }

    public function descargar(ReporteGenerado $reporte): StreamedResponse
    {
        abort_unless($reporte->archivo && Storage::disk('local')->exists($reporte->archivo), 404);

        return Storage::disk('local')->download($reporte->archivo, basename($reporte->archivo));
    }
}
