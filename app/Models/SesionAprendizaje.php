<?php

namespace App\Models;

use App\Models\Scopes\CoordinadorProgramaViaRelacionScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SesionAprendizaje extends Model
{
    protected $table = 'sesiones_aprendizaje';
    protected $primaryKey = 'id_sesion';
    public $timestamps = false;

    protected static function booted(): void
    {
        static::addGlobalScope(new CoordinadorProgramaViaRelacionScope('curso'));
    }

    protected $fillable = [
        'id_curso',
        'id_docente',
        'titulo',
        'archivo',
        'numero_sesion',
        'estado',
    ];

    protected $casts = [
        'fecha_subida' => 'datetime',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }
}
