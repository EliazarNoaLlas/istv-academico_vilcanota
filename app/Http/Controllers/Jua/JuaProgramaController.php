<?php

namespace App\Http\Controllers\Jua;

use App\Http\Controllers\Controller;
use App\Models\ProgramaEstudio;
use Illuminate\View\View;

/** Solo lectura: JUA consulta los programas de estudio reales del instituto. */
class JuaProgramaController extends Controller
{
    public function index(): View
    {
        $programas = ProgramaEstudio::withCount(['cursos', 'estudiantes', 'docentes'])
            ->with(['itinerarios' => fn ($q) => $q->where('estado', 'ACTIVO')])
            ->orderBy('nombre')
            ->get();

        return view('jua.programas.index', ['programas' => $programas]);
    }
}
