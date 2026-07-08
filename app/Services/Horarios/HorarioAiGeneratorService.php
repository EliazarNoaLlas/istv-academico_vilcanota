<?php

namespace App\Services\Horarios;

use App\Models\Aula;
use App\Models\Horario;
use App\Models\HorarioIaGenerado;
use App\Models\PeriodoAcademico;
use App\Models\ProgramaEstudio;
use App\Services\Horarios\Providers\FakeHorarioProvider;
use App\Services\Horarios\Providers\GeminiHorarioProvider;
use App\Services\Horarios\Providers\GrokHorarioProvider;
use App\Services\Horarios\Providers\LlmHorarioProviderInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Orquesta la generacion con IA: arma el contexto/prompt, llama al provider,
 * parsea, valida, repara si hace falta y solo entonces persiste. Nunca
 * guarda la respuesta cruda de la IA directamente en `horarios`.
 */
class HorarioAiGeneratorService
{
    /** Semestres que Fase 3 genera automaticamente; I y III ya existen y no se tocan. */
    private const SEMESTRES_GENERABLES_DSI = ['II', 'IV', 'V', 'VI'];
    private const SEMESTRES_PROTEGIDOS_DSI = ['I', 'III'];

    /** 5 dias x 6 bloques utiles (sin receso) = capacidad semanal de la tabla. */
    private const TOTAL_SLOTS_SEMANA = 30;

    public function __construct(
        private readonly HorarioAiPromptBuilderService $promptBuilder,
        private readonly HorarioAiResponseParserService $parser,
        private readonly HorarioValidationService $validacion,
        private readonly HorarioConflictService $conflictos,
        private readonly HorarioRepairService $repairService,
        private readonly HorarioPersistenceService $persistencia,
        private readonly HorarioQueryService $consultas,
        private readonly HorarioCatalogService $catalogo,
    ) {}

    /**
     * @param array{id_programa:int,id_periodo:int,semestre?:?string,provider?:?string,modo?:?string,max_intentos_reparacion?:?int,id_usuario?:?int} $datos
     */
    public function generar(array $datos): array
    {
        $filtro = [
            'id_programa' => (int) $datos['id_programa'],
            'id_periodo' => (int) $datos['id_periodo'],
            'semestre' => $datos['semestre'] ?? null,
        ];
        $modo = $datos['modo'] ?? 'guardar_si_valido';
        $maxIntentos = (int) ($datos['max_intentos_reparacion'] ?? config('services.horarios_ai.max_repair_attempts', 50));

        $contexto = $this->promptBuilder->contexto($filtro);
        $prompt = $this->promptBuilder->construir($filtro)['prompt'];

        $generacion = HorarioIaGenerado::create([
            'id_usuario' => $datos['id_usuario'] ?? null,
            'id_periodo' => $filtro['id_periodo'],
            'programa' => ProgramaEstudio::find($filtro['id_programa'])?->nombre,
            'prompt_resumen' => Str::limit($prompt, 4000),
            'estado' => 'BORRADOR',
            'metadata_json' => ['filtro' => $filtro],
        ]);

        try {
            $nombreProvider = $datos['provider'] ?? config('services.horarios_ai.provider', 'gemini');
            $provider = $this->resolverProveedor($nombreProvider);
            $generacion->modelo = $this->modeloDe($nombreProvider);

            $textoCrudo = $provider->generar($prompt);
            $parseado = $this->parser->parsear($textoCrudo);
        } catch (Throwable $e) {
            $generacion->fill([
                'errores_json' => ['error' => $e->getMessage()],
            ])->save();

            return $this->respuesta($generacion, false, 'No se pudo generar la propuesta: '.$e->getMessage());
        }

        return $this->validarRepararYGuardar($generacion, $parseado['detalles'], $parseado['observaciones'], $contexto, $filtro, $modo, $maxIntentos);
    }

    public function aprobar(int $idGeneracion): array
    {
        $generacion = HorarioIaGenerado::findOrFail($idGeneracion);

        if ($generacion->estado === 'DESCARTADO') {
            return $this->respuesta($generacion, false, 'La generacion fue descartada y no puede aprobarse.');
        }

        if ($generacion->estado === 'APROBADO') {
            return $this->respuesta($generacion, true, 'La generacion ya estaba aprobada.');
        }

        $detalles = $generacion->resultado_json['detalles'] ?? [];
        $filtro = $generacion->metadata_json['filtro'] ?? ['id_programa' => null, 'semestre' => null];

        [$errores, $conflictos] = $this->validarTodo($detalles, $generacion->id_periodo);

        if ($errores !== [] || $conflictos !== []) {
            $generacion->fill(['errores_json' => ['errores' => $errores, 'conflictos' => $conflictos]])->save();

            return $this->respuesta($generacion, false, 'La propuesta aun tiene errores o conflictos; use "reparar" antes de aprobar.');
        }

        $this->guardarPropuestaValidada($detalles, $filtro);
        $generacion->fill(['estado' => 'APROBADO', 'errores_json' => null])->save();

        return $this->respuesta($generacion, true, 'Horario aprobado y guardado con fuente IA.');
    }

    public function descartar(int $idGeneracion): array
    {
        $generacion = HorarioIaGenerado::findOrFail($idGeneracion);
        $generacion->fill(['estado' => 'DESCARTADO'])->save();

        return $this->respuesta($generacion, true, 'Generacion descartada.');
    }

    public function reparar(int $idGeneracion, ?int $maxIntentos = null): array
    {
        $generacion = HorarioIaGenerado::findOrFail($idGeneracion);

        if ($generacion->estado !== 'BORRADOR') {
            return $this->respuesta($generacion, false, 'Solo se puede reparar una generacion en estado BORRADOR.');
        }

        $filtro = $generacion->metadata_json['filtro'] ?? ['id_programa' => null, 'id_periodo' => $generacion->id_periodo, 'semestre' => null];
        $contexto = $this->promptBuilder->contexto($filtro);
        $maxIntentos ??= (int) config('services.horarios_ai.max_repair_attempts', 50);

        $detalles = $generacion->resultado_json['detalles'] ?? [];
        $observaciones = $generacion->resultado_json['observaciones'] ?? [];

        return $this->validarRepararYGuardar($generacion, $detalles, $observaciones, $contexto, $filtro, 'borrador', $maxIntentos, reintentoManual: true);
    }

