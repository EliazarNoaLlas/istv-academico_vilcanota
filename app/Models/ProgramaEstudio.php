<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramaEstudio extends Model
{
    protected $table = 'programas_estudio';
    protected $primaryKey = 'id_programa';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'familia_profesional',
        'duracion_ciclos',
        'estado',
    ];

    public function estudiantes(): HasMany
    {
        return $this->hasMany(Estudiante::class, 'id_programa');
    }

    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'id_programa');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'id_programa');
    }

    public function docentes(): BelongsToMany
    {
        return $this->belongsToMany(Docente::class, 'docente_programa', 'id_programa', 'id_docente')
            ->withPivot(['tipo_asignacion', 'es_principal', 'estado', 'fecha_inicio', 'fecha_fin', 'observacion'])
            ->withTimestamps();
    }
}
