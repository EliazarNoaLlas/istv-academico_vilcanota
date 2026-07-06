<?php

namespace App\Models;

use App\Models\Scopes\CoordinadorProgramaViaRelacionScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Matricula extends Model
{
    protected $table = 'matriculas';
    protected $primaryKey = 'id_matricula';
    public $timestamps = false;

    protected static function booted(): void
    {
        static::addGlobalScope(new CoordinadorProgramaViaRelacionScope('estudiante'));
    }

    protected $fillable = [
        'id_estudiante',
        'id_periodo',
        'ciclo',
        'estado',
        'fecha_matricula',
    ];

    protected $casts = [
        'fecha_matricula' => 'date',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class, 'id_periodo');
    }

    public function matriculaCursos(): HasMany
    {
        return $this->hasMany(MatriculaCurso::class, 'id_matricula');
    }
}
