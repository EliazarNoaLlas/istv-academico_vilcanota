<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarioEvento extends Model
{
    protected $table = 'calendario_academico_eventos';
    protected $primaryKey = 'id_evento';

    protected $fillable = [
        'id_periodo',
        'id_usuario_creador',
        'titulo',
        'tipo',
        'fecha',
        'descripcion',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class, 'id_periodo');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_creador');
    }
}
