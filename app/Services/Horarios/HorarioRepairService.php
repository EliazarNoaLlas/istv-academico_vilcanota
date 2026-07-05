<?php

namespace App\Services\Horarios;

/**
 * Reparacion determinista en PHP de una propuesta IA: nunca inventa IDs,
 * nunca asigna docentes/aulas inactivos, nunca supera el maximo de bloques
 * por docente y nunca crea un cruce nuevo al mover algo (toda reubicacion se
 * valida contra la ocupacion actual antes de aplicarse).
 */
class HorarioRepairService
{
    public function __construct(
        private readonly HorarioConflictService $conflictos,
        private readonly HorarioValidationService $validacion,
    ) {}

    /**
     * @param array<int, array<string, mixed>> $detalles
     * @param array{dias:array,bloques:array,aulas:array,docentes:array,cursos:array,id_periodo?:int,docente_max_bloques?:int} $contexto
     * @return array{detalles:array<int,array<string,mixed>>, cambios:array<int,string>, conflictos_restantes:array<int,array>, intentos:int}
     */
    public function reparar(array $detalles, array $contexto, int $maxIntentos = 50): array
    {
        $cambios = [];
        $intentos = 0;
        $idPeriodo = $contexto['id_periodo'] ?? null;

        while ($intentos < $maxIntentos) {
            $conflictosCruce = $this->conflictos->detectarParaPropuestaIa($detalles);
            $conflictosCarga = $this->validacion->validarCargaDocenteSemanal($detalles, $idPeriodo);

            if ($conflictosCruce === [] && $conflictosCarga === []) {
                break;
            }

            $resuelto = false;

            foreach ($conflictosCruce as $conflicto) {
                if ($intentos >= $maxIntentos) {
                    break;
                }
                $intentos++;
                if ($this->repararCruce($conflicto, $detalles, $contexto, $cambios)) {
                    $resuelto = true;
                    break;
                }
            }

            if (! $resuelto) {
                foreach ($conflictosCarga as $conflicto) {
                    if ($intentos >= $maxIntentos) {
                        break;
                    }
                    $intentos++;
                    if ($this->repararCarga($conflicto, $detalles, $contexto, $cambios)) {
                        $resuelto = true;
                        break;
                    }
                }
            }

            if (! $resuelto) {
                break;
            }
        }

        return [
            'detalles' => $detalles,
            'cambios' => $cambios,
            'intentos' => $intentos,
            'conflictos_restantes' => array_merge(
                $this->conflictos->detectarParaPropuestaIa($detalles),
                $this->validacion->validarCargaDocenteSemanal($detalles, $idPeriodo),
            ),
        ];
    }

    private function repararCruce(array $conflicto, array &$detalles, array $contexto, array &$cambios): bool
    {
        [$i, $j] = $conflicto['data']['indices'];
        $curso = $this->cursoDelContexto($contexto, $detalles[$j]['id_curso'] ?? null);
        $tieneDocenteFijo = ! empty($curso['id_docente']);

        // 1) Mover el bloque j a otro horario libre (mismo docente, misma aula).
        $slot = $this->buscarSlotLibre($detalles, $contexto, $j, (int) $detalles[$j]['id_docente'], (int) $detalles[$j]['id_aula']);
        if ($slot !== null) {
            $detalles[$j]['dia'] = $slot['dia'];
            $detalles[$j]['hora_inicio'] = $slot['inicio'];
            $detalles[$j]['hora_fin'] = $slot['fin'];
            $cambios[] = "Bloque #{$j} (curso {$detalles[$j]['id_curso']}) movido a {$slot['dia']} {$slot['inicio']} para evitar {$conflicto['tipo']}.";

            return true;
        }

        // 2) Si el cruce es solo de aula, intentar cambiar el aula en el mismo horario.
        if ($conflicto['tipo'] === 'CRUCE_AULA') {
            $aula = $this->buscarAulaLibre($detalles, $contexto, $j, $curso);
            if ($aula !== null) {
                $detalles[$j]['id_aula'] = $aula;
                $cambios[] = "Bloque #{$j} (curso {$detalles[$j]['id_curso']}) reasignado al aula {$aula} por cruce de ambiente.";

                return true;
            }
        }

        // 3) Si el cruce es de docente y el curso no tiene docente fijo, reasignar docente.
        if ($conflicto['tipo'] === 'CRUCE_DOCENTE' && ! $tieneDocenteFijo) {
            $docente = $this->buscarDocenteCompatible($detalles, $contexto, $j, (int) $detalles[$j]['id_docente']);
            if ($docente !== null) {
                $detalles[$j]['id_docente'] = $docente;
                $cambios[] = "Bloque #{$j} (curso {$detalles[$j]['id_curso']}) reasignado al docente {$docente} por cruce horario.";

                return true;
            }
        }

        return false;
    }

