<?php

namespace App\Services\Horarios\Providers;

use App\Services\Horarios\HorarioAiPromptBuilderService;

/**
 * Proveedor sin llamadas HTTP: relee el contexto de datos reales que
 * HorarioAiPromptBuilderService embebio en el prompt y arma una propuesta
 * simple con esos mismos IDs. Sirve para desarrollo local y para
 * HorarioAiGeneratorServiceTest sin gastar cuota de Gemini/Grok. Es
 * deliberadamente ingenuo (no evita todos los cruces): la validacion y
 * reparacion en PHP son las responsables de dejar el horario correcto.
 */
class FakeHorarioProvider implements LlmHorarioProviderInterface
{
    public function __construct(private readonly HorarioAiPromptBuilderService $promptBuilder) {}

    public function generar(string $prompt): string
    {
        $contexto = $this->promptBuilder->parseContexto($prompt);

        $cursos = $contexto['cursos'] ?? [];
        $docentes = $contexto['docentes'] ?? [];
        $aulas = $contexto['aulas'] ?? [];
        $dias = $contexto['dias'] ?? ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        $bloques = $contexto['bloques'] ?? [];

        $cursorPorDocente = [];
        $detalles = [];

        foreach ($cursos as $index => $curso) {
            $idDocente = $curso['id_docente'] ?? $this->docenteConMenorCarga($docentes);
            if ($idDocente === null || $aulas === [] || $bloques === [] || $dias === []) {
                continue;
            }

            $bloquesNecesarios = max(1, (int) ceil((float) ($curso['total_horas'] ?? 2)));
            $aula = $aulas[$index % count($aulas)];
            $cursor = $cursorPorDocente[$idDocente] ?? 0;

            for ($b = 0; $b < $bloquesNecesarios; $b++) {
                $slot = $cursor + $b;
                $dia = $dias[intdiv($slot, count($bloques)) % count($dias)];
                $bloque = $bloques[$slot % count($bloques)];

                $detalles[] = [
                    'id_curso' => $curso['id_curso'],
                    'id_docente' => $idDocente,
                    'id_aula' => $aula['id_aula'],
                    'dia' => mb_strtoupper($dia),
                    'hora_inicio' => $bloque['inicio'],
                    'hora_fin' => $bloque['fin'],
                    'tipo' => ($curso['horas_practica'] ?? 0) > 0 ? 'PRACTICA' : 'TEORIA',
                ];
            }

            $cursorPorDocente[$idDocente] = $cursor + $bloquesNecesarios;
        }

        return json_encode([
            'estado' => 'GENERADO',
            'detalles' => $detalles,
            'observaciones' => ['Propuesta generada por FakeHorarioProvider (sin llamada externa).'],
            'conflictos' => [],
        ], JSON_UNESCAPED_UNICODE);
    }

    private function docenteConMenorCarga(array $docentes): ?int
    {
        if ($docentes === []) {
            return null;
        }

        usort($docentes, fn ($a, $b) => ($a['carga_actual_bloques'] ?? 0) <=> ($b['carga_actual_bloques'] ?? 0));

        return $docentes[0]['id_docente'] ?? null;
    }
}
