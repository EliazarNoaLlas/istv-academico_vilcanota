<?php

namespace App\Services\Horarios;

use RuntimeException;

/**
 * Extrae y valida la forma base del JSON devuelto por el LLM. El modelo
 * puede envolver el JSON en texto o bloques ```json — aqui se aisla el
 * primer objeto JSON balanceado del texto antes de decodificar.
 */
class HorarioAiResponseParserService
{
    /**
     * @return array{estado:string, detalles:array<int,array<string,mixed>>, observaciones:array<int,string>, conflictos:array<int,mixed>}
     *
     * @throws RuntimeException si no se puede extraer un JSON con la forma esperada
     */
    public function parsear(string $textoCrudo): array
    {
        $json = $this->extraerJsonBalanceado($textoCrudo);

        if ($json === null) {
            throw new RuntimeException('No se pudo extraer un JSON valido de la respuesta del modelo.');
        }

        $datos = json_decode($json, true);

        if (! is_array($datos) || json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('El JSON extraido de la respuesta del modelo no es valido: '.json_last_error_msg());
        }

        if (! array_key_exists('detalles', $datos) || ! is_array($datos['detalles'])) {
            throw new RuntimeException('El JSON de la respuesta no tiene la clave "detalles" esperada.');
        }

        return [
            'estado' => is_string($datos['estado'] ?? null) ? $datos['estado'] : 'GENERADO',
            'detalles' => array_values($datos['detalles']),
            'observaciones' => is_array($datos['observaciones'] ?? null) ? array_values($datos['observaciones']) : [],
            'conflictos' => is_array($datos['conflictos'] ?? null) ? array_values($datos['conflictos']) : [],
        ];
    }

    private function extraerJsonBalanceado(string $texto): ?string
    {
        $inicio = strpos($texto, '{');
        if ($inicio === false) {
            return null;
        }

        $profundidad = 0;
        $dentroDeCadena = false;
        $escapado = false;

        for ($i = $inicio; $i < strlen($texto); $i++) {
            $caracter = $texto[$i];

            if ($dentroDeCadena) {
                if ($escapado) {
                    $escapado = false;
                } elseif ($caracter === '\\') {
                    $escapado = true;
                } elseif ($caracter === '"') {
                    $dentroDeCadena = false;
                }

                continue;
            }

            if ($caracter === '"') {
                $dentroDeCadena = true;
            } elseif ($caracter === '{') {
                $profundidad++;
            } elseif ($caracter === '}') {
                $profundidad--;
                if ($profundidad === 0) {
                    return substr($texto, $inicio, $i - $inicio + 1);
                }
            }
        }

        return null;
    }
}
