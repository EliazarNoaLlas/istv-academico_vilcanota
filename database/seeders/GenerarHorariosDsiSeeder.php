<?php

namespace Database\Seeders;

use App\Models\PeriodoAcademico;
use App\Models\ProgramaEstudio;
use App\Services\Horarios\HorarioAiGeneratorService;
use Illuminate\Database\Seeder;

/**
 * Fase 3/3.2: genera los horarios de II, IV, V y VI llamando al servicio
 * real (HorarioAiGeneratorService::generarSemestresPendientesDsi), no
 * reimplementando el algoritmo aqui. Debe correr DESPUES de
 * HorariosBaseDsiSeeder (I y III ya deben existir: el generador evita
 * cruces contra ellos) y de la correccion/reasignacion de cursos. Es
 * idempotente porque generarSemestreDsi() devuelve HORARIO_EXISTENTE y no
 * duplica si el semestre ya tiene bloques.
 *
 * IV puede quedar incompleto (ver conversacion Fase 3.2): dos de sus
 * docentes coinciden con otros semestres en el unico slot que le falta.
 * Queda documentado como BORRADOR en horarios_ia_generados para completar
 * a mano desde el editor; no es un error de este seeder.
 */
class GenerarHorariosDsiSeeder extends Seeder
{
    public function run(): void
    {
        $idPrograma = ProgramaEstudio::where('codigo', 'DSI')->value('id_programa');
        $idPeriodo = PeriodoAcademico::where('estado', 'ACTIVO')->value('id_periodo');

        if (! $idPrograma || ! $idPeriodo) {
            return;
        }

        $resultado = app(HorarioAiGeneratorService::class)->generarSemestresPendientesDsi($idPrograma, $idPeriodo);

        $generados = implode(', ', $resultado['resumen_global']['semestres_generados'] ?? []) ?: 'ninguno';
        $this->command?->info("Horarios DSI generados para: {$generados}.");

        foreach ($resultado['resultados'] as $r) {
            if (! $r['ok'] && $r['estado'] !== 'HORARIO_EXISTENTE') {
                $this->command?->warn("Semestre {$r['semestre']}: {$r['estado']} — {$r['mensaje']}");
            }
        }
    }
}
