<?php

namespace App\Services\Horarios;

use App\Models\Aula;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\Horario;

/**
 * Reglas de negocio propias del horario institucional (no conflictos entre
 * bloques, eso vive en HorarioConflictService): receso obligatorio y
 * duracion de bloque academico valida.
 */
class HorarioValidationService
{
    private const RECESO_INICIO = '11:00';
    private const RECESO_FIN = '11:15';
    private const MINUTOS_BLOQUE = 45;

    private const DIAS_CANONICOS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    /**
     * @param array<int, array{hora_inicio:string,hora_fin:string}> $bloques
     * @return array<int, string> lista de mensajes de error (vacio = todo valido)
     */
    public function validarReglasInstitucionales(array $bloques): array
    {
        $errores = [];

        foreach ($bloques as $bloque) {
            if ($this->seSuperponeConReceso($bloque['hora_inicio'], $bloque['hora_fin'])) {
                $errores[] = "El bloque {$bloque['hora_inicio']}-{$bloque['hora_fin']} se superpone con el receso institucional (".self::RECESO_INICIO.'-'.self::RECESO_FIN.').';
            }

            $minutos = $this->minutosEntre($bloque['hora_inicio'], $bloque['hora_fin']);
            if ($minutos <= 0 || $minutos % self::MINUTOS_BLOQUE !== 0) {
                $errores[] = "El bloque {$bloque['hora_inicio']}-{$bloque['hora_fin']} no corresponde a un bloque academico valido (multiplo de ".self::MINUTOS_BLOQUE." minutos).";
            }
        }

        return array_values(array_unique($errores));
    }

    private function seSuperponeConReceso(string $inicio, string $fin): bool
    {
        return $inicio < self::RECESO_FIN && self::RECESO_INICIO < $fin;
    }

    private function minutosEntre(string $inicio, string $fin): int
    {
        [$h1, $m1] = array_map('intval', explode(':', $inicio));
        [$h2, $m2] = array_map('intval', explode(':', $fin));

        return ($h2 * 60 + $m2) - ($h1 * 60 + $m1);
    }

    /**
     * Valida una propuesta generada por IA: IDs reales y activos/disponibles,
     * formato de dia/hora, duplicados internos y horas minimas cubiertas por
     * curso. El receso y la duracion de bloque ya los cubre
     * validarReglasInstitucionales(), asi que no se repiten aqui.
     *
     * @param array<int, array{id_curso:mixed,id_docente:mixed,id_aula:mixed,dia:mixed,hora_inicio:mixed,hora_fin:mixed}> $detalles
     * @return array<int, string> mensajes de error (vacio = todo valido)
     */
    public function validarPropuestaIa(array $detalles): array
    {
        $errores = [];

        if ($detalles === []) {
            return ['La propuesta no contiene ningun bloque.'];
        }

        $cursos = Curso::whereIn('id_curso', array_column($detalles, 'id_curso'))->get()->keyBy('id_curso');
        $docentes = Docente::whereIn('id_docente', array_column($detalles, 'id_docente'))->get()->keyBy('id_docente');
        $aulas = Aula::whereIn('id_aula', array_column($detalles, 'id_aula'))->get()->keyBy('id_aula');

        $vistos = [];
        $bloquesPorCurso = [];

        foreach ($detalles as $i => $d) {
            $ref = "detalle #{$i}";

            $curso = $cursos->get($d['id_curso'] ?? null);
            if (! $curso) {
                $errores[] = "{$ref}: id_curso {$this->comoTexto($d['id_curso'] ?? null)} no existe.";
            } elseif ($curso->estado !== 'ACTIVO') {
                $errores[] = "{$ref}: el curso '{$curso->nombre_curso}' no esta activo.";
            }

            $docente = $docentes->get($d['id_docente'] ?? null);
            if (! $docente) {
                $errores[] = "{$ref}: id_docente {$this->comoTexto($d['id_docente'] ?? null)} no existe.";
            } elseif ($docente->estado_academico !== 'ACTIVO') {
                $errores[] = "{$ref}: el docente {$docente->id_docente} no esta activo.";
            }

            $aula = $aulas->get($d['id_aula'] ?? null);
            if (! $aula) {
                $errores[] = "{$ref}: id_aula {$this->comoTexto($d['id_aula'] ?? null)} no existe.";
            } elseif ($aula->estado !== 'DISPONIBLE') {
                $errores[] = "{$ref}: el aula '{$aula->codigo}' no esta disponible.";
            }

            if ($this->normalizarDia((string) ($d['dia'] ?? '')) === null) {
                $errores[] = "{$ref}: dia '{$this->comoTexto($d['dia'] ?? null)}' no es un dia valido.";
            }

            $horaInicio = $this->normalizarHora((string) ($d['hora_inicio'] ?? ''));
            $horaFin = $this->normalizarHora((string) ($d['hora_fin'] ?? ''));
            if ($horaInicio === null || $horaFin === null) {
                $errores[] = "{$ref}: hora_inicio/hora_fin con formato invalido.";
            } elseif ($horaInicio >= $horaFin) {
                $errores[] = "{$ref}: hora_inicio debe ser menor que hora_fin.";
            }

            $clave = ($d['id_curso'] ?? '?').'|'.($this->normalizarDia((string) ($d['dia'] ?? '')) ?? '?').'|'.($horaInicio ?? '?');
            if (isset($vistos[$clave])) {
                $errores[] = "{$ref}: bloque duplicado dentro de la misma propuesta (curso {$this->comoTexto($d['id_curso'] ?? null)}, mismo dia y hora).";
            }
            $vistos[$clave] = true;

            if ($curso) {
                $bloquesPorCurso[$curso->id_curso] = ($bloquesPorCurso[$curso->id_curso] ?? 0) + 1;
            }
        }

        foreach ($bloquesPorCurso as $idCurso => $bloquesAsignados) {
            $curso = $cursos->get($idCurso);
            $horasRequeridas = (float) ($curso->total_horas ?? 0);
            if ($horasRequeridas > 0 && $bloquesAsignados < $horasRequeridas) {
                $errores[] = "El curso '{$curso->nombre_curso}' tiene {$bloquesAsignados} bloque(s) asignado(s) pero requiere {$horasRequeridas}.";
            }
        }

        return array_values(array_unique($errores));
    }

