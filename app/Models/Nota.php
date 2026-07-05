<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nota extends Model
{
    protected $table = 'notas';
    protected $primaryKey = 'id_nota';

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'id_matricula_curso',
        'unidad',
        'practica',
        'teoria',
        'examen',
        'estado',
    ];

    protected $casts = [
        'practica' => 'decimal:2',
        'teoria' => 'decimal:2',
        'examen' => 'decimal:2',
        'promedio' => 'decimal:2',
    ];

    public function matriculaCurso(): BelongsTo
    {
        return $this->belongsTo(MatriculaCurso::class, 'id_matricula_curso');
    }
}
