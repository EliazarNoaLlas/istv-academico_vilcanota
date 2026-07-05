<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Autoriza por rol (director, jua, coordinador, docente). No implementa
     * permisos granulares: eso se activa en una fase posterior cuando los
     * modulos ya esten migrados.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (! $usuario || ! in_array($usuario->rol?->codigo, $roles, true)) {
            abort(403, 'No tienes permiso para acceder a este modulo.');
        }

        return $next($request);
    }
}
