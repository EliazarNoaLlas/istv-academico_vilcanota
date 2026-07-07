<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItinerarioModulo extends Model
{
    protected $table = 'itinerario_modulos';
    protected $primaryKey = 'id_modulo';

    protected $fillable = [
        'id_itinerario',
        'numero_modulo',
        'codigo',
        'nombre',
        'competencia',
        'descripcion',
        'orden',
        'color_hex',
        'total_creditos',
        'total_horas',
        'estado',
    ];

    public function itinerario(): BelongsTo
    {
        return $this->belongsTo(ItinerarioFormativo::class, 'id_itinerario');
    }

    public function bloques(): HasMany
    {
        return $this->hasMany(ItinerarioBloque::class, 'id_modulo');
    }

    public function totales(): HasMany
    {
        return $this->hasMany(ItinerarioTotal::class, 'id_modulo');
    }

    /**
     * Suma los créditos y horas de sus bloques y actualiza
     * total_creditos y total_horas del módulo.
     */
    public function calcularTotales(): array
    {
        $totalCreditos = 0;
        $totalHoras = 0;

        foreach ($this->bloques as $bloque) {
            $totalCreditos += (int) $bloque->unidades()->sum('creditos');
            $totalHoras += (int) $bloque->unidades()->sum('horas_ud');
        }

        $this->update([
            'total_creditos' => $totalCreditos,
            'total_horas' => $totalHoras,
        ]);

        return [
            'id_modulo' => $this->id_modulo,
            'nombre' => $this->nombre,
            'total_creditos' => $totalCreditos,
            'total_horas' => $totalHoras,
        ];
    }
}