    public function estado(int $idGeneracion): array
    {
        return $this->respuesta(HorarioIaGenerado::findOrFail($idGeneracion), true, 'OK');
    }

    private function validarRepararYGuardar(
        HorarioIaGenerado $generacion,
        array $detalles,
        array $observaciones,
        array $contexto,
        array $filtro,
        string $modo,
        int $maxIntentos,
        bool $reintentoManual = false,
    ): array {
        [$errores, $conflictos] = $this->validarTodo($detalles, $filtro['id_periodo'] ?? $generacion->id_periodo);
        $cambios = [];
        $intentosUsados = 0;

        if ($conflictos !== [] && $maxIntentos > 0) {
            $reparado = $this->repairService->reparar($detalles, $contexto, $maxIntentos);
            $detalles = $reparado['detalles'];
            $cambios = $reparado['cambios'];
            $intentosUsados = $reparado['intentos'];
            $conflictos = $reparado['conflictos_restantes'];
            $errores = $this->validacion->validarPropuestaIa($detalles);
            $errores = array_merge($errores, $this->validacion->validarReglasInstitucionales($detalles));
        }

        $esValido = $errores === [] && $conflictos === [];

        $metadata = $generacion->metadata_json ?? ['filtro' => $filtro];
        $metadata['intentos_reparacion'] = ($metadata['intentos_reparacion'] ?? 0) + $intentosUsados;
        $metadata['cambios_reparacion'] = array_merge($metadata['cambios_reparacion'] ?? [], $cambios);

        $generacion->fill([
            'resultado_json' => ['detalles' => $detalles, 'observaciones' => $observaciones],
            'metadata_json' => $metadata,
            'errores_json' => $esValido ? null : ['errores' => $errores, 'conflictos' => $conflictos],
        ]);

        if ($esValido && $modo === 'guardar_si_valido') {
            $this->guardarPropuestaValidada($detalles, $filtro);
            $generacion->estado = 'APROBADO';
        } else {
            $generacion->estado = 'BORRADOR';
        }

        $generacion->save();

        if ($esValido) {
            $mensaje = $modo === 'guardar_si_valido' ? 'Horario generado y guardado correctamente.' : 'Propuesta valida, lista para aprobar.';
        } else {
            $mensaje = $reintentoManual ? 'Se aplicaron reparaciones parciales; aun quedan conflictos por revisar.' : 'La propuesta quedo con conflictos; revise y use "reparar" o corrija manualmente.';
        }

        return $this->respuesta($generacion, true, $mensaje);
    }

    /** @return array{0: array<int,string>, 1: array<int,array>} [errores, conflictos] */
    private function validarTodo(array $detalles, ?int $idPeriodo): array
    {
        $errores = array_merge(
            $this->validacion->validarPropuestaIa($detalles),
            $this->validacion->validarReglasInstitucionales($detalles),
        );

        $conflictos = array_merge(
            $this->conflictos->detectarParaPropuestaIa($detalles),
            $this->validacion->validarCargaDocenteSemanal($detalles, $idPeriodo),
        );

        return [$errores, $conflictos];
    }

    private function guardarPropuestaValidada(array $detalles, array $filtro): void
    {
        $aulasPorId = Aula::whereIn('id_aula', array_column($detalles, 'id_aula'))->get()->keyBy('id_aula');

        $bloques = array_map(fn (array $d) => [
            'id_curso' => $d['id_curso'],
            'id_docente' => $d['id_docente'],
            'dia' => $this->validacion->normalizarDia((string) $d['dia']) ?? $d['dia'],
            'hora_inicio' => $this->validacion->normalizarHora((string) $d['hora_inicio']) ?? $d['hora_inicio'],
            'hora_fin' => $this->validacion->normalizarHora((string) $d['hora_fin']) ?? $d['hora_fin'],
            'aula' => $aulasPorId->get($d['id_aula'])?->codigo,
            'estado' => 'Confirmado',
            'fuente' => 'IA',
        ], $detalles);

        $filtrosPersistencia = array_filter([
            'semestre' => $filtro['semestre'] ?? null,
            'id_programa' => $filtro['id_programa'] ?? null,
        ]);

        $this->persistencia->guardar($bloques, $filtrosPersistencia);
    }

    private function resolverProveedor(string $nombre): LlmHorarioProviderInterface
    {
        return match ($nombre) {
            'grok' => app(GrokHorarioProvider::class),
            'fake' => app(FakeHorarioProvider::class),
            default => app(GeminiHorarioProvider::class),
        };
    }

    private function modeloDe(string $nombreProvider): string
    {
        return match ($nombreProvider) {
            'grok' => (string) config('services.grok.model'),
            'fake' => 'fake',
            default => (string) config('services.gemini.model'),
        };
    }

    private function respuesta(HorarioIaGenerado $generacion, bool $ok, string $mensaje): array
    {
        return [
            'ok' => $ok,
            'mensaje' => $mensaje,
            'generacion' => [
                'id_generacion' => $generacion->id_generacion,
                'estado' => $generacion->estado,
                'modelo' => $generacion->modelo,
                'programa' => $generacion->programa,
                'resultado' => $generacion->resultado_json,
                'errores' => $generacion->errores_json,
                'metadata' => $generacion->metadata_json,
                'fecha_generacion' => optional($generacion->fecha_generacion)->toIso8601String(),
            ],
        ];
    }

