<?php

namespace App\Services\Horarios;

use App\Models\Curso;

/**
 * Deteccion pura de conflictos (docente, aula, semestre/programa). Antes
 * vivia dentro de HorarioValidationService; se separo para que
 * HorarioValidationService quede solo con las reglas de negocio propias
 * (receso institucional, duracion de bloque) y este servicio se pueda
 * exponer directamente como endpoint (/detectar-conflictos).
 */
class HorarioConflictService
{
    /**
     * @param array<int, array{id_curso:int,id_docente:int,dia:string,hora_inicio:string,hora_fin:string,aula:?string}> $bloques
     * @return array<int, string> lista de mensajes de conflicto (vacio = sin conflictos)
     */
    public function detectar(array $bloques): array
    {
        $conflictos = [];
        $semestrePorCurso = Curso::pluck('semestre', 'id_curso');

        foreach ($bloques as $i => $a) {
            foreach ($bloques as $j => $b) {
                if ($j <= $i || $a['dia'] !== $b['dia'] || ! $this->seSuperponen($a, $b)) {
                    continue;
                }

                if ($a['id_docente'] === $b['id_docente']) {
                    $conflictos[] = "Superposicion de docente el {$a['dia']}: el docente ya tiene otro bloque entre {$a['hora_inicio']} y {$a['hora_fin']}.";
                }

                if (! empty($a['aula']) && ($a['aula'] ?? null) === ($b['aula'] ?? null)) {
                    $conflictos[] = "Superposicion de aula {$a['aula']} el {$a['dia']} entre {$a['hora_inicio']} y {$a['hora_fin']}.";
                }

                $semestreA = $semestrePorCurso[$a['id_curso']] ?? null;
                $semestreB = $semestrePorCurso[$b['id_curso']] ?? null;
                if ($semestreA !== null && $semestreA === $semestreB && $a['id_curso'] !== $b['id_curso']) {
                    $conflictos[] = "Dos cursos del semestre {$semestreA} coinciden el {$a['dia']} entre {$a['hora_inicio']} y {$a['hora_fin']}.";
                }
            }
        }

        return array_values(array_unique($conflictos));
    }

    private function seSuperponen(array $a, array $b): bool
    {
        return $a['hora_inicio'] < $b['hora_fin'] && $b['hora_inicio'] < $a['hora_fin'];
    }

    /**
     * Version estructurada de detectar(), pensada para una propuesta IA: usa
     * id_aula (no texto de aula, que la propuesta no trae) y devuelve arrays
     * con los indices de los detalles en conflicto para que
     * HorarioRepairService pueda actuar sobre ellos directamente.
     *
     * @param array<int, array{id_curso:mixed,id_docente:mixed,id_aula:mixed,dia:mixed,hora_inicio:mixed,hora_fin:mixed}> $detalles
     * @return array<int, array{tipo:string, severidad:string, mensaje:string, data:array}>
     */
    public function detectarParaPropuestaIa(array $detalles): array
    {
        $conflictos = [];

        foreach ($detalles as $i => $a) {
            foreach ($detalles as $j => $b) {
                if ($j <= $i || ($a['dia'] ?? null) !== ($b['dia'] ?? null) || ! $this->seSuperponen($a, $b)) {
                    continue;
                }

                if (($a['id_docente'] ?? null) !== null && $a['id_docente'] === $b['id_docente']) {
                    $conflictos[] = [
                        'tipo' => 'CRUCE_DOCENTE',
                        'severidad' => 'CRITICA',
                        'mensaje' => "El docente {$a['id_docente']} tiene dos bloques el {$a['dia']} entre {$a['hora_inicio']} y {$a['hora_fin']}.",
                        'data' => ['indices' => [$i, $j], 'id_docente' => $a['id_docente'], 'dia' => $a['dia']],
                    ];
                }

                if (($a['id_aula'] ?? null) !== null && $a['id_aula'] === $b['id_aula']) {
                    $conflictos[] = [
                        'tipo' => 'CRUCE_AULA',
                        'severidad' => 'CRITICA',
                        'mensaje' => "El aula {$a['id_aula']} esta ocupada dos veces el {$a['dia']} entre {$a['hora_inicio']} y {$a['hora_fin']}.",
                        'data' => ['indices' => [$i, $j], 'id_aula' => $a['id_aula'], 'dia' => $a['dia']],
                    ];
                }
            }
        }

        return $conflictos;
    }
}
