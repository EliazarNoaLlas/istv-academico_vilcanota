<?php

namespace App\Models;

use App\Models\Scopes\CoordinadorDocenteProgramaScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Docente extends Model
{
    use SoftDeletes;

    protected $table = 'docentes';
    protected $primaryKey = 'id_docente';
    public $timestamps = false;

    protected static function booted(): void
    {
        static::addGlobalScope(new CoordinadorDocenteProgramaScope());
    }

    protected $fillable = [
        'id_usuario',
        'codigo_docente',
        'especialidad',
        'tipo_docente',
        'estado_academico',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'id_docente');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'id_docente');
    }

    public function portafolios(): HasMany
    {
        return $this->hasMany(PortafolioDocente::class, 'id_docente');
    }

    public function sesionesAprendizaje(): HasMany
    {
        return $this->hasMany(SesionAprendizaje::class, 'id_docente');
    }

    public function asistenciaSesiones(): HasMany
    {
        return $this->hasMany(AsistenciaSesion::class, 'id_docente');
    }

    public function alertasAcademicas(): HasMany
    {
        return $this->hasMany(AlertaAcademica::class, 'id_docente');
    }

    /** Filas completas de docente_programa (tipo_asignacion, es_principal, fechas, observacion). */
    public function asignacionesPrograma(): HasMany
    {
        return $this->hasMany(DocentePrograma::class, 'id_docente');
    }

    public function programas(): BelongsToMany
    {
        return $this->belongsToMany(ProgramaEstudio::class, 'docente_programa', 'id_docente', 'id_programa')
            ->withPivot(['tipo_asignacion', 'es_principal', 'estado', 'fecha_inicio', 'fecha_fin', 'observacion'])
            ->withTimestamps();
    }

    public function disponibilidades(): HasMany
    {
        return $this->hasMany(DocenteDisponibilidad::class, 'id_docente');
    }
}
