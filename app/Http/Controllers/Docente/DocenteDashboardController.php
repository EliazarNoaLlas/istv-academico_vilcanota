<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DocenteDashboardController extends Controller
{
    public function index(): View
    {
        return view('docente.dashboard.index');
    }
}
