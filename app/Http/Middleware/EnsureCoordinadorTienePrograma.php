<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloquea por completo al coordinador que aun no tiene un programa de
 * estudio asignado, en vez de dejarlo entrar y que los scopes globales le
 * devuelvan listas vacias en todo el panel sin explicacion.
 */
class EnsureCoordinadorTienePrograma
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if ($usuario && $usuario->rol?->codigo === 'coordinador' && $usuario->id_programa === null) {
            abort(403, 'Su cuenta de coordinador aún no tiene un programa de estudios asignado. Contacte a Dirección Académica.');
        }

        return $next($request);
    }
}
