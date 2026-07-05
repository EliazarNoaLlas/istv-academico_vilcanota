<?php

namespace App\Http\Controllers\Jua;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class JuaDashboardController extends Controller
{
    public function index(): View
    {
        return view('jua.dashboard.index');
    }
}
