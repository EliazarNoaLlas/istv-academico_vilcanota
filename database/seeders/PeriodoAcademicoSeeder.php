<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeriodoAcademicoSeeder extends Seeder
{
    public function run(): void
    {
        // El orden importa: replica el orden real de creacion en produccion
        // (id_periodo 1-4) para que un import de datos reales no choque por FK.
        $periodos = [
            ['codigo' => '2026-I', 'nombre' => 'Semestre Academico 2026-I', 'fecha_inicio' => '2026-03-16', 'fecha_fin' => '2026-07-31', 'estado' => 'ACTIVO'],
            ['codigo' => '2026-II', 'nombre' => 'Semestre Academico 2026-II', 'fecha_inicio' => '2026-08-17', 'fecha_fin' => '2026-12-18', 'estado' => 'PLANIFICADO'],
            ['codigo' => '2026-V', 'nombre' => 'Semestre 2026-V', 'fecha_inicio' => null, 'fecha_fin' => null, 'estado' => 'PLANIFICADO'],
            ['codigo' => '2026-III', 'nombre' => 'Semestre 2026-III', 'fecha_inicio' => null, 'fecha_fin' => null, 'estado' => 'PLANIFICADO'],
        ];

        foreach ($periodos as $periodo) {
            DB::table('periodos_academicos')->updateOrInsert(['codigo' => $periodo['codigo']], $periodo);
        }
    }
}
