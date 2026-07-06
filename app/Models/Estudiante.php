<?php

namespace App\Models;

use App\Models\Scopes\CoordinadorProgramaDirectoScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estudiante extends Model
{
    use SoftDeletes;

    protected $table = 'estudiantes';
    protected $primaryKey = 'id_estudiante';
    public $timestamps = false;

    protected static function booted(): void
    {
        static::addGlobalScope(new CoordinadorProgramaDirectoScope());
    }

    protected $fillable = [
        'codigo_estudiante',
        'dni',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'correo',
        'telefono',
        'id_programa',
        'ciclo',
        'estado',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
    ];

    public function programa(): BelongsTo
    {
        return $this->belongsTo(ProgramaEstudio::class, 'id_programa');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'id_estudiante');
    }

    public function asistenciaDetalle(): HasMany
    {
        return $this->hasMany(AsistenciaDetalle::class, 'id_estudiante');
    }

    public function alertasAcademicas(): HasMany
    {
        return $this->hasMany(AlertaAcademica::class, 'id_estudiante');
    }

    public function iaPredicciones(): HasMany
    {
        return $this->hasMany(IaPrediccion::class, 'id_estudiante');
    }
}