    /**
     * Fase 3: genera de forma determinista (sin LLM) el horario semanal de
     * UN semestre DSI. No toca I ni III (protegidos), no duplica si ya
     * existe horario, y no genera si hay cursos sin docente. La propuesta se
     * construye respetando de entrada la disponibilidad real de docentes y
     * aulas (incluye lo ya guardado de otros semestres), asi que en el caso
     * normal no llega a necesitar HorarioRepairService.
     */
    public function generarSemestreDsi(int $idPrograma, int $idPeriodo, string $semestre): array
    {
        if (! in_array($semestre, self::SEMESTRES_GENERABLES_DSI, true)) {
            return $this->respuestaSemestre($semestre, false, 'SEMESTRE_NO_PERMITIDO', "El semestre {$semestre} no se genera con este flujo (ya existe y esta protegido, o esta fuera de alcance).");
        }

        $resumen = $this->consultas->resumenPorSemestre($idPrograma, $idPeriodo)->firstWhere('semestre', $semestre);

        if (! $resumen || $resumen['cursos'] === 0) {
            return $this->respuestaSemestre($semestre, false, 'SIN_CURSOS', "El semestre {$semestre} no tiene cursos activos del programa.");
        }

        if ($resumen['cursos_sin_docente'] > 0) {
            return $this->respuestaSemestre($semestre, false, 'CURSOS_SIN_DOCENTE', "El semestre {$semestre} tiene {$resumen['cursos_sin_docente']} curso(s) sin docente asignado.");
        }

        if ($resumen['bloques_generados'] > 0) {
            return $this->respuestaSemestre($semestre, false, 'HORARIO_EXISTENTE', "El semestre {$semestre} ya tiene {$resumen['bloques_generados']} bloque(s) generados. No se duplica.");
        }

        if ($resumen['bloques_requeridos'] === 0 || $resumen['bloques_requeridos'] > self::TOTAL_SLOTS_SEMANA) {
            return $this->respuestaSemestre($semestre, false, 'SIN_CAPACIDAD', "El semestre {$semestre} requiere {$resumen['bloques_requeridos']} bloques y la tabla semanal solo tiene ".self::TOTAL_SLOTS_SEMANA.' disponibles.');
        }

        $cursos = $this->consultas->cursosSemestre($idPrograma, $semestre);
        $aulas = Aula::where('estado', 'DISPONIBLE')->get();

        if ($aulas->isEmpty()) {
            return $this->respuestaSemestre($semestre, false, 'SIN_CAPACIDAD', 'No hay aulas disponibles para generar el horario.');
        }

        [$detalles, $pendientes] = $this->ubicarBloques($cursos, $aulas, $this->slotsSemana(), $idPeriodo, $semestre);

        if ($pendientes !== []) {
            return $this->guardarBorradorYReportar($semestre, $idPeriodo, $idPrograma, $detalles, $pendientes, 'CONFLICTOS_NO_REPARABLES', "No se pudieron ubicar todas las horas del semestre {$semestre} sin cruces de docente o aula.");
        }

        [$errores, $conflictos] = $this->validarProduccionFinal($detalles);

        if ($errores !== [] || $conflictos !== []) {
            $detalles = $this->intentarReparar($detalles, $cursos, $aulas, $idPeriodo);
            [$errores, $conflictos] = $this->validarProduccionFinal($detalles);
        }

        if ($errores !== [] || $conflictos !== []) {
            return $this->guardarBorradorYReportar($semestre, $idPeriodo, $idPrograma, $detalles, array_merge($errores, $conflictos), 'CONFLICTOS_NO_REPARABLES', "La propuesta del semestre {$semestre} quedo con conflictos incluso despues de reparar.");
        }

        $this->persistencia->guardar($detalles, ['semestre' => $semestre, 'id_programa' => $idPrograma]);

        return $this->respuestaSemestre($semestre, true, 'GENERADO', "Horario generado correctamente para el semestre {$semestre}.", [
            'resumen' => [
                'cursos' => $cursos->count(),
                'bloques_requeridos' => $resumen['bloques_requeridos'],
                'bloques_generados' => count($detalles),
                'docentes_usados' => collect($detalles)->pluck('id_docente')->unique()->count(),
                'aulas_usadas' => collect($detalles)->pluck('aula')->unique()->count(),
                'conflictos' => 0,
            ],
            'horarios' => $this->horariosPersistidos($idPrograma, $idPeriodo, $semestre),
        ]);
    }

    /**
     * Fase 4: limpia SOLO los bloques fuente = 'IA' del semestre y vuelve a
     * generar. Si hay algun bloque fuente = 'MANUAL' (cargado por un humano
     * desde el editor), se detiene sin tocar nada: regenerar automatico
     * nunca debe pisar una edicion manual confirmada.
     */
    public function regenerarSemestreDsi(int $idPrograma, int $idPeriodo, string $semestre): array
    {
        if (! in_array($semestre, self::SEMESTRES_GENERABLES_DSI, true)) {
            return $this->respuestaSemestre($semestre, false, 'SEMESTRE_NO_PERMITIDO', "El semestre {$semestre} no admite este flujo (protegido o fuera de alcance).");
        }

        $tieneManual = Horario::where('id_programa', $idPrograma)
            ->where('id_periodo', $idPeriodo)
            ->where('semestre', $semestre)
            ->where('fuente', 'MANUAL')
            ->exists();

        if ($tieneManual) {
            return $this->respuestaSemestre($semestre, false, 'HORARIO_MANUAL_EXISTENTE', "El semestre {$semestre} tiene bloques cargados manualmente. Edite o elimine esos bloques desde el editor antes de regenerar; no se sobrescriben automaticamente.");
        }

        $this->persistencia->eliminarGeneradosPorIa(['semestre' => $semestre, 'id_programa' => $idPrograma]);

        return $this->generarSemestreDsi($idPrograma, $idPeriodo, $semestre);
    }

    /** Los bloques ya guardados en `horarios` para el semestre (se usa para adjuntarlos a la respuesta JSON). */
    private function horariosPersistidos(int $idPrograma, int $idPeriodo, string $semestre): array
    {
        return Horario::where('id_programa', $idPrograma)
            ->where('id_periodo', $idPeriodo)
            ->where('semestre', $semestre)
            ->orderBy('dia')
            ->orderBy('hora_inicio')
            ->get()
            ->toArray();
    }

    /** Fase 3: corre generarSemestreDsi() en orden para II, IV, V y VI. Nunca incluye I ni III. */
    public function generarSemestresPendientesDsi(int $idPrograma, int $idPeriodo, array $semestres = self::SEMESTRES_GENERABLES_DSI): array
    {
        $resultados = [];
        $generados = [];
        $bloquesGenerados = 0;
        $conConflictos = 0;

        foreach (array_intersect($semestres, self::SEMESTRES_GENERABLES_DSI) as $semestre) {
            $resultado = $this->generarSemestreDsi($idPrograma, $idPeriodo, $semestre);
            $resultados[] = $resultado;

            if ($resultado['ok'] && $resultado['estado'] === 'GENERADO') {
                $generados[] = $semestre;
                $bloquesGenerados += $resultado['resumen']['bloques_generados'] ?? 0;
            } else {
                $conConflictos++;
            }
        }

        return [
            'ok' => $conConflictos === 0,
            'estado' => $conConflictos === 0 ? 'GENERACION_COMPLETA' : 'GENERACION_PARCIAL',
            'programa' => ProgramaEstudio::find($idPrograma)?->nombre,
            'periodo' => PeriodoAcademico::find($idPeriodo)?->codigo,
            'resumen_global' => [
                'semestres_generados' => $generados,
                'semestres_no_tocados' => self::SEMESTRES_PROTEGIDOS_DSI,
                'bloques_generados' => $bloquesGenerados,
                'conflictos' => $conConflictos,
            ],
            'resultados' => $resultados,
        ];
    }

