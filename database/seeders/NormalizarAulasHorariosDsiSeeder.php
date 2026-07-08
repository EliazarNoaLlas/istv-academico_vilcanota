<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Normaliza id_aula/aula en los horarios existentes del programa DSI que
 * BackfillHorariosDsiSeeder dejo en NULL por no coincidir textualmente con
 * ninguna aula real ("Lab. Cómputo", "A204", "A205", "Invernadero", "Campo
 * Experimental"...). No toca id_curso, id_docente, dia, hora_inicio,
 * hora_fin ni la distribucion de bloques de I y III: solo reescribe la
 * referencia de aula. Idempotente.
 */
class NormalizarAulasHorariosDsiSeeder extends Seeder
{
    private const ID_PROGRAMA_DSI = 1;

    /** Variantes de texto libre -> codigo real de aula (clave en minuscula/trim). */
    private const MAPEO_TEXTO_A_CODIGO = [
        'lab. cómputo' => 'LAB-COMP',
        'laboratorio de cómputo' => 'LAB-COMP',
        'laboratorio de computo' => 'LAB-COMP',
        'lab. redes' => 'LAB-REDES',
        'laboratorio de redes' => 'LAB-REDES',
        'a204' => 'A204',
        'a205' => 'A205',
    ];

    /** Textos que no son aulas reales de DSI (de otro programa/prueba): se reasignan, no se crean. */
    private const TEXTOS_NO_DSI = ['invernadero', 'campo experimental'];

    private const CANDIDATOS_LABORATORIO = ['LAB-COMP', 'LAB-REDES', 'LAB-SW'];
    private const CANDIDATOS_AULA = ['A201', 'A202', 'A203', 'A204', 'A205'];

    public function run(): void
    {
        DB::transaction(function () {
            $this->asegurarAulasReales();
            $aulasPorCodigo = DB::table('aulas')->pluck('id_aula', 'codigo');

            $this->mapearTextosConocidos($aulasPorCodigo);
            $this->reasignarTextosNoDsi($aulasPorCodigo);
        });
    }

    /** Garantiza que existan las aulas necesarias sin pisar datos de las que ya existen. */
    private function asegurarAulasReales(): void
    {
        $definiciones = [
            ['codigo' => 'A204', 'nombre' => 'Aula 204', 'tipo' => 'AULA', 'capacidad' => 35, 'ubicacion' => 'Pabellon A'],
            ['codigo' => 'A205', 'nombre' => 'Aula 205', 'tipo' => 'AULA', 'capacidad' => 35, 'ubicacion' => 'Pabellon A'],
            ['codigo' => 'LAB-COMP', 'nombre' => 'Laboratorio de Computo', 'tipo' => 'LABORATORIO', 'capacidad' => 28, 'ubicacion' => 'Pabellon B'],
            ['codigo' => 'LAB-REDES', 'nombre' => 'Laboratorio de Redes', 'tipo' => 'LABORATORIO', 'capacidad' => 24, 'ubicacion' => 'Pabellon B'],
            ['codigo' => 'LAB-SW', 'nombre' => 'Laboratorio de Software', 'tipo' => 'LABORATORIO', 'capacidad' => 28, 'ubicacion' => 'Pabellon B'],
        ];

        foreach ($definiciones as $aula) {
            $codigo = $aula['codigo'];
            unset($aula['codigo']);

            DB::table('aulas')->where('codigo', $codigo)->exists()
                || DB::table('aulas')->insert(array_merge($aula, ['codigo' => $codigo, 'estado' => 'DISPONIBLE']));
        }
    }

    /** Reemplaza abreviaturas/variantes conocidas por el codigo real (A204, A205, LAB-COMP, LAB-REDES). */
    private function mapearTextosConocidos($aulasPorCodigo): void
    {
        $filas = $this->horariosDsiSinAula();

        foreach ($filas as $fila) {
            $clave = mb_strtolower(trim($fila->aula));
            $codigo = self::MAPEO_TEXTO_A_CODIGO[$clave] ?? null;

            if (! $codigo || ! isset($aulasPorCodigo[$codigo])) {
                continue;
            }

            DB::table('horarios')->where('id_horario', $fila->id_horario)->update([
                'id_aula' => $aulasPorCodigo[$codigo],
                'aula' => $codigo,
            ]);
        }
    }

    /**
     * "Invernadero" y "Campo Experimental" no son aulas de DSI: se busca una
     * aula real libre en ese mismo dia/hora (laboratorio si el curso tiene
     * horas_practica > 0, aula comun si es teorico/transversal). Si ninguna
     * esta libre, se deja id_aula en NULL y se registra en observacion.
     */
    private function reasignarTextosNoDsi($aulasPorCodigo): void
    {
        $filas = $this->horariosDsiSinAula();

        if ($filas->isEmpty()) {
            return;
        }

        $ocupadas = DB::table('horarios')
            ->whereNotNull('id_aula')
            ->get(['id_aula', 'dia', 'hora_inicio'])
            ->map(fn ($r) => "{$r->id_aula}|{$r->dia}|{$r->hora_inicio}")
            ->flip();

        foreach ($filas as $fila) {
            $clave = mb_strtolower(trim($fila->aula));

            if (! in_array($clave, self::TEXTOS_NO_DSI, true)) {
                continue; // Texto no contemplado en la regla: se deja para revision manual.
            }

            $candidatos = $fila->horas_practica > 0 ? self::CANDIDATOS_LABORATORIO : self::CANDIDATOS_AULA;
            $asignada = null;

            foreach ($candidatos as $codigo) {
                if (! isset($aulasPorCodigo[$codigo])) {
                    continue;
                }

                $slot = "{$aulasPorCodigo[$codigo]}|{$fila->dia}|{$fila->hora_inicio}";

                if (! $ocupadas->has($slot)) {
                    $asignada = $codigo;
                    break;
                }
            }

            if ($asignada) {
                DB::table('horarios')->where('id_horario', $fila->id_horario)->update([
                    'id_aula' => $aulasPorCodigo[$asignada],
                    'aula' => $asignada,
                ]);
                $ocupadas->put("{$aulasPorCodigo[$asignada]}|{$fila->dia}|{$fila->hora_inicio}", true);
            } else {
                $nota = "Fase 2.5: sin aula libre para reemplazar '{$fila->aula}' el {$fila->dia} {$fila->hora_inicio}.";
                DB::table('horarios')->where('id_horario', $fila->id_horario)->update([
                    'observacion' => trim(($fila->observacion ?? '')." {$nota}"),
                ]);
                $this->command?->warn($nota." (id_horario={$fila->id_horario})");
            }
        }
    }

    private function horariosDsiSinAula()
    {
        return DB::table('horarios as h')
            ->join('cursos as c', 'c.id_curso', '=', 'h.id_curso')
            ->where('h.id_programa', self::ID_PROGRAMA_DSI)
            ->where('c.estado', '!=', 'ARCHIVADO')
            ->whereNull('c.deleted_at')
            ->whereNull('h.id_aula')
            ->whereNotNull('h.aula')
            ->select('h.id_horario', 'h.dia', 'h.hora_inicio', 'h.aula', 'h.observacion', 'c.horas_practica')
            ->get();
    }
}
