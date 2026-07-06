<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Para modelos sin id_programa propio pero que cuelgan de un curso o
 * estudiante ya restringido (notas, portafolios, alertas, sesiones...).
 * whereHas() construye la subconsulta con el propio query builder del
 * modelo relacionado, que ya trae su global scope aplicado: no hace falta
 * repetir la condicion de programa aqui.
 */
class CoordinadorProgramaViaRelacionScope implements Scope
{
    public function __construct(private readonly string $relacion) {}

    public function apply(Builder $builder, Model $model): void
    {
        if (! CoordinadorScopeHelper::aplica()) {
            return;
        }

        if (CoordinadorScopeHelper::idPrograma() === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->whereHas($this->relacion);
    }
}