    /**
     * Coloca cada hora (bloques_requeridos = horas_ud) de cada curso en un
     * slot semanal libre. Recorre los 30 slots en orden y, en cada uno,
     * elige entre los cursos pendientes que SI caben ahi (docente libre -ni
     * por horarios ya guardados de otros semestres ni por lo colocado en
     * este mismo semestre-, aula preferida segun horas_practica libre) al
     * que tiene MENOS slots futuros compatibles con su docente ("variable
     * mas restringida primero"). Esto evita que un curso con docente
     * holgado consuma por accidente uno de los pocos huecos que le quedan a
     * un docente casi saturado (critico aqui porque bloques_requeridos =
     * capacidad exacta de 30, sin margen). Cada slot se usa una sola vez
     * (regla 15: no dos cursos del mismo semestre en la misma celda).
     *
     * @return array{0: array<int,array<string,mixed>>, 1: array<int,array<string,mixed>>} [detalles, pendientes]
     */
    private function ubicarBloques(BaseCollection $cursos, EloquentCollection $aulas, array $slots, int $idPeriodo, string $semestre): array
    {
        $ocupacionDocente = $this->ocupacionExistente($idPeriodo, 'id_docente');
        $ocupacionAula = $this->ocupacionExistente($idPeriodo, 'id_aula');

        $aulasPorCodigo = $aulas->keyBy('codigo');
        $laboratorios = $aulas->whereIn('tipo', ['LABORATORIO', 'TALLER'])->pluck('codigo')->values()->all();
        $comunes = $aulas->whereNotIn('tipo', ['LABORATORIO', 'TALLER'])->pluck('codigo')->values()->all();

        // Reparte bloques_requeridos (horas_ud) de cada curso entre
        // laboratorio y aula comun EN PROPORCION a horas_practica/horas_teoria,
        // en vez de mandar el curso completo a laboratorio con que tenga una
        // sola hora practica: eso satura los 3 laboratorios entre semestres
        // (todo DSI es "algo practico") y deja sin margen a los semestres
        // que se generan despues.
        $pendientes = [];
        $candidatosPorCurso = [];

        foreach ($cursos as $c) {
            $totalHoras = $c['horas_teoria'] + $c['horas_practica'];
            $horasLab = $totalHoras > 0
                ? (int) round($c['bloques_requeridos'] * $c['horas_practica'] / $totalHoras)
                : 0;
            $horasLab = min($horasLab, $c['bloques_requeridos']);
            $horasComun = $c['bloques_requeridos'] - $horasLab;

            $candidatosLab = $laboratorios !== [] ? $laboratorios : $comunes;
            $candidatosComun = $comunes !== [] ? $comunes : $laboratorios;
            $candidatosPorCurso[$c['id_curso']] = array_values(array_unique(array_merge(
                $horasLab > 0 ? $candidatosLab : [],
                $horasComun > 0 ? $candidatosComun : [],
            )));

            if ($horasLab > 0) {
                $pendientes[] = ['id_curso' => $c['id_curso'], 'nombre_curso' => $c['nombre_curso'], 'id_docente' => $c['id_docente'], 'candidatos_aula' => $candidatosLab, 'restantes' => $horasLab];
            }
            if ($horasComun > 0) {
                $pendientes[] = ['id_curso' => $c['id_curso'], 'nombre_curso' => $c['nombre_curso'], 'id_docente' => $c['id_docente'], 'candidatos_aula' => $candidatosComun, 'restantes' => $horasComun];
            }
        }

        $detalles = [];
        $usoPorSlot = [];

        foreach ($slots as $indiceSlot => $slot) {
            $clave = $slot['dia'].'|'.$slot['inicio'];
            $slotsFuturos = array_slice($slots, $indiceSlot);

            $mejorIndice = null;
            $mejorAula = null;
            $mejorUrgencia = null;

            foreach ($pendientes as $i => $item) {
                if ($item['restantes'] <= 0 || in_array($item['id_docente'], $ocupacionDocente[$clave] ?? [], true)) {
                    continue;
                }

                $aulaLibre = null;
                foreach ($item['candidatos_aula'] as $codigo) {
                    $aula = $aulasPorCodigo[$codigo] ?? null;
                    if ($aula && ! in_array($aula->id_aula, $ocupacionAula[$clave] ?? [], true)) {
                        $aulaLibre = $aula;
                        break;
                    }
                }

                if ($aulaLibre === null) {
                    continue;
                }

                // Opciones futuras reales: no basta con que el docente este
                // libre, tambien debe quedar al menos un aula de su tipo
                // preferido libre en ese slot (si no, no cuenta como opcion).
                $urgencia = 0;
                foreach ($slotsFuturos as $futuro) {
                    $claveFutura = $futuro['dia'].'|'.$futuro['inicio'];
                    if (in_array($item['id_docente'], $ocupacionDocente[$claveFutura] ?? [], true)) {
                        continue;
                    }

                    foreach ($item['candidatos_aula'] as $codigo) {
                        $aulaFutura = $aulasPorCodigo[$codigo] ?? null;
                        if ($aulaFutura && ! in_array($aulaFutura->id_aula, $ocupacionAula[$claveFutura] ?? [], true)) {
                            $urgencia++;
                            break;
                        }
                    }
                }

                if ($mejorUrgencia === null || $urgencia < $mejorUrgencia) {
                    $mejorUrgencia = $urgencia;
                    $mejorIndice = $i;
                    $mejorAula = $aulaLibre;
                }
            }

            if ($mejorIndice === null) {
                continue;
            }

            $item = $pendientes[$mejorIndice];

            $detalles[] = [
                'id_curso' => $item['id_curso'],
                'id_docente' => $item['id_docente'],
                'id_aula' => $mejorAula->id_aula,
                'dia' => $slot['dia'],
                'hora_inicio' => $slot['inicio'],
                'hora_fin' => $slot['fin'],
                'aula' => $mejorAula->codigo,
                'estado' => 'Confirmado',
                'fuente' => 'IA',
                'observacion' => "Horario generado automáticamente para semestre {$semestre}.",
            ];

            $ocupacionDocente[$clave][] = $item['id_docente'];
            $ocupacionAula[$clave][] = $mejorAula->id_aula;
            $usoPorSlot[$clave] = count($detalles) - 1;
            $pendientes[$mejorIndice]['restantes']--;
        }

        // Segunda pasada: para lo que quedo sin ubicar (caso limite, sin
        // margen porque bloques_requeridos = 30 = capacidad total), intenta
        // liberar un slot moviendo a otra hora libre al curso que lo ocupa,
        // en vez de rendirse de inmediato.
        foreach ($pendientes as $i => &$item) {
            while ($item['restantes'] > 0
                && $this->intentarSwap($item, $slots, $detalles, $usoPorSlot, $ocupacionDocente, $ocupacionAula, $aulasPorCodigo, $candidatosPorCurso)) {
                $item['restantes']--;
            }
        }
        unset($item);

        // Un curso puede tener dos entradas en $pendientes (porcion de
        // laboratorio y porcion de aula comun): se agrupan por id_curso para
        // que el reporte de conflictos no lo duplique.
        $sinUbicar = collect($pendientes)
            ->filter(fn ($p) => $p['restantes'] > 0)
            ->groupBy('id_curso')
            ->map(fn ($grupo) => [
                'id_curso' => $grupo->first()['id_curso'],
                'nombre_curso' => $grupo->first()['nombre_curso'],
                'id_docente' => $grupo->first()['id_docente'],
                'horas_sin_ubicar' => $grupo->sum('restantes'),
            ])
            ->values()
            ->all();

        return [$detalles, $sinUbicar];
    }

