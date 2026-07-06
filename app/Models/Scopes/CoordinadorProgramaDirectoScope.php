<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/** Para modelos con columna id_programa propia (cursos, estudiantes, horarios). */
class CoordinadorProgramaDirectoScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! CoordinadorScopeHelper::aplica()) {
            return;
        }

        $idPrograma = CoordinadorScopeHelper::idPrograma();

        if ($idPrograma === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where($model->getTable().'.id_programa', $idPrograma);
    }
}