    /**
     * Reasigna docente a uno de los bloques excedentes de $conflicto['data']['id_docente'],
     * solo si el curso correspondiente no tiene docente fijo en cursos.id_docente.
     * Si ningun bloque es reasignable, el conflicto queda marcado como no reparable
     * (mover el bloque de horario no reduce la carga total semanal del docente).
     */
    private function repararCarga(array $conflicto, array &$detalles, array $contexto, array &$cambios): bool
    {
        $idDocenteSobrecargado = $conflicto['data']['id_docente'];

        foreach ($detalles as $k => $detalle) {
            if ((int) ($detalle['id_docente'] ?? null) !== (int) $idDocenteSobrecargado) {
                continue;
            }

            $curso = $this->cursoDelContexto($contexto, $detalle['id_curso'] ?? null);
            if (! empty($curso['id_docente'])) {
                continue;
            }

            $docente = $this->buscarDocenteCompatible($detalles, $contexto, $k, (int) $idDocenteSobrecargado);
            if ($docente !== null) {
                $detalles[$k]['id_docente'] = $docente;
                $cambios[] = "Bloque #{$k} (curso {$detalle['id_curso']}) reasignado al docente {$docente} porque el docente {$idDocenteSobrecargado} superaba su carga maxima.";

                return true;
            }
        }

        return false;
    }

    private function buscarSlotLibre(array $detalles, array $contexto, int $indiceExcluido, int $idDocente, int $idAula): ?array
    {
        $ocupacion = $this->construirOcupacion($detalles, $indiceExcluido);

        foreach ($contexto['dias'] as $dia) {
            foreach ($contexto['bloques'] as $bloque) {
                $clave = mb_strtoupper($dia).'|'.$bloque['inicio'];

                if (in_array($idDocente, $ocupacion['docente'][$clave] ?? [], true)) {
                    continue;
                }
                if (in_array($idAula, $ocupacion['aula'][$clave] ?? [], true)) {
                    continue;
                }

                return ['dia' => mb_strtoupper($dia), 'inicio' => $bloque['inicio'], 'fin' => $bloque['fin']];
            }
        }

        return null;
    }

    private function buscarAulaLibre(array $detalles, array $contexto, int $indiceExcluido, ?array $curso): ?int
    {
        $detalle = $detalles[$indiceExcluido];
        $clave = mb_strtoupper((string) $detalle['dia']).'|'.$detalle['hora_inicio'];
        $ocupacion = $this->construirOcupacion($detalles, $indiceExcluido);

        foreach ($this->aulasCompatibles($contexto, $curso) as $aula) {
            if (! in_array($aula['id_aula'], $ocupacion['aula'][$clave] ?? [], true)) {
                return $aula['id_aula'];
            }
        }

        return null;
    }

    private function buscarDocenteCompatible(array $detalles, array $contexto, int $indiceExcluido, int $idDocenteActual): ?int
    {
        $detalle = $detalles[$indiceExcluido];
        $clave = mb_strtoupper((string) $detalle['dia']).'|'.$detalle['hora_inicio'];
        $ocupacion = $this->construirOcupacion($detalles, $indiceExcluido);
        $maximo = (int) ($contexto['docente_max_bloques'] ?? config('services.horarios_ai.docente_max_bloques', 20));

        $cargaActual = [];
        foreach ($detalles as $k => $d) {
            if ($k === $indiceExcluido) {
                continue;
            }
            $cargaActual[$d['id_docente']] = ($cargaActual[$d['id_docente']] ?? 0) + 1;
        }

        $candidatos = collect($contexto['docentes'])
            ->reject(fn ($doc) => (int) $doc['id_docente'] === $idDocenteActual)
            ->reject(fn ($doc) => in_array($doc['id_docente'], $ocupacion['docente'][$clave] ?? [], true))
            ->reject(function ($doc) use ($cargaActual, $maximo) {
                $carga = (int) ($doc['carga_actual_bloques'] ?? 0) + (int) ($cargaActual[$doc['id_docente']] ?? 0);

                return $carga + 1 > $maximo;
            })
            ->sortBy(fn ($doc) => (int) ($doc['carga_actual_bloques'] ?? 0) + (int) ($cargaActual[$doc['id_docente']] ?? 0));

        return $candidatos->first()['id_docente'] ?? null;
    }

    private function aulasCompatibles(array $contexto, ?array $curso): array
    {
        $aulas = $contexto['aulas'] ?? [];

        if (($curso['horas_practica'] ?? 0) > 0) {
            $preferidas = array_values(array_filter($aulas, fn ($a) => in_array($a['tipo'], ['LABORATORIO', 'TALLER'], true)));
            if ($preferidas !== []) {
                return $preferidas;
            }
        }

        return $aulas;
    }

    private function cursoDelContexto(array $contexto, mixed $idCurso): ?array
    {
        foreach ($contexto['cursos'] ?? [] as $curso) {
            if ((int) $curso['id_curso'] === (int) $idCurso) {
                return $curso;
            }
        }

        return null;
    }

    /** @return array{docente:array<string,array<int,int>>, aula:array<string,array<int,int>>} */
    private function construirOcupacion(array $detalles, int $indiceExcluido): array
    {
        $ocupacion = ['docente' => [], 'aula' => []];

        foreach ($detalles as $k => $d) {
            if ($k === $indiceExcluido) {
                continue;
            }

            $clave = mb_strtoupper((string) $d['dia']).'|'.$d['hora_inicio'];
            $ocupacion['docente'][$clave][] = (int) $d['id_docente'];
            $ocupacion['aula'][$clave][] = (int) $d['id_aula'];
        }

        return $ocupacion;
    }
}