    /**
     * Busca un slot donde el docente del pendiente este libre pero ocupado
     * por OTRO curso de este mismo semestre, y reubica a ese ocupante en un
     * slot libre propio (docente y aula libres ahi) para liberar el slot
     * original. Si lo logra, coloca al pendiente en el slot liberado y
     * devuelve true. No toca horarios de otros semestres (solo reubica
     * bloques dentro de $detalles, el semestre que se esta generando).
     */
    private function intentarSwap(array $item, array $slots, array &$detalles, array &$usoPorSlot, array &$ocupacionDocente, array &$ocupacionAula, $aulasPorCodigo, array $candidatosPorCurso): bool
    {
        foreach ($slots as $slot) {
            $clave = $slot['dia'].'|'.$slot['inicio'];

            if (in_array($item['id_docente'], $ocupacionDocente[$clave] ?? [], true) || ! isset($usoPorSlot[$clave])) {
                continue;
            }

            $ocupanteIndex = $usoPorSlot[$clave];
            $ocupante = $detalles[$ocupanteIndex];

            foreach ($slots as $altSlot) {
                $altClave = $altSlot['dia'].'|'.$altSlot['inicio'];

                if ($altClave === $clave || isset($usoPorSlot[$altClave])
                    || in_array($ocupante['id_docente'], $ocupacionDocente[$altClave] ?? [], true)) {
                    continue;
                }

                $aulaAlt = $this->primeraAulaLibre($candidatosPorCurso[$ocupante['id_curso']] ?? [$ocupante['aula']], $aulasPorCodigo, $ocupacionAula[$altClave] ?? []);
                if ($aulaAlt === null) {
                    continue;
                }

                $aulaLiberadaEnClave = array_values(array_diff($ocupacionAula[$clave] ?? [], [$ocupante['id_aula']]));
                $aulaPendiente = $this->primeraAulaLibre($item['candidatos_aula'], $aulasPorCodigo, $aulaLiberadaEnClave);
                if ($aulaPendiente === null) {
                    continue;
                }

                // 1) liberar el slot original.
                $ocupacionDocente[$clave] = array_values(array_diff($ocupacionDocente[$clave], [$ocupante['id_docente']]));
                $ocupacionAula[$clave] = $aulaLiberadaEnClave;
                unset($usoPorSlot[$clave]);

                // 2) mover al ocupante a su slot alterno.
                $detalles[$ocupanteIndex] = array_merge($ocupante, [
                    'dia' => $altSlot['dia'], 'hora_inicio' => $altSlot['inicio'], 'hora_fin' => $altSlot['fin'],
                    'id_aula' => $aulaAlt->id_aula, 'aula' => $aulaAlt->codigo,
                ]);
                $ocupacionDocente[$altClave][] = $ocupante['id_docente'];
                $ocupacionAula[$altClave][] = $aulaAlt->id_aula;
                $usoPorSlot[$altClave] = $ocupanteIndex;

                // 3) colocar al pendiente en el slot que quedo libre.
                $detalles[] = [
                    'id_curso' => $item['id_curso'],
                    'id_docente' => $item['id_docente'],
                    'id_aula' => $aulaPendiente->id_aula,
                    'dia' => $slot['dia'],
                    'hora_inicio' => $slot['inicio'],
                    'hora_fin' => $slot['fin'],
                    'aula' => $aulaPendiente->codigo,
                    'estado' => 'Confirmado',
                    'fuente' => 'IA',
                    'observacion' => $ocupante['observacion'],
                ];
                $ocupacionDocente[$clave][] = $item['id_docente'];
                $ocupacionAula[$clave][] = $aulaPendiente->id_aula;
                $usoPorSlot[$clave] = count($detalles) - 1;

                return true;
            }
        }

        return false;
    }

    private function primeraAulaLibre(array $codigosCandidatos, $aulasPorCodigo, array $aulasOcupadas): ?Aula
    {
        foreach ($codigosCandidatos as $codigo) {
            $aula = $aulasPorCodigo[$codigo] ?? null;
            if ($aula && ! in_array($aula->id_aula, $aulasOcupadas, true)) {
                return $aula;
            }
        }

        return null;
    }

