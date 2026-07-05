<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Horario extends Model
{
    protected $table = 'horarios';
    protected $primaryKey = 'id_horario';
    public $timestamps = false;

    protected $fillable = [
        'id_curso',
        'id_docente',
        'id_aula',
        'id_periodo',
        'id_programa',
        'semestre',
        'dia',
        'hora_inicio',
        'hora_fin',
        'aula',
        'estado',
        'fuente',
        'observacion',
    ];

    protected $casts = [
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }

    /**
     * Nombrada distinto de la columna 'aula' (varchar legado): si se llamara
     * igual, Eloquent siempre devolveria el atributo de columna y jamas
     * ejecutaria esta relacion.
     */
    public function aulaAsignada(): BelongsTo
    {
        return $this->belongsTo(Aula::class, 'id_aula');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class, 'id_periodo');
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(ProgramaEstudio::class, 'id_programa');
    }

    public function asistenciaSesiones(): HasMany
    {
        return $this->hasMany(AsistenciaSesion::class, 'id_horario');
    }
}
