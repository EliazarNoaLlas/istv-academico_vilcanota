<?php

namespace App\Http\Controllers\Jua;

use App\Http\Controllers\Controller;
use App\Models\CalendarioEvento;
use App\Services\Academic\CalendarioAcademicoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JuaCalendarioController extends Controller
{
    public function __construct(private readonly CalendarioAcademicoService $calendario) {}

    public function page(): View
    {
        $hoy = now();

        return view('jua.calendario.index', [
            'anio' => (int) $hoy->format('Y'),
            'mes' => (int) $hoy->format('n'),
            'kpis' => $this->calendario->kpis(),
            'proximos' => $this->calendario->proximosEventos(),
            'tipos' => CalendarioAcademicoService::TIPOS,
        ]);
    }

    public function eventos(Request $request): JsonResponse
    {
        $anio = (int) $request->query('anio', now()->format('Y'));
        $mes = (int) $request->query('mes', now()->format('n'));

        return response()->json([
            'ok' => true,
            'eventos' => $this->calendario->listarPorMes($anio, $mes),
            'kpis' => $this->calendario->kpis(),
            'proximos' => $this->calendario->proximosEventos(),
        ]);
    }

    private function reglas(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:180'],
            'tipo' => ['required', 'string', Rule::in(array_keys(CalendarioAcademicoService::TIPOS))],
            'fecha' => ['required', 'date'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate($this->reglas());
        $evento = $this->calendario->crear($datos, $request->user()->id_usuario);

        return response()->json(['ok' => true, 'evento' => $evento], 201);
    }

    public function update(Request $request, CalendarioEvento $evento): JsonResponse
    {
        $datos = $request->validate($this->reglas());
        $evento = $this->calendario->actualizar($evento, $datos);

        return response()->json(['ok' => true, 'evento' => $evento]);
    }

    public function destroy(CalendarioEvento $evento): JsonResponse
    {
        $this->calendario->eliminar($evento);

        return response()->json(['ok' => true]);
    }
}
