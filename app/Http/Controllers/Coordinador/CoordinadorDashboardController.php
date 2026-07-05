<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class CoordinadorDashboardController extends Controller
{
    public function index(): View
    {
        return view('coordinador.dashboard.index');
    }
}
