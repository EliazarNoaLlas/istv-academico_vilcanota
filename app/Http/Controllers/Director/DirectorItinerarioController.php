<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Http\Requests\Director\StoreItinerarioRequest;
use App\Http\Requests\Director\UpdateItinerarioRequest;
use App\Http\Requests\Director\UpdateItinerarioUnidadRequest;
use App\Models\ItinerarioFormativo;
use App\Models\ItinerarioUnidadDidactica;
use App\Models\ProgramaEstudio;
use App\Services\Academic\ItinerarioCalculoService;
use App\Services\Academic\ItinerarioExportService;
use App\Services\Academic\ItinerarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DirectorItinerarioController extends Controller
{
    public function __construct(
        private readonly ItinerarioService $itinerarios,
        private readonly ItinerarioCalculoService $calculo,
        private readonly ItinerarioExportService $exportador,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'id_programa', 'estado', 'version']);
        $data = $this->itinerarios->listarParaDirector($filters);

        return view('director.itinerarios.index', [
            'itinerarios' => $data['itinerarios'],
            'kpis' => $data['kpis'],
            'versiones' => $data['versiones'],
            'programas' => ProgramaEstudio::orderBy('nombre')->get(),
            'filters' => $filters,
        ]);
    }

    public function show(ItinerarioFormativo $itinerario): View
    {
        $detalle = $this->itinerarios->obtenerDetalle($itinerario->id_itinerario);

        return view('director.itinerarios.show', [
            'itinerario' => $detalle,
            'validaciones' => $this->calculo->validarTotales($detalle),
        ]);
    }

    public function edit(ItinerarioFormativo $itinerario): View
    {
        $data = $this->itinerarios->obtenerParaEditor($itinerario->id_itinerario);

        return view('director.itinerarios.edit', [
            'itinerario' => $data['itinerario'],
            'bloques' => $data['bloques'],
            'validaciones' => $data['validaciones'],
        ]);
    }

    public function store(StoreItinerarioRequest $request): RedirectResponse
    {
        $itinerario = $this->itinerarios->crearItinerario($request->validated());

        return redirect()
            ->route('director.itinerarios.show', $itinerario)
            ->with('status', 'Itinerario formativo creado correctamente.');
    }

    public function update(UpdateItinerarioRequest $request, ItinerarioFormativo $itinerario): RedirectResponse
    {
        $this->itinerarios->actualizarItinerario($itinerario, $request->validated());

        return back()->with('status', 'Itinerario formativo actualizado correctamente.');
    }

    public function updateUnidad(
        UpdateItinerarioUnidadRequest $request,
        ItinerarioFormativo $itinerario,
        ItinerarioUnidadDidactica $unidad,
    ): JsonResponse|RedirectResponse {
        $resultado = $this->itinerarios->actualizarUnidad($itinerario, $unidad, $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Unidad didáctica actualizada correctamente',
                'unidad' => $resultado['unidad'],
                'totales' => $resultado['totales'],
                'validaciones' => $resultado['validaciones'],
            ]);
        }

        return back()->with('status', 'Unidad didáctica actualizada correctamente.');
    }

    public function validarTotales(ItinerarioFormativo $itinerario): JsonResponse
    {
        $validaciones = $this->calculo->validarTotales($itinerario);

        return response()->json([
            'success' => true,
            'alertas' => count($validaciones),
            'validaciones' => $validaciones,
        ]);
    }

    public function recalcularTotales(ItinerarioFormativo $itinerario): JsonResponse
    {
        $totales = $this->calculo->recalcularTotales($itinerario);

        return response()->json([
            'success' => true,
            'message' => 'Totales recalculados correctamente',
            'totales' => $totales,
            'validaciones' => $this->calculo->validarTotales($itinerario->refresh()),
        ]);
    }

    public function duplicar(ItinerarioFormativo $itinerario): RedirectResponse
    {
        $copia = $this->itinerarios->duplicarItinerario($itinerario);

        return redirect()
            ->route('director.itinerarios.edit', $copia)
            ->with('status', "Itinerario duplicado como versión {$copia->version} (borrador).");
    }

    public function activar(ItinerarioFormativo $itinerario): RedirectResponse
    {
        $this->itinerarios->activarItinerario($itinerario);

        return back()->with('status', 'Itinerario activado. Las demás versiones activas del programa fueron archivadas.');
    }

    public function archivar(ItinerarioFormativo $itinerario): RedirectResponse
    {
        $this->itinerarios->archivarItinerario($itinerario);

        return back()->with('status', 'Itinerario archivado correctamente.');
    }

    public function exportExcel(ItinerarioFormativo $itinerario): Response
    {
        return $this->exportador->exportExcel($itinerario);
    }

    public function exportPdf(Request $request, ItinerarioFormativo $itinerario): Response
    {
        return $this->exportador->exportPdf($itinerario, $request->user());
    }
}
