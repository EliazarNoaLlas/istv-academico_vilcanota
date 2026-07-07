<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DocenteAnaliticaController extends Controller
{
    public function page(): View
    {
        return view('docente.analitica.index');
    }
}
