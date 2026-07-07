<?php

namespace App\Services\Horarios;

use App\Models\Aula;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\Horario;

/**
 * Arma el prompt para el LLM y el "contexto de datos" que lo acompana. El
 * contexto se serializa dentro de marcadores fijos para que
 * FakeHorarioProvider (uso local/pruebas, sin llamar a ninguna API) pueda
 * releerlo con parseContexto() y generar una propuesta usando los mismos
 * IDs reales, sin duplicar las consultas a la base de datos.
 */
class HorarioAiPromptBuilderService
{
    public const MARCADOR_INICIO = '### CONTEXTO_DATOS_JSON_INICIO';
    public const MARCADOR_FIN = '### CONTEXTO_DATOS_JSON_FIN';

    /**
     * @param array{id_programa:int,id_periodo:int,semestre:?string} $filtro
     * @return array{prompt:string, contexto:array<string,mixed>}
     */
    public function construir(array $filtro): array
    {
        $contexto = $this->contexto($filtro);
        $prompt = $this->construirPrompt($contexto);

        return ['prompt' => $prompt, 'contexto' => $contexto];
    }

    /** Contexto de datos reales, sin el prompt. Lo reutiliza HorarioAiGeneratorService::reparar() para trabajar con el estado actual de la BD (no una foto vieja guardada en metadata_json). */
    public function contexto(array $filtro): array
    {
        $cursos = Curso::query()
            ->where('id_programa', $filtro['id_programa'])
            ->where('estado', 'ACTIVO')
            ->when($filtro['semestre'] ?? null, fn($q) => $q->where('semestre', $filtro['semestre']))
            ->get()
            ->map(fn(Curso $curso) => [
                'id_curso' => $curso->id_curso,
                'nombre_curso' => $curso->nombre_curso,
                'modulo' => $curso->modulo,
                'semestre' => $curso->semestre,
                'id_docente' => $curso->id_docente,
                'horas_teoria' => (float)$curso->horas_teoria,
                'horas_practica' => (float)$curso->horas_practica,
                'total_horas' => (float)$curso->total_horas,
            ])->values()->all();

        $idDocentesDelPrograma = array_values(array_unique(array_filter(array_column($cursos, 'id_docente'))));

        $docentes = Docente::query()
            ->where('estado_academico', 'ACTIVO')
            ->where(function ($q) use ($idDocentesDelPrograma, $filtro) {
                $q->whereIn('id_docente', $idDocentesDelPrograma)
                    ->orWhereHas('asignacionesPrograma', fn($qq) => $qq->where('id_programa', $filtro['id_programa']));
            })
            ->get()
            ->map(fn(Docente $docente) => [
                'id_docente' => $docente->id_docente,
                'especialidad' => $docente->especialidad,
                'tipo_docente' => $docente->tipo_docente,
                'carga_actual_bloques' => Horario::where('id_docente', $docente->id_docente)
                    ->where('id_periodo', $filtro['id_periodo'])
                    ->count(),
            ])->values()->all();

        $aulas = Aula::where('estado', 'DISPONIBLE')
            ->get()
            ->map(fn(Aula $aula) => [
                'id_aula' => $aula->id_aula,
                'codigo' => $aula->codigo,
                'tipo' => $aula->tipo,
                'capacidad' => $aula->capacidad,
            ])->values()->all();

        $catalogo = app(HorarioCatalogService::class)->obtener();
        $bloques = array_values(array_filter($catalogo['bloques_horario'], fn($b) => empty($b['receso'])));

        return [
            'id_programa' => $filtro['id_programa'],
            'id_periodo' => $filtro['id_periodo'],
            'semestre' => $filtro['semestre'] ?? null,
            'cursos' => $cursos,
            'docentes' => $docentes,
            'aulas' => $aulas,
            'dias' => $catalogo['dias'],
            'bloques' => $bloques,
            'docente_max_bloques' => (int)config('services.horarios_ai.docente_max_bloques', 20),
        ];
    }

    private function construirPrompt(array $contexto): string
    {
        $json = json_encode($contexto, JSON_UNESCAPED_UNICODE);
        $marcadorInicio = self::MARCADOR_INICIO;
        $marcadorFin = self::MARCADOR_FIN;

        return <<<PROMPT
Eres un asistente que genera horarios academicos para un instituto tecnologico peruano.

Debes devolver EXCLUSIVAMENTE un JSON valido, sin texto adicional antes o despues, con esta forma exacta:
{
  "estado": "GENERADO",
  "detalles": [
    {"id_curso": 1, "id_docente": 2, "id_aula": 3, "dia": "LUNES", "hora_inicio": "08:00", "hora_fin": "08:45", "tipo": "TEORIA"}
  ],
  "observaciones": [],
  "conflictos": []
}

Reglas obligatorias (no negociables):
- Usa UNICAMENTE los id_curso, id_docente e id_aula que aparecen en el contexto de datos. Esta prohibido inventar IDs.
- Un docente no puede tener dos cursos el mismo dia y hora.
- Un aula no puede ser usada por dos cursos el mismo dia y hora.
- Ningun docente debe superar {$contexto['docente_max_bloques']} bloques academicos semanales (sumando carga_actual_bloques + lo que le asignes).
- No programes clases en el bloque de receso (11:00-11:15).
- Si un curso tiene horas_practica > 0, prefiere un aula tipo LABORATORIO o TALLER cuando exista una disponible.
- Un curso debe cubrir aproximadamente total_horas bloques academicos (1 bloque = 1 hora academica de 45 minutos).
- Si un curso ya tiene id_docente asignado en el contexto, respeta ese docente; si es null, elige el docente compatible con menor carga_actual_bloques.
- Distribuye los cursos entre los dias de la semana, evitando huecos innecesarios.

Contexto de datos (cursos, docentes, aulas, dias y bloques disponibles reales):
{$marcadorInicio}
{$json}
{$marcadorFin}

Responde solo con el JSON pedido.
PROMPT;
    }

    /** Reextrae el contexto embebido en el prompt (lo usa FakeHorarioProvider). */
    public function parseContexto(string $prompt): array
    {
        $inicio = strpos($prompt, self::MARCADOR_INICIO);
        $fin = strpos($prompt, self::MARCADOR_FIN);

        if ($inicio === false || $fin === false || $fin <= $inicio) {
            return [];
        }

        $bloque = substr($prompt, $inicio + strlen(self::MARCADOR_INICIO), $fin - ($inicio + strlen(self::MARCADOR_INICIO)));
        $datos = json_decode(trim($bloque), true);

        return is_array($datos) ? $datos : [];
    }
}
