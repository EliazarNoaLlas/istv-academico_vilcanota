<?php

namespace App\Services\Academic;

use App\Models\ItinerarioBloque;
use App\Models\ItinerarioFormativo;
use App\Models\ItinerarioModulo;
use App\Models\ItinerarioUnidadDidactica;

class ItinerarioCalculoService
{
    public const SEMANAS_TEORIA = 16;
    public const SEMANAS_PRACTICA = 32;

    /**
     * Calcula los campos derivados de una unidad didáctica a partir de
     * sus horas semanales de teoría y práctica.
     */
    public function calcularCamposUnidad(int $teoricas, int $practicas): array
    {
        $totalTeoria = $teoricas * self::SEMANAS_TEORIA;
        $totalPractica = $practicas * self::SEMANAS_PRACTICA;

        return [
            'horas_ciclo' => $teoricas + ($practicas * 2),
            'creditos' => $teoricas + $practicas,
            'total_horas_teoria' => $totalTeoria,
            'total_horas_practica' => $totalPractica,
            'horas_ud' => $totalTeoria + $totalPractica,
        ];
    }

    public function aplicarCalculosUnidad(ItinerarioUnidadDidactica $unidad): ItinerarioUnidadDidactica
    {
        $unidad->fill($this->calcularCamposUnidad(
            (int) $unidad->horas_teoricas_semanales,
            (int) $unidad->horas_practicas_semanales,
        ));
        $unidad->save();

        return $unidad;
    }

    public function calcularTotalesPorBloque(ItinerarioBloque $bloque): array
    {
        $creditos = (int) $bloque->unidades()->sum('creditos');
        $horas = (int) $bloque->unidades()->sum('horas_ud');

        $bloque->update([
            'creditos_bloque' => $creditos,
            'horas_bloque' => $horas,
        ]);

        return [
            'id_bloque' => $bloque->id_bloque,
            'nombre' => $bloque->nombre,
            'creditos_bloque' => $creditos,
            'horas_bloque' => $horas,
        ];
    }

    public function calcularTotalesPorModulo(ItinerarioModulo $modulo): array
    {
        $bloques = $modulo->bloques()->with('unidades')->get()
            ->map(fn (ItinerarioBloque $bloque) => $this->calcularTotalesPorBloque($bloque));

        $creditos = (int) $bloques->sum('creditos_bloque');
        $horas = (int) $bloques->sum('horas_bloque');

        $modulo->update([
            'total_creditos' => $creditos,
            'total_horas' => $horas,
        ]);

        return [
            'id_modulo' => $modulo->id_modulo,
            'nombre' => $modulo->nombre,
            'total_creditos' => $creditos,
            'total_horas' => $horas,
            'bloques' => $bloques->values()->all(),
        ];
    }

    public function calcularTotalesPorItinerario(ItinerarioFormativo $itinerario): array
    {
        $modulos = $itinerario->modulos()->get()
            ->map(fn (ItinerarioModulo $modulo) => $this->calcularTotalesPorModulo($modulo));

        $creditos = (int) $modulos->sum('total_creditos');
        $horas = (int) $modulos->sum('total_horas');

        $itinerario->update([
            'total_creditos' => $creditos,
            'total_horas' => $horas,
        ]);

        return [
            'id_itinerario' => $itinerario->id_itinerario,
            'total_creditos' => $creditos,
            'total_horas' => $horas,
            'modulos' => $modulos->values()->all(),
        ];
    }

    /**
     * Alias semántico: recalcula toda la cascada bloque -> módulo -> itinerario.
     */
    public function recalcularTotales(ItinerarioFormativo $itinerario): array
    {
        return $this->calcularTotalesPorItinerario($itinerario);
    }

