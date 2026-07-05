<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocenteDisponibilidad extends Model
{
    protected $table = 'docente_disponibilidades';
    protected $primaryKey = 'id_disponibilidad';

    protected $fillable = [
        'id_docente',
        'dia',
        'hora_inicio',
        'hora_fin',
        'tipo',
        'motivo',
        'estado',
    ];

    protected $casts = [
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
    ];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }
}
