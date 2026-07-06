<?php

namespace App\Models\Scopes;

use Illuminate\Support\Facades\Auth;

/**
 * Punto unico de verdad para el aislamiento por programa de estudio del
 * coordinador. Un coordinador sin id_programa asignado nunca debe ver filas
 * (en vez de ver "todo" por defecto), por eso idPrograma() y aplica() se
 * consultan siempre juntos desde los scopes.
 */
class CoordinadorScopeHelper
{
    public static function aplica(): bool
    {
        $usuario = Auth::user();

        return (bool) $usuario && $usuario->rol?->codigo === 'coordinador';
    }

    public static function idPrograma(): ?int
    {
        return Auth::user()?->id_programa;
    }
}
