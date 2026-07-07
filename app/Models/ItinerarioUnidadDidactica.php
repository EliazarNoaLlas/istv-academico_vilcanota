<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItinerarioUnidadDidactica extends Model
{
    public const SEMANAS_TEORIA = 16;
    public const SEMANAS_PRACTICA = 32;

    protected $table = 'itinerario_unidades_didacticas';
    protected $primaryKey = 'id_unidad';

    protected $fillable = [
        'id_bloque',
        'id_curso',
        'nombre',
        'codigo',
        'ciclo',
        'horas_ciclo',
        'horas_teoricas_semanales',
        'horas_practicas_semanales',
        'creditos',
        'total_horas_teoria',
        'total_horas_practica',
        'horas_ud',
        'orden',
        'es_editable',
        'observacion',
        'estado',
    ];

    protected $casts = [
        'es_editable' => 'boolean',
    ];

    public function bloque(): BelongsTo
    {
        return $this->belongsTo(ItinerarioBloque::class, 'id_bloque');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }

    /**
     * horas_ciclo = horas_teoricas_semanales + (horas_practicas_semanales * 2)
     */
    public function calcularHorasCiclo(): int
    {
        return $this->horas_teoricas_semanales + ($this->horas_practicas_semanales * 2);
    }

    /**
     * creditos = horas_teoricas_semanales + horas_practicas_semanales
     */
    public function calcularCreditos(): int
    {
        return $this->horas_teoricas_semanales + $this->horas_practicas_semanales;
    }

    /**
     * total_horas_teoria = horas_teoricas_semanales * 16
     */
    public function calcularTotalHorasTeoria(): int
    {
        return $this->horas_teoricas_semanales * self::SEMANAS_TEORIA;
    }

    /**
     * total_horas_practica = horas_practicas_semanales * 32
     */
    public function calcularTotalHorasPractica(): int
    {
        return $this->horas_practicas_semanales * self::SEMANAS_PRACTICA;
    }

    /**
     * horas_ud = total_horas_teoria + total_horas_practica
     */
    public function calcularHorasUd(): int
    {
        return $this->calcularTotalHorasTeoria() + $this->calcularTotalHorasPractica();
    }

    /**
     * Recalcula y guarda todos los campos derivados a partir de
     * horas_teoricas_semanales y horas_practicas_semanales.
     */
    public function aplicarCalculos(): self
    {
        $this->horas_ciclo = $this->calcularHorasCiclo();
        $this->creditos = $this->calcularCreditos();
        $this->total_horas_teoria = $this->calcularTotalHorasTeoria();
        $this->total_horas_practica = $this->calcularTotalHorasPractica();
        $this->horas_ud = $this->calcularHorasUd();
        $this->save();

        return $this;
    }
}
