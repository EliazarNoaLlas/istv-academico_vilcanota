<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IaPrediccion extends Model
{
    protected $table = 'ia_predicciones';
    protected $primaryKey = 'id_prediccion';
    public $timestamps = false;

    protected $fillable = [
        'id_estudiante',
        'id_curso',
        'id_periodo',
        'modelo',
        'score_riesgo',
        'probabilidad_aprobar',
        'nivel',
        'factores_json',
        'simulacion_json',
        'recomendacion',
    ];

    protected $casts = [
        'score_riesgo' => 'decimal:2',
        'probabilidad_aprobar' => 'decimal:2',
        'factores_json' => 'array',
        'simulacion_json' => 'array',
        'fecha_prediccion' => 'datetime',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class, 'id_periodo');
    }
}
