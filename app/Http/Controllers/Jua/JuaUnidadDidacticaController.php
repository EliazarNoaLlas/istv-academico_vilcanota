<?php

namespace App\Http\Controllers\Jua;

use App\Http\Controllers\Controller;
use App\Models\ItinerarioUnidadDidactica;
use App\Models\ProgramaEstudio;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Solo lectura: JUA consulta las unidades didacticas reales de los itinerarios formativos. */
class JuaUnidadDidacticaController extends Controller
{
    public function index(Request $request): View
    {
        $idPrograma = $request->query('id_programa') ? (int) $request->query('id_programa') : null;

        $unidades = ItinerarioUnidadDidactica::query()
            ->with(['curso', 'bloque.modulo.itinerario.programa'])
            ->whereHas('bloque.modulo.itinerario', function ($q) use ($idPrograma) {
                $q->where('estado', 'ACTIVO');
                $q->when($idPrograma, fn ($qp) => $qp->where('id_programa', $idPrograma));
            })
            ->orderBy('ciclo')
            ->orderBy('orden')
            ->get();

        return view('jua.unidades.index', [
            'unidades' => $unidades,
            'programas' => ProgramaEstudio::orderBy('nombre')->get(),
            'idProgramaActivo' => $idPrograma,
        ]);
    }
}
