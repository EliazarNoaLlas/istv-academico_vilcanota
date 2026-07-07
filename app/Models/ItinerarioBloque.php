<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItinerarioBloque extends Model
{
    protected $table = 'itinerario_bloques';
    protected $primaryKey = 'id_bloque';

    protected $fillable = [
        'id_modulo',
        'nombre',
        'tipo_bloque',
        'color_hex',
        'orden',
        'creditos_bloque',
        'horas_bloque',
        'descripcion',
    ];

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(ItinerarioModulo::class, 'id_modulo');
    }

    public function unidades(): HasMany
    {
        return $this->hasMany(ItinerarioUnidadDidactica::class, 'id_bloque');
    }

    public function totales(): HasMany
    {
        return $this->hasMany(ItinerarioTotal::class, 'id_bloque');
    }

    /**
     * Suma créditos y horas_ud de sus unidades didácticas y actualiza
     * creditos_bloque y horas_bloque.
     */
    public function calcularTotales(): array
    {
        $creditos = (int) $this->unidades()->sum('creditos');
        $horas = (int) $this->unidades()->sum('horas_ud');

        $this->update([
            'creditos_bloque' => $creditos,
            'horas_bloque' => $horas,
        ]);

        return [
            'id_bloque' => $this->id_bloque,
            'nombre' => $this->nombre,
            'creditos_bloque' => $creditos,
            'horas_bloque' => $horas,
        ];
    }

    /**
     * Compara los totales registrados contra la suma real de sus unidades.
     * Detecta errores como horas_bloque = 269 cuando la suma real es 768.
     */
    public function validarTotales(): array
    {
        $creditosCalculados = (int) $this->unidades()->sum('creditos');
        $horasCalculadas = (int) $this->unidades()->sum('horas_ud');

        return [
            'nivel' => 'BLOQUE',
            'id' => $this->id_bloque,
            'nombre' => $this->nombre,
            'creditos_registrados' => $this->creditos_bloque,
            'creditos_calculados' => $creditosCalculados,
            'horas_registradas' => $this->horas_bloque,
            'horas_calculadas' => $horasCalculadas,
            'es_valido' => $this->creditos_bloque === $creditosCalculados
                && $this->horas_bloque === $horasCalculadas,
        ];
    }
}
