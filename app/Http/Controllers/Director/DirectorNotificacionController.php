<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DirectorNotificacionController extends Controller
{
    public function page(): View
    {
        return view('director.notificaciones.index');
    }
}
