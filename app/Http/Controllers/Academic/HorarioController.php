<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Horarios\StoreHorarioRequest;
use App\Services\Horarios\HorarioPersistenceService;
use App\Services\Horarios\HorarioQueryService;
use App\Services\Horarios\HorarioValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    public function __construct(
        private HorarioQueryService $consultas,
        private HorarioValidationService $validacion,
        private HorarioPersistenceService $persistencia,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $horarios = $this->consultas->listar(
            $request->query('id_docente') ? (int) $request->query('id_docente') : null,
            $request->query('id_curso') ? (int) $request->query('id_curso') : null,
        );

        return response()->json(['ok' => true, 'horarios' => $horarios]);
    }

    public function store(StoreHorarioRequest $request): JsonResponse
    {
        $bloques = $request->validated('horarios');

        $conflictos = $this->validacion->validarConflictos($bloques);

        if ($conflictos !== []) {
            return response()->json(['ok' => false, 'conflictos' => $conflictos], 422);
        }

        $this->persistencia->guardar($bloques);

        return response()->json(['ok' => true]);
    }
}