    /** Los 30 slots semanales (Lunes-Viernes x 6 bloques, receso ya excluido por el catalogo). */
    private function slotsSemana(): array
    {
        $bloques = array_values(array_filter(
            $this->catalogo->obtener()['bloques_horario'],
            fn ($b) => empty($b['receso'])
        ));

        $slots = [];
        foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'] as $dia) {
            foreach ($bloques as $bloque) {
                $slots[] = ['dia' => $dia, 'inicio' => $bloque['inicio'], 'fin' => $bloque['fin']];
            }
        }

        return $slots;
    }

    /** Ocupacion real ya guardada en `horarios` para el periodo, indexada por "dia|hora_inicio". */
    private function ocupacionExistente(int $idPeriodo, string $columna): array
    {
        $ocupacion = [];

        DB::table('horarios')
            ->where('id_periodo', $idPeriodo)
            ->whereNotNull($columna)
            ->get(['dia', 'hora_inicio', $columna])
            ->each(function ($fila) use (&$ocupacion, $columna) {
                $clave = $fila->dia.'|'.substr((string) $fila->hora_inicio, 0, 5);
                $ocupacion[$clave][] = (int) $fila->{$columna};
            });

        return $ocupacion;
    }

    /** @return array{0: array<int,string>, 1: array<int,string>} [errores institucionales, conflictos de cruce] */
    private function validarProduccionFinal(array $detalles): array
    {
        return [
            $this->validacion->validarReglasInstitucionales($detalles),
            $this->conflictos->detectar($detalles),
        ];
    }

    /** Red de seguridad: solo se ejecuta si validarProduccionFinal() encuentra algo inesperado. */
    private function intentarReparar(array $detalles, BaseCollection $cursos, EloquentCollection $aulas, int $idPeriodo): array
    {
        $contexto = [
            'dias' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
            'bloques' => collect($this->slotsSemana())->unique('inicio')->map(fn ($s) => ['inicio' => $s['inicio'], 'fin' => $s['fin']])->values()->all(),
            'aulas' => $aulas->map(fn ($a) => ['id_aula' => $a->id_aula, 'codigo' => $a->codigo, 'tipo' => $a->tipo, 'capacidad' => $a->capacidad])->all(),
            'docentes' => $cursos->pluck('id_docente')->unique()->map(fn ($id) => [
                'id_docente' => $id,
                'carga_actual_bloques' => Horario::where('id_docente', $id)->where('id_periodo', $idPeriodo)->count(),
            ])->values()->all(),
            'cursos' => $cursos->all(),
            'id_periodo' => $idPeriodo,
            'docente_max_bloques' => (int) config('services.horarios_ai.docente_max_bloques', 20),
        ];

        $reparado = $this->repairService->reparar($detalles, $contexto, 30);
        $detalles = $reparado['detalles'];

        $aulasPorId = $aulas->keyBy('id_aula');

        return array_map(function ($d) use ($aulasPorId) {
            $d['aula'] = $aulasPorId->get($d['id_aula'])?->codigo ?? ($d['aula'] ?? null);

            return $d;
        }, $detalles);
    }

    private function guardarBorradorYReportar(string $semestre, int $idPeriodo, int $idPrograma, array $detalles, array $problemas, string $estado, string $mensaje): array
    {
        $generacion = HorarioIaGenerado::create([
            'id_periodo' => $idPeriodo,
            'programa' => ProgramaEstudio::find($idPrograma)?->nombre,
            'modelo' => 'determinista-dsi',
            'resultado_json' => ['detalles' => $detalles],
            'errores_json' => ['problemas' => $problemas],
            'estado' => 'BORRADOR',
            'metadata_json' => ['filtro' => ['id_programa' => $idPrograma, 'id_periodo' => $idPeriodo, 'semestre' => $semestre]],
        ]);

        return $this->respuestaSemestre($semestre, false, $estado, $mensaje, [
            'id_generacion' => $generacion->id_generacion,
            'conflictos' => $problemas,
        ]);
    }

    private function respuestaSemestre(string $semestre, bool $ok, string $estado, string $mensaje, array $extra = []): array
    {
        return array_merge([
            'ok' => $ok,
            'estado' => $estado,
            'semestre' => $semestre,
            'mensaje' => $mensaje,
        ], $extra);
    }

    /**
     * Fase 3.2: reparacion dirigida y localizada. Solo actua sobre UN
     * semestre que ya quedo en BORRADOR con conflictos (no reintenta la
     * generacion completa ni toca otros semestres). Retoma los bloques que
     * SI se ubicaron en ese borrador, y para las horas pendientes usa una
     * cadena de intercambio de profundidad 2 (mueve al curso que ocupa el
     * slot que el pendiente necesita; si ese tambien esta bloqueado, mueve
     * primero a quien lo bloquea a el, y solo entonces reacomoda la cadena).
     * Cada intento de cadena se valida de punta a punta (docente y aula
     * libres en el slot final) antes de aplicar ningun cambio.
     */
    public function repararSemestreDsi(int $idPrograma, int $idPeriodo, string $semestre): array
    {
        if (! in_array($semestre, self::SEMESTRES_GENERABLES_DSI, true)) {
            return $this->respuestaSemestre($semestre, false, 'SEMESTRE_NO_PERMITIDO', "El semestre {$semestre} no admite este flujo.");
        }

        $resumen = $this->consultas->resumenPorSemestre($idPrograma, $idPeriodo)->firstWhere('semestre', $semestre);

        if ($resumen && $resumen['bloques_generados'] > 0) {
            return $this->respuestaSemestre($semestre, false, 'HORARIO_EXISTENTE', "El semestre {$semestre} ya tiene horario generado. No hay nada que reparar.");
        }

        $generacion = HorarioIaGenerado::where('estado', 'BORRADOR')
            ->where('id_periodo', $idPeriodo)
            ->where('metadata_json->filtro->id_programa', $idPrograma)
            ->where('metadata_json->filtro->semestre', $semestre)
            ->latest('id_generacion')
            ->first();

        if (! $generacion) {
            return $this->respuestaSemestre($semestre, false, 'SIN_BORRADOR', "No hay una propuesta BORRADOR pendiente para el semestre {$semestre}. Genere primero con generarSemestreDsi().");
        }

        $detalles = $generacion->resultado_json['detalles'] ?? [];
        $problemas = $generacion->errores_json['problemas'] ?? [];

        if ($problemas === []) {
            return $this->respuestaSemestre($semestre, false, 'SIN_CONFLICTOS', "La propuesta BORRADOR del semestre {$semestre} no tiene conflictos pendientes que reparar.");
        }

        $cursos = $this->consultas->cursosSemestre($idPrograma, $semestre);
        $aulas = Aula::where('estado', 'DISPONIBLE')->get();
        $aulasPorCodigo = $aulas->keyBy('codigo');
        $laboratorios = $aulas->whereIn('tipo', ['LABORATORIO', 'TALLER'])->pluck('codigo')->values()->all();
        $comunes = $aulas->whereNotIn('tipo', ['LABORATORIO', 'TALLER'])->pluck('codigo')->values()->all();

        $candidatosPorCurso = [];
        foreach ($cursos as $c) {
            $candidatosPorCurso[$c['id_curso']] = array_values(array_unique(array_merge(
                $c['horas_practica'] > 0 ? ($laboratorios ?: $comunes) : [],
                $c['horas_teoria'] > 0 || $c['horas_practica'] === 0 ? ($comunes ?: $laboratorios) : [],
            )));
        }

        // Ocupacion real: lo que ya hay guardado de otros semestres (I, III
        // y cualquier otro ya persistido) mas los bloques que este borrador
        // ya tenia bien ubicados. No se toca nada fuera de $detalles.
        $ocupacionDocente = $this->ocupacionExistente($idPeriodo, 'id_docente');
        $ocupacionAula = $this->ocupacionExistente($idPeriodo, 'id_aula');
        $usoPorSlot = [];

        foreach ($detalles as $i => $d) {
            $clave = $d['dia'].'|'.$d['hora_inicio'];
            $ocupacionDocente[$clave][] = $d['id_docente'];
            $ocupacionAula[$clave][] = $d['id_aula'];
            $usoPorSlot[$clave] = $i;
        }

        $slots = $this->slotsSemana();
        $pendientesRestantes = [];

        foreach ($problemas as $problema) {
            if (! isset($problema['id_curso'], $problema['id_docente'], $problema['horas_sin_ubicar'])) {
                continue;
            }

            $item = [
                'id_curso' => $problema['id_curso'],
                'nombre_curso' => $problema['nombre_curso'] ?? '',
                'id_docente' => $problema['id_docente'],
                'candidatos_aula' => $candidatosPorCurso[$problema['id_curso']] ?? array_merge($laboratorios, $comunes),
                'observacion' => "Horario generado automáticamente para semestre {$semestre} (reparación dirigida Fase 3.2).",
            ];

            $horasSinUbicar = 0;
            for ($h = (int) $problema['horas_sin_ubicar']; $h > 0; $h--) {
                if (! $this->intentarSwapProfundo($item, $slots, $detalles, $usoPorSlot, $ocupacionDocente, $ocupacionAula, $aulasPorCodigo, $candidatosPorCurso)) {
                    $horasSinUbicar++;
                }
            }

            if ($horasSinUbicar > 0) {
                $pendientesRestantes[] = [
                    'id_curso' => $item['id_curso'],
                    'nombre_curso' => $item['nombre_curso'],
                    'id_docente' => $item['id_docente'],
                    'horas_sin_ubicar' => $horasSinUbicar,
                ];
            }
        }

        if ($pendientesRestantes !== []) {
            $generacion->fill([
                'resultado_json' => ['detalles' => $detalles],
                'errores_json' => ['problemas' => $pendientesRestantes],
            ])->save();

            return $this->respuestaSemestre($semestre, false, 'CONFLICTOS_NO_REPARABLES', "La reparacion dirigida no logro ubicar todas las horas pendientes del semestre {$semestre}.", [
                'id_generacion' => $generacion->id_generacion,
                'conflictos' => $pendientesRestantes,
            ]);
        }

        [$errores, $conflictos] = $this->validarProduccionFinal($detalles);

        if ($errores !== [] || $conflictos !== []) {
            $generacion->fill(['errores_json' => ['errores' => $errores, 'conflictos' => $conflictos]])->save();

            return $this->respuestaSemestre($semestre, false, 'CONFLICTOS_NO_REPARABLES', "La propuesta reparada del semestre {$semestre} quedo con conflictos inesperados; revise manualmente.", [
                'id_generacion' => $generacion->id_generacion,
            ]);
        }

        $this->persistencia->guardar($detalles, ['semestre' => $semestre, 'id_programa' => $idPrograma]);
        $generacion->fill(['estado' => 'APROBADO', 'errores_json' => null, 'resultado_json' => ['detalles' => $detalles]])->save();

        return $this->respuestaSemestre($semestre, true, 'GENERADO', "Horario del semestre {$semestre} completado mediante reparacion dirigida.", [
            'resumen' => [
                'cursos' => $cursos->count(),
                'bloques_requeridos' => $resumen['bloques_requeridos'] ?? count($detalles),
                'bloques_generados' => count($detalles),
                'docentes_usados' => collect($detalles)->pluck('id_docente')->unique()->count(),
                'aulas_usadas' => collect($detalles)->pluck('aula')->unique()->count(),
                'conflictos' => 0,
            ],
        ]);
    }

    /**
     * Cadena de hasta 2 movimientos para liberarle un slot a $item:
     * 1) slot vacio -> se coloca directo.
     * 2) slot ocupado por A, y A tiene otro slot vacio -> se mueve A ahi.
     * 3) slot ocupado por A, cuyo unico slot alternativo esta ocupado por B,
     *    y B si tiene un slot realmente vacio -> se mueve B, despues A, y
     *    recien entonces se coloca $item. Toda la cadena se valida (docente
     *    y aula libres en cada tramo) ANTES de aplicar el primer cambio.
     */
    private function intentarSwapProfundo(array $item, array $slots, array &$detalles, array &$usoPorSlot, array &$ocupacionDocente, array &$ocupacionAula, $aulasPorCodigo, array $candidatosPorCurso): bool
    {
        foreach ($slots as $slot) {
            $clave = $slot['dia'].'|'.$slot['inicio'];

            if (in_array($item['id_docente'], $ocupacionDocente[$clave] ?? [], true)) {
                continue;
            }

            if (! isset($usoPorSlot[$clave])) {
                // Caso directo: el slot esta realmente vacio (sobra porque
                // el borrador quedo con menos de 30 bloques), solo falta que
                // haya un aula compatible libre ahi.
                $aulaDirecta = $this->primeraAulaLibre($item['candidatos_aula'], $aulasPorCodigo, $ocupacionAula[$clave] ?? []);
                if ($aulaDirecta !== null) {
                    $this->colocarBloqueNuevo($item, $slot, $aulaDirecta, $detalles, $usoPorSlot, $ocupacionDocente, $ocupacionAula);

                    return true;
                }

                continue;
            }

            $ocupanteIndex = $usoPorSlot[$clave];
            $ocupante = $detalles[$ocupanteIndex];

            foreach ($slots as $altSlot) {
                $altClave = $altSlot['dia'].'|'.$altSlot['inicio'];

                if ($altClave === $clave || in_array($ocupante['id_docente'], $ocupacionDocente[$altClave] ?? [], true)) {
                    continue;
                }

                if (! isset($usoPorSlot[$altClave])) {
                    // Profundidad 1: altSlot esta realmente libre para el ocupante.
                    $aulaAlt = $this->primeraAulaLibre($candidatosPorCurso[$ocupante['id_curso']] ?? [$ocupante['aula']], $aulasPorCodigo, $ocupacionAula[$altClave] ?? []);
                    $aulaItem = $aulaAlt !== null
                        ? $this->primeraAulaLibre($item['candidatos_aula'], $aulasPorCodigo, array_values(array_diff($ocupacionAula[$clave] ?? [], [$ocupante['id_aula']])))
                        : null;

                    if ($aulaAlt === null || $aulaItem === null) {
                        continue;
                    }

                    $this->moverBloqueExistente($ocupanteIndex, $altSlot, $aulaAlt, $detalles, $usoPorSlot, $ocupacionDocente, $ocupacionAula, $clave);
                    $this->colocarBloqueNuevo($item, $slot, $aulaItem, $detalles, $usoPorSlot, $ocupacionDocente, $ocupacionAula);

                    return true;
                }

                // Profundidad 2: altSlot esta ocupado por un tercer bloque;
                // solo se sigue la cadena si ese tercero tiene un slot
                // realmente vacio (no se encadena mas alla de esto).
                $tercero = $detalles[$usoPorSlot[$altClave]];

                foreach ($slots as $slotLibre) {
                    $claveLibre = $slotLibre['dia'].'|'.$slotLibre['inicio'];

                    if (in_array($claveLibre, [$clave, $altClave], true) || isset($usoPorSlot[$claveLibre])
                        || in_array($tercero['id_docente'], $ocupacionDocente[$claveLibre] ?? [], true)) {
                        continue;
                    }

                    $aulaTercero = $this->primeraAulaLibre($candidatosPorCurso[$tercero['id_curso']] ?? [$tercero['aula']], $aulasPorCodigo, $ocupacionAula[$claveLibre] ?? []);
                    $aulaOcupante = $aulaTercero !== null
                        ? $this->primeraAulaLibre($candidatosPorCurso[$ocupante['id_curso']] ?? [$ocupante['aula']], $aulasPorCodigo, array_values(array_diff($ocupacionAula[$altClave] ?? [], [$tercero['id_aula']])))
                        : null;
                    $aulaItem = $aulaOcupante !== null
                        ? $this->primeraAulaLibre($item['candidatos_aula'], $aulasPorCodigo, array_values(array_diff($ocupacionAula[$clave] ?? [], [$ocupante['id_aula']])))
                        : null;

                    if ($aulaTercero === null || $aulaOcupante === null || $aulaItem === null) {
                        continue;
                    }

                    $this->moverBloqueExistente($usoPorSlot[$altClave], $slotLibre, $aulaTercero, $detalles, $usoPorSlot, $ocupacionDocente, $ocupacionAula, $altClave);
                    $this->moverBloqueExistente($ocupanteIndex, $altSlot, $aulaOcupante, $detalles, $usoPorSlot, $ocupacionDocente, $ocupacionAula, $clave);
                    $this->colocarBloqueNuevo($item, $slot, $aulaItem, $detalles, $usoPorSlot, $ocupacionDocente, $ocupacionAula);

                    return true;
                }
            }
        }

        return false;
    }

    private function moverBloqueExistente(int $indice, array $nuevoSlot, Aula $aula, array &$detalles, array &$usoPorSlot, array &$ocupacionDocente, array &$ocupacionAula, string $claveVieja): void
    {
        $bloque = $detalles[$indice];

        $ocupacionDocente[$claveVieja] = array_values(array_diff($ocupacionDocente[$claveVieja] ?? [], [$bloque['id_docente']]));
        $ocupacionAula[$claveVieja] = array_values(array_diff($ocupacionAula[$claveVieja] ?? [], [$bloque['id_aula']]));
        unset($usoPorSlot[$claveVieja]);

        $claveNueva = $nuevoSlot['dia'].'|'.$nuevoSlot['inicio'];
        $detalles[$indice] = array_merge($bloque, [
            'dia' => $nuevoSlot['dia'],
            'hora_inicio' => $nuevoSlot['inicio'],
            'hora_fin' => $nuevoSlot['fin'],
            'id_aula' => $aula->id_aula,
            'aula' => $aula->codigo,
        ]);
        $ocupacionDocente[$claveNueva][] = $bloque['id_docente'];
        $ocupacionAula[$claveNueva][] = $aula->id_aula;
        $usoPorSlot[$claveNueva] = $indice;
    }

    private function colocarBloqueNuevo(array $item, array $slot, Aula $aula, array &$detalles, array &$usoPorSlot, array &$ocupacionDocente, array &$ocupacionAula): void
    {
        $clave = $slot['dia'].'|'.$slot['inicio'];

        $detalles[] = [
            'id_curso' => $item['id_curso'],
            'id_docente' => $item['id_docente'],
            'id_aula' => $aula->id_aula,
            'dia' => $slot['dia'],
            'hora_inicio' => $slot['inicio'],
            'hora_fin' => $slot['fin'],
            'aula' => $aula->codigo,
            'estado' => 'Confirmado',
            'fuente' => 'IA',
            'observacion' => $item['observacion'] ?? null,
        ];
        $ocupacionDocente[$clave][] = $item['id_docente'];
        $ocupacionAula[$clave][] = $aula->id_aula;
        $usoPorSlot[$clave] = count($detalles) - 1;
    }
}