    /**
     * Compara los totales registrados contra los calculados y detecta datos
     * incompletos. Devuelve la lista de inconsistencias (vacía si todo cuadra).
     */
    public function validarTotales(ItinerarioFormativo $itinerario): array
    {
        $itinerario->loadMissing('modulos.bloques.unidades');
        $validaciones = [];

        if (blank($itinerario->resolucion_oficio)) {
            $validaciones[] = [
                'nivel' => 'ADVERTENCIA',
                'ambito' => 'Itinerario',
                'titulo' => 'Itinerario sin oficio o resolución',
                'detalle' => "El itinerario \"{$itinerario->nombre}\" no tiene registrado el oficio o resolución de aprobación.",
                'recomendacion' => 'Registrar el número de oficio MINEDU en las propiedades del itinerario.',
            ];
        }

        $creditosItinerario = 0;
        $horasItinerario = 0;

        foreach ($itinerario->modulos as $modulo) {
            if (blank($modulo->competencia)) {
                $validaciones[] = [
                    'nivel' => 'ADVERTENCIA',
                    'ambito' => "Módulo {$modulo->numero_modulo}",
                    'titulo' => 'Módulo sin competencia registrada',
                    'detalle' => "El módulo \"{$modulo->nombre}\" no tiene definida su competencia técnica.",
                    'recomendacion' => 'Registrar la competencia del módulo según el documento oficial.',
                ];
            }

            $creditosModulo = 0;
            $horasModulo = 0;

            foreach ($modulo->bloques as $bloque) {
                $creditosCalculados = (int) $bloque->unidades->sum('creditos');
                $horasCalculadas = (int) $bloque->unidades->sum('horas_ud');
                $creditosModulo += $creditosCalculados;
                $horasModulo += $horasCalculadas;

                if ($bloque->unidades->isEmpty()) {
                    $validaciones[] = [
                        'nivel' => 'ADVERTENCIA',
                        'ambito' => "Módulo {$modulo->numero_modulo} · {$bloque->nombre}",
                        'titulo' => 'Bloque sin unidades didácticas',
                        'detalle' => "El bloque \"{$bloque->nombre}\" no tiene unidades didácticas registradas.",
                        'recomendacion' => 'Registrar las unidades didácticas del bloque o eliminarlo.',
                    ];
                }

                if ($bloque->creditos_bloque !== $creditosCalculados) {
                    $validaciones[] = [
                        'nivel' => 'ERROR',
                        'ambito' => "Módulo {$modulo->numero_modulo} · {$bloque->nombre}",
                        'titulo' => 'Inconsistencia de créditos en bloque',
                        'detalle' => "Créditos registrados: {$bloque->creditos_bloque}. Créditos calculados: {$creditosCalculados}.",
                        'recomendacion' => "Corregir el total de créditos a {$creditosCalculados} o revisar las unidades didácticas del bloque.",
                        'id_bloque' => $bloque->id_bloque,
                        'comparacion' => ['etiqueta' => 'Créditos', 'registrado' => $bloque->creditos_bloque, 'calculado' => $creditosCalculados],
                    ];
                }

                if ($bloque->horas_bloque !== $horasCalculadas) {
                    $validaciones[] = [
                        'nivel' => 'ERROR',
                        'ambito' => "Módulo {$modulo->numero_modulo} · {$bloque->nombre}",
                        'titulo' => 'Inconsistencia de horas en bloque',
                        'detalle' => "Total ingresado: {$bloque->horas_bloque}. Total calculado: {$horasCalculadas}.",
                        'recomendacion' => "Corregir el total de horas a {$horasCalculadas} o revisar las unidades didácticas del bloque.",
                        'id_bloque' => $bloque->id_bloque,
                        'comparacion' => ['etiqueta' => 'Horas', 'registrado' => $bloque->horas_bloque, 'calculado' => $horasCalculadas],
                    ];
                }

                foreach ($bloque->unidades as $unidad) {
                    if (((int) $unidad->horas_teoricas_semanales + (int) $unidad->horas_practicas_semanales) === 0) {
                        $validaciones[] = [
                            'nivel' => 'ADVERTENCIA',
                            'ambito' => "Ciclo {$unidad->ciclo} · {$unidad->nombre}",
                            'titulo' => 'Unidad didáctica sin horas',
                            'detalle' => "La unidad \"{$unidad->nombre}\" tiene 0 horas teóricas y 0 horas prácticas.",
                            'recomendacion' => 'Registrar las horas semanales de teoría y práctica de la unidad.',
                        ];
                        continue;
                    }

                    $esperado = $this->calcularCamposUnidad(
                        (int) $unidad->horas_teoricas_semanales,
                        (int) $unidad->horas_practicas_semanales,
                    );

                    if ((int) $unidad->horas_ud !== $esperado['horas_ud'] || (int) $unidad->creditos !== $esperado['creditos']) {
                        $validaciones[] = [
                            'nivel' => 'ERROR',
                            'ambito' => "Ciclo {$unidad->ciclo} · {$unidad->nombre}",
                            'titulo' => 'Campos derivados desactualizados en unidad',
                            'detalle' => "Créditos/horas guardados ({$unidad->creditos} cr., {$unidad->horas_ud} h) no coinciden con la fórmula ({$esperado['creditos']} cr., {$esperado['horas_ud']} h).",
                            'recomendacion' => 'Guardar nuevamente la unidad o usar "Recalcular totales" para aplicar las fórmulas.',
                        ];
                    }
                }
            }

            if ($modulo->total_creditos !== $creditosModulo || $modulo->total_horas !== $horasModulo) {
                $validaciones[] = [
                    'nivel' => 'ERROR',
                    'ambito' => "Módulo {$modulo->numero_modulo}",
                    'titulo' => 'Inconsistencia de totales en módulo',
                    'detalle' => "Registrado: {$modulo->total_creditos} cr. / {$modulo->total_horas} h. Calculado: {$creditosModulo} cr. / {$horasModulo} h.",
                    'recomendacion' => 'Usar "Recalcular totales" para sincronizar el módulo con sus bloques.',
                    'comparacion' => $modulo->total_horas !== $horasModulo
                        ? ['etiqueta' => 'Horas', 'registrado' => $modulo->total_horas, 'calculado' => $horasModulo]
                        : ['etiqueta' => 'Créditos', 'registrado' => $modulo->total_creditos, 'calculado' => $creditosModulo],
                ];
            }

            $creditosItinerario += $creditosModulo;
            $horasItinerario += $horasModulo;
        }

        if ($itinerario->total_creditos !== $creditosItinerario || $itinerario->total_horas !== $horasItinerario) {
            $validaciones[] = [
                'nivel' => 'ERROR',
                'ambito' => 'Itinerario',
                'titulo' => 'Inconsistencia de totales generales',
                'detalle' => "Registrado: {$itinerario->total_creditos} cr. / {$itinerario->total_horas} h. Calculado: {$creditosItinerario} cr. / {$horasItinerario} h.",
                'recomendacion' => 'Usar "Recalcular totales" para sincronizar el itinerario con sus módulos.',
                'comparacion' => $itinerario->total_horas !== $horasItinerario
                    ? ['etiqueta' => 'Horas', 'registrado' => $itinerario->total_horas, 'calculado' => $horasItinerario]
                    : ['etiqueta' => 'Créditos', 'registrado' => $itinerario->total_creditos, 'calculado' => $creditosItinerario],
            ];
        }

        return $validaciones;
    }
}
