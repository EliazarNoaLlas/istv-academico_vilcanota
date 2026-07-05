<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatriculaCurso extends Model
{
    protected $table = 'matricula_cursos';
    protected $primaryKey = 'id_matricula_curso';
    public $timestamps = false;

    protected $fillable = [
        'id_matricula',
        'id_curso',
        'estado',
    ];

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'id_matricula');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'id_matricula_curso');
    }
}
