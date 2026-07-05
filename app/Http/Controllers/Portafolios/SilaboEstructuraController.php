<?php

namespace App\Http\Controllers\Portafolios;

use App\Http\Controllers\Controller;
use App\Models\SilaboEstructura;
use Illuminate\Http\JsonResponse;

class SilaboEstructuraController extends Controller
{
    /**
     * Reemplaza a silabo_estructura.php: la rubrica vive en BD (sembrada por
     * silabo_estructura_institucional.sql), no se siembra desde este endpoint.
     */
    public function index(): JsonResponse
    {
        $estructura = SilaboEstructura::where('activo', true)
            ->with('criterios')
            ->latest('id_estructura')
            ->first();

        if (! $estructura) {
            return response()->json(['ok' => false, 'mensaje' => 'No hay una estructura de silabo activa.'], 404);
        }

        return response()->json(['ok' => true, 'estructura' => $estructura]);
    }
}