    /**
     * Suma los bloques por docente (propuesta nueva + horarios ya existentes
     * en el periodo) y reporta conflicto CRITICO si alguno supera
     * config('services.horarios_ai.docente_max_bloques').
     *
     * @param array<int, array{id_docente:mixed}> $detalles
     * @return array<int, array{tipo:string, severidad:string, mensaje:string, data:array}>
     */
    public function validarCargaDocenteSemanal(array $detalles, ?int $idPeriodo = null): array
    {
        $maximo = (int) config('services.horarios_ai.docente_max_bloques', 20);

        $nuevosPorDocente = [];
        foreach ($detalles as $d) {
            $idDocente = $d['id_docente'] ?? null;
            if ($idDocente === null) {
                continue;
            }
            $nuevosPorDocente[$idDocente] = ($nuevosPorDocente[$idDocente] ?? 0) + 1;
        }

        $existentesPorDocente = Horario::query()
            ->whereIn('id_docente', array_keys($nuevosPorDocente))
            ->when($idPeriodo, fn ($q) => $q->where('id_periodo', $idPeriodo))
            ->selectRaw('id_docente, COUNT(*) as total')
            ->groupBy('id_docente')
            ->pluck('total', 'id_docente');

        $conflictos = [];
        foreach ($nuevosPorDocente as $idDocente => $bloquesNuevos) {
            $bloquesAsignados = $bloquesNuevos + (int) ($existentesPorDocente[$idDocente] ?? 0);

            if ($bloquesAsignados > $maximo) {
                $conflictos[] = [
                    'tipo' => 'DOCENTE_SUPERA_CARGA',
                    'severidad' => 'CRITICA',
                    'mensaje' => "El docente con ID {$idDocente} supera el maximo de {$maximo} bloques academicos semanales.",
                    'data' => [
                        'id_docente' => $idDocente,
                        'bloques_asignados' => $bloquesAsignados,
                        'maximo_permitido' => $maximo,
                    ],
                ];
            }
        }

        return $conflictos;
    }

    /** Normaliza a la grafia canonica del catalogo (con tilde) o null si no es un dia valido. */
    public function normalizarDia(string $dia): ?string
    {
        $sinTildes = strtr(mb_strtoupper(trim($dia)), ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U']);

        foreach (self::DIAS_CANONICOS as $canonico) {
            $canonicoSinTildes = strtr(mb_strtoupper($canonico), ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U']);
            if ($sinTildes === $canonicoSinTildes) {
                return $canonico;
            }
        }

        return null;
    }

    /** Normaliza "H:i", "H:i:s" o similares a "H:i" con ceros a la izquierda, o null si es invalido. */
    public function normalizarHora(string $hora): ?string
    {
        if (! preg_match('/^(\d{1,2}):(\d{2})(:\d{2})?$/', trim($hora), $m)) {
            return null;
        }

        $h = (int) $m[1];
        $min = (int) $m[2];

        if ($h > 23 || $min > 59) {
            return null;
        }

        return sprintf('%02d:%02d', $h, $min);
    }

    private function comoTexto(mixed $valor): string
    {
        return $valor === null ? 'null' : (string) $valor;
    }
}
