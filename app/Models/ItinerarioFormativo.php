<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItinerarioFormativo extends Model
{
    protected $table = 'itinerarios_formativos';
    protected $primaryKey = 'id_itinerario';

    protected $fillable = [
        'id_programa',
        'codigo',
        'nombre',
        'resolucion_oficio',
        'descripcion',
        'duracion_ciclos',
        'total_creditos',
        'total_horas',
        'version',
        'estado',
        'fecha_aprobacion',
    ];

    protected $casts = [
        'fecha_aprobacion' => 'date',
    ];

    public function programa(): BelongsTo
    {
        return $this->belongsTo(ProgramaEstudio::class, 'id_programa');
    }

    public function modulos(): HasMany
    {
        return $this->hasMany(ItinerarioModulo::class, 'id_itinerario');
    }

    public function totales(): HasMany
    {
        return $this->hasMany(ItinerarioTotal::class, 'id_itinerario');
    }

    /**
     * Suma créditos y horas de las unidades didácticas de cada bloque
     * y actualiza creditos_bloque y horas_bloque.
     */
    public function calcularTotalesPorBloque(): array
    {
        $resultados = [];

        foreach ($this->modulos as $modulo) {
            foreach ($modulo->bloques as $bloque) {
                $resultados[] = $bloque->calcularTotales();
            }
        }

        return $resultados;
    }

    /**
     * Suma créditos y horas de los bloques de cada módulo
     * y actualiza total_creditos y total_horas del módulo.
     */
    public function calcularTotalesPorModulo(): array
    {
        return $this->modulos->map(fn (ItinerarioModulo $modulo) => $modulo->calcularTotales())->all();
    }

    /**
     * Recalcula bloques, módulos y los totales generales del itinerario.
     */
    public function calcularTotalesPorItinerario(): array
    {
        $this->calcularTotalesPorBloque();
        $this->load('modulos.bloques.unidades');
        $this->calcularTotalesPorModulo();

        $totalCreditos = 0;
        $totalHoras = 0;

        foreach ($this->modulos as $modulo) {
            $totalCreditos += $modulo->total_creditos;
            $totalHoras += $modulo->total_horas;
        }

        $this->update([
            'total_creditos' => $totalCreditos,
            'total_horas' => $totalHoras,
        ]);

        return [
            'total_creditos' => $totalCreditos,
            'total_horas' => $totalHoras,
        ];
    }

    /**
     * Compara los totales registrados contra los calculados en bloques,
     * módulos y el itinerario. Devuelve las inconsistencias encontradas.
     */
    public function validarTotales(): array
    {
        $inconsistencias = [];
        $creditosCalculados = 0;
        $horasCalculadas = 0;

        foreach ($this->modulos()->with('bloques.unidades')->get() as $modulo) {
            $creditosModulo = 0;
            $horasModulo = 0;

            foreach ($modulo->bloques as $bloque) {
                $validacion = $bloque->validarTotales();

                if (!$validacion['es_valido']) {
                    $inconsistencias[] = $validacion;
                }

                $creditosModulo += $validacion['creditos_calculados'];
                $horasModulo += $validacion['horas_calculadas'];
            }

            if ($modulo->total_creditos !== $creditosModulo || $modulo->total_horas !== $horasModulo) {
                $inconsistencias[] = [
                    'nivel' => 'MODULO',
                    'id' => $modulo->id_modulo,
                    'nombre' => $modulo->nombre,
                    'creditos_registrados' => $modulo->total_creditos,
                    'creditos_calculados' => $creditosModulo,
                    'horas_registradas' => $modulo->total_horas,
                    'horas_calculadas' => $horasModulo,
                    'es_valido' => false,
                ];
            }

            $creditosCalculados += $creditosModulo;
            $horasCalculadas += $horasModulo;
        }

        if ($this->total_creditos !== $creditosCalculados || $this->total_horas !== $horasCalculadas) {
            $inconsistencias[] = [
                'nivel' => 'ITINERARIO',
                'id' => $this->id_itinerario,
                'nombre' => $this->nombre,
                'creditos_registrados' => $this->total_creditos,
                'creditos_calculados' => $creditosCalculados,
                'horas_registradas' => $this->total_horas,
                'horas_calculadas' => $horasCalculadas,
                'es_valido' => false,
            ];
        }

        return $inconsistencias;
    }
}
