<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Reasigna el curso 26 (Gestion de servicios de TI, semestre V, programa
 * DSI) de Hernan Palomino (id_docente 3) a Emiliano Mendoza (id_docente 11).
 * Hernan quedaba con 33 horas_ud semanales repartidas en 5 semestres (I, II,
 * III, IV, V), por encima de los 30 slots fisicos de la semana: un cuello de
 * botella matematico que Fase 3 no puede resolver generando horarios, solo
 * reasignando el curso. Emiliano es Desarrollo de Software (area compatible
 * con DSI) y tenia la carga mas baja del programa. Idempotente.
 */
class ReasignarGestionServiciosTiSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cursos')
            ->where('id_curso', 26)
            ->where('id_programa', 1)
            ->where('id_docente', 3)
            ->update(['id_docente' => 11]);
    }
}
