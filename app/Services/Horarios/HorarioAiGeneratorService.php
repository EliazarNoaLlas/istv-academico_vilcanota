<?php

namespace App\Services\Horarios;

use App\Models\Aula;
use App\Models\HorarioIaGenerado;
use App\Models\ProgramaEstudio;
use App\Services\Horarios\Providers\FakeHorarioProvider;
use App\Services\Horarios\Providers\GeminiHorarioProvider;
use App\Services\Horarios\Providers\GrokHorarioProvider;
use App\Services\Horarios\Providers\LlmHorarioProviderInterface;
use Illuminate\Support\Str;
use Throwable;

/**
 * Orquesta la generacion con IA: arma el contexto/prompt, llama al provider,
 * parsea, valida, repara si hace falta y solo entonces persiste. Nunca
 * guarda la respuesta cruda de la IA directamente en `horarios`.
 */
class HorarioAiGeneratorService
{
    public function __construct(
        private readonly HorarioAiPromptBuilderService $promptBuilder,
        private readonly HorarioAiResponseParserService $parser,
        private readonly HorarioValidationService $validacion,
        private readonly HorarioConflictService $conflictos,
        private readonly HorarioRepairService $repairService,
        private readonly HorarioPersistenceService $persistencia,
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
}
