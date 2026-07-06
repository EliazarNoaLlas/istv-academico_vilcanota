<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Los docentes no tienen id_programa propio: se vinculan via la tabla pivote
 * docente_programa (relacion programas() en el modelo Docente). Se califica
 * la columna con el nombre de tabla porque tanto programas_estudio como el
 * pivote tienen una columna id_programa.
 */
class CoordinadorDocenteProgramaScope implements Scope
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

        $builder->whereHas('programas', fn ($q) => $q->where('programas_estudio.id_programa', $idPrograma));
    }
}
