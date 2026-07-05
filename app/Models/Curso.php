<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Curso extends Model
{
    use SoftDeletes;

    protected $table = 'cursos';
    protected $primaryKey = 'id_curso';
    public $timestamps = false;

    protected $fillable = [
        'id_docente',
        'id_programa',
        'tipo_curso',
        'nombre_curso',
        'modulo',
        'semestre',
        'creditos',
        'horas_teoria',
        'horas_practica',
        'horas_ud',
        'total_teoria',
        'total_practica',
        'total_horas',
        'estado',
    ];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(ProgramaEstudio::class, 'id_programa');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'id_curso');
    }

    public function matriculaCursos(): HasMany
    {
        return $this->hasMany(MatriculaCurso::class, 'id_curso');
    }

    public function portafolios(): HasMany
    {
        return $this->hasMany(PortafolioDocente::class, 'id_curso');
    }

    public function sesionesAprendizaje(): HasMany
    {
        return $this->hasMany(SesionAprendizaje::class, 'id_curso');
    }

    public function asistenciaSesiones(): HasMany
    {
        return $this->hasMany(AsistenciaSesion::class, 'id_curso');
    }

    public function alertasAcademicas(): HasMany
    {
        return $this->hasMany(AlertaAcademica::class, 'id_curso');
    }

    public function iaPredicciones(): HasMany
    {
        return $this->hasMany(IaPrediccion::class, 'id_curso');
    }
}
