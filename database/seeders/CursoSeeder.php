<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CursoSeeder extends Seeder
{
    /**
     * Itinerario formativo de Desarrollo de Sistemas de Informacion (3 modulos,
     * semestres I-VI), tal como esta definido en el plan curricular institucional.
     */
    public function run(): void
    {
        $idPrograma = DB::table('programas_estudio')->where('nombre', 'Desarrollo de Sistemas de Informacion')->value('id_programa');

        if (!$idPrograma) {
            return;
        }

        $cursos = [
            // Modulo I
            ['nombre_curso' => 'Logica de programacion', 'modulo' => 'Modulo I', 'semestre' => 'I', 'creditos' => 5, 'horas_teoria' => 1, 'horas_practica' => 2, 'horas_ud' => 3, 'total_teoria' => 16, 'total_practica' => 64, 'total_horas' => 80],
            ['nombre_curso' => 'Diseno de software', 'modulo' => 'Modulo I', 'semestre' => 'I', 'creditos' => 6, 'horas_teoria' => 2, 'horas_practica' => 2, 'horas_ud' => 4, 'total_teoria' => 32, 'total_practica' => 64, 'total_horas' => 96],
            ['nombre_curso' => 'Modelamiento de bases de datos', 'modulo' => 'Modulo I', 'semestre' => 'I', 'creditos' => 7, 'horas_teoria' => 1, 'horas_practica' => 3, 'horas_ud' => 4, 'total_teoria' => 16, 'total_practica' => 96, 'total_horas' => 112],
            ['nombre_curso' => 'Tecnicas de programacion', 'modulo' => 'Modulo I', 'semestre' => 'I', 'creditos' => 6, 'horas_teoria' => 2, 'horas_practica' => 2, 'horas_ud' => 4, 'total_teoria' => 32, 'total_practica' => 64, 'total_horas' => 96],
            ['nombre_curso' => 'Comunicacion oral', 'modulo' => 'Modulo I', 'semestre' => 'I', 'creditos' => 3, 'horas_teoria' => 1, 'horas_practica' => 1, 'horas_ud' => 2, 'total_teoria' => 16, 'total_practica' => 32, 'total_horas' => 48],
            ['nombre_curso' => 'Aplicaciones en internet', 'modulo' => 'Modulo I', 'semestre' => 'I', 'creditos' => 3, 'horas_teoria' => 1, 'horas_practica' => 1, 'horas_ud' => 2, 'total_teoria' => 16, 'total_practica' => 32, 'total_horas' => 48],
            ['nombre_curso' => 'Algoritmos y estructuras de datos', 'modulo' => 'Modulo I', 'semestre' => 'II', 'creditos' => 6, 'horas_teoria' => 2, 'horas_practica' => 2, 'horas_ud' => 6, 'total_teoria' => 32, 'total_practica' => 64, 'total_horas' => 96],
            ['nombre_curso' => 'Diseno web', 'modulo' => 'Modulo I', 'semestre' => 'II', 'creditos' => 6, 'horas_teoria' => 2, 'horas_practica' => 2, 'horas_ud' => 6, 'total_teoria' => 32, 'total_practica' => 64, 'total_horas' => 96],
            ['nombre_curso' => 'Gestion de base de datos', 'modulo' => 'Modulo I', 'semestre' => 'II', 'creditos' => 7, 'horas_teoria' => 1, 'horas_practica' => 3, 'horas_ud' => 7, 'total_teoria' => 16, 'total_practica' => 96, 'total_horas' => 112],
            ['nombre_curso' => 'Programacion orientada a objetos', 'modulo' => 'Modulo I', 'semestre' => 'II', 'creditos' => 5, 'horas_teoria' => 1, 'horas_practica' => 2, 'horas_ud' => 5, 'total_teoria' => 16, 'total_practica' => 64, 'total_horas' => 80],
            ['nombre_curso' => 'Interpretacion y produccion de textos', 'modulo' => 'Modulo I', 'semestre' => 'II', 'creditos' => 3, 'horas_teoria' => 1, 'horas_practica' => 1, 'horas_ud' => 3, 'total_teoria' => 16, 'total_practica' => 32, 'total_horas' => 48],
            ['nombre_curso' => 'Ofimatica', 'modulo' => 'Modulo I', 'semestre' => 'II', 'creditos' => 3, 'horas_teoria' => 1, 'horas_practica' => 1, 'horas_ud' => 3, 'total_teoria' => 16, 'total_practica' => 32, 'total_horas' => 48],

            // Modulo II
            ['nombre_curso' => 'Administracion de sitios web', 'modulo' => 'Modulo II', 'semestre' => 'III', 'creditos' => 8, 'horas_teoria' => 2, 'horas_practica' => 3, 'horas_ud' => 5, 'total_teoria' => 32, 'total_practica' => 96, 'total_horas' => 128],
            ['nombre_curso' => 'Seguridad informatica', 'modulo' => 'Modulo II', 'semestre' => 'III', 'creditos' => 3, 'horas_teoria' => 1, 'horas_practica' => 1, 'horas_ud' => 2, 'total_teoria' => 16, 'total_practica' => 32, 'total_horas' => 48],
            ['nombre_curso' => 'Aplicaciones web', 'modulo' => 'Modulo II', 'semestre' => 'III', 'creditos' => 9, 'horas_teoria' => 1, 'horas_practica' => 4, 'horas_ud' => 5, 'total_teoria' => 16, 'total_practica' => 128, 'total_horas' => 144],
            ['nombre_curso' => 'Aplicaciones moviles', 'modulo' => 'Modulo II', 'semestre' => 'III', 'creditos' => 7, 'horas_teoria' => 1, 'horas_practica' => 3, 'horas_ud' => 4, 'total_teoria' => 16, 'total_practica' => 96, 'total_horas' => 112],
            ['nombre_curso' => 'Ingles para la comunicacion oral', 'modulo' => 'Modulo II', 'semestre' => 'III', 'creditos' => 3, 'horas_teoria' => 1, 'horas_practica' => 1, 'horas_ud' => 2, 'total_teoria' => 16, 'total_practica' => 32, 'total_horas' => 48],
            ['nombre_curso' => 'Lenguaje de programacion concurrente', 'modulo' => 'Modulo II', 'semestre' => 'IV', 'creditos' => 8, 'horas_teoria' => 2, 'horas_practica' => 3, 'horas_ud' => 8, 'total_teoria' => 32, 'total_practica' => 96, 'total_horas' => 128],
            ['nombre_curso' => 'Lenguaje de programacion web dinamico', 'modulo' => 'Modulo II', 'semestre' => 'IV', 'creditos' => 8, 'horas_teoria' => 2, 'horas_practica' => 3, 'horas_ud' => 8, 'total_teoria' => 32, 'total_practica' => 96, 'total_horas' => 128],
            ['nombre_curso' => 'Modelamiento de software de entretenimiento', 'modulo' => 'Modulo II', 'semestre' => 'IV', 'creditos' => 5, 'horas_teoria' => 1, 'horas_practica' => 2, 'horas_ud' => 5, 'total_teoria' => 16, 'total_practica' => 64, 'total_horas' => 80],
            ['nombre_curso' => 'Comprension y redaccion en ingles', 'modulo' => 'Modulo II', 'semestre' => 'IV', 'creditos' => 2, 'horas_teoria' => 0, 'horas_practica' => 1, 'horas_ud' => 2, 'total_teoria' => 0, 'total_practica' => 32, 'total_horas' => 32],
            ['nombre_curso' => 'Base de datos no relacionales', 'modulo' => 'Modulo II', 'semestre' => 'V', 'creditos' => 7, 'horas_teoria' => 1, 'horas_practica' => 3, 'horas_ud' => 7, 'total_teoria' => 16, 'total_practica' => 96, 'total_horas' => 112],

            // Modulo III
            ['nombre_curso' => 'Gestion de proyectos de TI', 'modulo' => 'Modulo III', 'semestre' => 'V', 'creditos' => 5, 'horas_teoria' => 1, 'horas_practica' => 2, 'horas_ud' => 5, 'total_teoria' => 16, 'total_practica' => 64, 'total_horas' => 80],
            ['nombre_curso' => 'Pruebas y calidad del software', 'modulo' => 'Modulo III', 'semestre' => 'V', 'creditos' => 7, 'horas_teoria' => 1, 'horas_practica' => 3, 'horas_ud' => 7, 'total_teoria' => 16, 'total_practica' => 96, 'total_horas' => 112],
            ['nombre_curso' => 'Inteligencia de negocios', 'modulo' => 'Modulo III', 'semestre' => 'V', 'creditos' => 5, 'horas_teoria' => 1, 'horas_practica' => 2, 'horas_ud' => 5, 'total_teoria' => 16, 'total_practica' => 64, 'total_horas' => 80],
            ['nombre_curso' => 'Gestion de servicios de TI', 'modulo' => 'Modulo III', 'semestre' => 'V', 'creditos' => 7, 'horas_teoria' => 1, 'horas_practica' => 3, 'horas_ud' => 7, 'total_teoria' => 16, 'total_practica' => 96, 'total_horas' => 112],
            ['nombre_curso' => 'Fundamentos de innovacion tecnologica', 'modulo' => 'Modulo III', 'semestre' => 'V', 'creditos' => 3, 'horas_teoria' => 1, 'horas_practica' => 1, 'horas_ud' => 3, 'total_teoria' => 16, 'total_practica' => 32, 'total_horas' => 48],
            ['nombre_curso' => 'Comportamiento etico', 'modulo' => 'Modulo III', 'semestre' => 'V', 'creditos' => 3, 'horas_teoria' => 1, 'horas_practica' => 1, 'horas_ud' => 3, 'total_teoria' => 16, 'total_practica' => 32, 'total_horas' => 48],
            ['nombre_curso' => 'Gestion de servidores', 'modulo' => 'Modulo III', 'semestre' => 'VI', 'creditos' => 5, 'horas_teoria' => 1, 'horas_practica' => 2, 'horas_ud' => 3, 'total_teoria' => 16, 'total_practica' => 64, 'total_horas' => 80],
            ['nombre_curso' => 'Gestion de redes informaticas', 'modulo' => 'Modulo III', 'semestre' => 'VI', 'creditos' => 7, 'horas_teoria' => 1, 'horas_practica' => 3, 'horas_ud' => 4, 'total_teoria' => 16, 'total_practica' => 96, 'total_horas' => 112],
            ['nombre_curso' => 'Soporte de auditoria de TI', 'modulo' => 'Modulo III', 'semestre' => 'VI', 'creditos' => 6, 'horas_teoria' => 2, 'horas_practica' => 2, 'horas_ud' => 4, 'total_teoria' => 32, 'total_practica' => 64, 'total_horas' => 96],
            ['nombre_curso' => 'Auditoria de software', 'modulo' => 'Modulo III', 'semestre' => 'VI', 'creditos' => 3, 'horas_teoria' => 1, 'horas_practica' => 1, 'horas_ud' => 2, 'total_teoria' => 16, 'total_practica' => 32, 'total_horas' => 48],
            ['nombre_curso' => 'Inteligencia artificial', 'modulo' => 'Modulo III', 'semestre' => 'VI', 'creditos' => 3, 'horas_teoria' => 1, 'horas_practica' => 1, 'horas_ud' => 2, 'total_teoria' => 16, 'total_practica' => 32, 'total_horas' => 48],
            ['nombre_curso' => 'Solucion de problemas', 'modulo' => 'Modulo III', 'semestre' => 'VI', 'creditos' => 3, 'horas_teoria' => 1, 'horas_practica' => 1, 'horas_ud' => 2, 'total_teoria' => 16, 'total_practica' => 32, 'total_horas' => 48],
            ['nombre_curso' => 'Innovacion tecnologica', 'modulo' => 'Modulo III', 'semestre' => 'VI', 'creditos' => 3, 'horas_teoria' => 1, 'horas_practica' => 1, 'horas_ud' => 2, 'total_teoria' => 16, 'total_practica' => 32, 'total_horas' => 48],
        ];

        foreach ($cursos as $curso) {
            DB::table('cursos')->updateOrInsert(
                ['nombre_curso' => $curso['nombre_curso']],
                [
                    ...$curso,
                    'id_programa' => $idPrograma,
                    'tipo_curso' => 'ESPECIFICO',
                    'estado' => 'ACTIVO',
                ]
            );
        }
    }
}
