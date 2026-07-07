<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItinerarioTotal extends Model
{
    protected $table = 'itinerario_totales';
    protected $primaryKey = 'id_total';

    protected $fillable = [
        'id_itinerario',
        'id_modulo',
        'id_bloque',
        'tipo_total',
        'ciclo',
        'total_creditos',
        'total_horas_teoria',
        'total_horas_practica',
        'total_horas_ud',
    ];

    public function itinerario(): BelongsTo
    {
        return $this->belongsTo(ItinerarioFormativo::class, 'id_itinerario');
    }

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(ItinerarioModulo::class, 'id_modulo');
    }

    public function bloque(): BelongsTo
    {
        return $this->belongsTo(ItinerarioBloque::class, 'id_bloque');
    }
}
