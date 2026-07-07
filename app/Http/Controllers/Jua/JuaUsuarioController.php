<?php

namespace App\Http\Controllers\Jua;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

/** Solo lectura: JUA consulta las cuentas reales del sistema (la gestion completa sigue siendo de Director). */
class JuaUsuarioController extends Controller
{
    public function index(): View
    {
        $usuarios = User::with(['rol', 'programa'])
            ->orderBy('nombres')
            ->get();

        return view('jua.usuarios.index', ['usuarios' => $usuarios]);
    }
}
