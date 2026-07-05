<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgramaEstudioSeeder extends Seeder
{
    public function run(): void
    {
        $programas = [
            ['codigo' => 'DSI', 'nombre' => 'Desarrollo de Sistemas de Informacion', 'familia_profesional' => 'Computacion e Informatica'],
            ['codigo' => 'AGRO', 'nombre' => 'Produccion Agropecuaria', 'familia_profesional' => 'Actividades Agrarias'],
            ['codigo' => 'ENF', 'nombre' => 'Enfermeria Tecnica', 'familia_profesional' => 'Salud'],
            ['codigo' => 'CON', 'nombre' => 'Construccion Civil', 'familia_profesional' => 'Construccion'],
            ['codigo' => 'CTB', 'nombre' => 'Contabilidad', 'familia_profesional' => 'Administracion y Comercio'],
        ];

        foreach ($programas as $programa) {
            DB::table('programas_estudio')->updateOrInsert(['codigo' => $programa['codigo']], $programa);
        }
    }
}
