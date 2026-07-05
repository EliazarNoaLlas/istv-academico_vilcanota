<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AulaSeeder extends Seeder
{
    public function run(): void
    {
        $aulas = [
            ['codigo' => 'A201', 'nombre' => 'Aula 201', 'tipo' => 'AULA', 'capacidad' => 35, 'ubicacion' => 'Pabellon A'],
            ['codigo' => 'A202', 'nombre' => 'Aula 202', 'tipo' => 'AULA', 'capacidad' => 35, 'ubicacion' => 'Pabellon A'],
            ['codigo' => 'A203', 'nombre' => 'Aula 203', 'tipo' => 'AULA', 'capacidad' => 30, 'ubicacion' => 'Pabellon A'],
            ['codigo' => 'LAB-COMP', 'nombre' => 'Laboratorio de Computo', 'tipo' => 'LABORATORIO', 'capacidad' => 28, 'ubicacion' => 'Pabellon B'],
            ['codigo' => 'LAB-REDES', 'nombre' => 'Laboratorio de Redes', 'tipo' => 'LABORATORIO', 'capacidad' => 24, 'ubicacion' => 'Pabellon B'],
        ];

        foreach ($aulas as $aula) {
            DB::table('aulas')->updateOrInsert(['codigo' => $aula['codigo']], $aula);
        }
    }
}
