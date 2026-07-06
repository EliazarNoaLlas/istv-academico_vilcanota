<?php

namespace App\Models;

use App\Models\Scopes\CoordinadorScopeHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertaAcademica extends Model
{
    protected $table = 'alertas_academicas';
    protected $primaryKey = 'id_alerta';
    public $timestamps = false;

    /**
     * id_curso e id_estudiante son ambos nullable aqui, asi que a diferencia
     * de otros modelos "via curso" se exige coincidencia por CUALQUIERA de
     * los dos (no se puede confiar en que uno solo siempre este presente).
     */
    protected static function booted(): void
    {
        static::addGlobalScope('coordinador_programa', function (Builder $builder) {
            if (! CoordinadorScopeHelper::aplica()) {
                return;
            }

            $idPrograma = CoordinadorScopeHelper::idPrograma();

            if ($idPrograma === null) {
                $builder->whereRaw('1 = 0');

                return;
            }

            $builder->where(function ($q) use ($idPrograma) {
                $q->whereHas('curso', fn ($qc) => $qc->where('id_programa', $idPrograma))
                    ->orWhereHas('estudiante', fn ($qe) => $qe->where('id_programa', $idPrograma));
            });
        });
    }

    protected $fillable = [
        'id_estudiante',
        'id_docente',
        'id_curso',
        'tipo',
        'severidad',
        'titulo',
        'detalle',
        'estado',
        'fecha_cierre',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }
}
