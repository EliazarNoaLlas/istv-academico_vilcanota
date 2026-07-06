<?php

namespace App\Models;

use App\Models\Scopes\CoordinadorProgramaViaRelacionScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortafolioDocente extends Model
{
    protected $table = 'portafolio_docente';
    protected $primaryKey = 'id_portafolio';
    public $timestamps = false;

    protected static function booted(): void
    {
        static::addGlobalScope(new CoordinadorProgramaViaRelacionScope('curso'));
    }

    protected $fillable = [
        'id_docente',
        'id_curso',
        'id_periodo',
        'silabo',
        'sesiones',
        'registro_asistencia',
        'registro_notas',
        'actas',
        'estado',
        'observacion',
    ];

    protected $casts = [
        'fecha_actualizacion' => 'datetime',
    ];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class, 'id_periodo');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(PortafolioDocumento::class, 'id_portafolio');
    }
}
