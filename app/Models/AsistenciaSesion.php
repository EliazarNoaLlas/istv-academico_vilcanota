<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsistenciaSesion extends Model
{
    protected $table = 'asistencia_sesiones';
    protected $primaryKey = 'id_sesion';
    public $timestamps = false;

    protected $fillable = [
        'id_curso',
        'id_docente',
        'id_horario',
        'id_periodo',
        'fecha_sesion',
        'hora_inicio',
        'hora_fin',
        'tema',
        'estado',
    ];

    protected $casts = [
        'fecha_sesion' => 'date',
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

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class, 'id_horario');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class, 'id_periodo');
    }

    public function detalle(): HasMany
    {
        return $this->hasMany(AsistenciaDetalle::class, 'id_sesion');
    }
}
