<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Reasigna "Gestion de servicios de TI" (semestre V, programa DSI) de
 * Hernan Palomino (DOC003) a Emiliano Mendoza (DOC013). Hernan quedaba con
 * 33 horas_ud semanales repartidas en 5 semestres (I, II, III, IV, V), por
 * encima de los 30 slots fisicos de la semana: un cuello de botella
 * matematico que Fase 3 no puede resolver generando horarios, solo
 * reasignando el curso. Emiliano es Desarrollo de Software (area
 * compatible con DSI) y tenia la carga mas baja del programa. Resuelve por
 * nombre_curso/codigo_docente (no por id fijo) para funcionar en
 * cualquier base de datos. Idempotente.
 */
class ReasignarGestionServiciosTiSeeder extends Seeder
{
    private const ID_PROGRAMA_DSI = 1;

    public function run(): void
    {
        $idDocenteOrigen = DB::table('docentes')->where('codigo_docente', 'DOC003')->value('id_docente');
        $idDocenteDestino = DB::table('docentes')->where('codigo_docente', 'DOC013')->value('id_docente');

        if (! $idDocenteOrigen || ! $idDocenteDestino) {
            return;
        }

        DB::table('cursos')
            ->where('nombre_curso', 'Gestión de servicios de TI')
            ->where('id_programa', self::ID_PROGRAMA_DSI)
            ->where('semestre', 'V')
            ->where('id_docente', $idDocenteOrigen)
            ->update(['id_docente' => $idDocenteDestino]);
    }
}
