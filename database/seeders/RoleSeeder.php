<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['codigo' => 'director', 'nombre' => 'Director Academico', 'descripcion' => 'Acceso global a gestion academica'],
            ['codigo' => 'jua', 'nombre' => 'Jefe de Unidad Academica', 'descripcion' => 'Revision y aprobacion academica'],
            ['codigo' => 'coordinador', 'nombre' => 'Coordinador Academico', 'descripcion' => 'Gestion de cursos, docentes y seguimiento'],
            ['codigo' => 'docente', 'nombre' => 'Docente', 'descripcion' => 'Registro de notas, asistencia y portafolio'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(['codigo' => $role['codigo']], $role);
        }
    }
}
