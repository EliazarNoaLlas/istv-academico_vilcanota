<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DocenteAsistenciaController extends Controller
{
    public function page(): View
    {
        return view('docente.asistencia.index');
    }
}
