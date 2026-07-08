<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\ProgramaEstudio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Docentes de Desarrollo de Sistemas (usuarios + docentes) y su asignacion
 * a los cursos de semestres I-V. Nunca antes estuvo en un seeder: solo
 * existia en database/migracion_datos_reales.sql (importado a mano) mas
 * Emiliano Mendoza/Vladimir Florez, que ni siquiera estaban ahi. Sin esto,
 * una instalacion nueva desde GitHub llega a "cursos sin docente" en TODOS
 * los semestres y los seeders de horarios (que dependen de que los cursos
 * ya tengan docente) no tienen nada que generar.
 *
 * Los 7 primeros son los del dump original (DOC001-DOC007); los 2
 * ultimos son los agregados en Fase 2 de la correccion de horarios.
 */
class DocentesDsiSeeder extends Seeder
{
    /** [usuario, correo, nombres, apellidos, dni, codigo_docente, especialidad] */
    private const DOCENTES = [
        ['d.huaylla', 'd.huaylla@istv.edu.pe', 'Diana', 'Huaylla', '00000001', 'DOC001', 'Desarrollo de Sistemas'],
        ['j.barrientos', 'j.barrientos@istv.edu.pe', 'Jhon', 'Barrientos Ferro', '00000002', 'DOC002', 'Desarrollo de Sistemas'],
        ['h.palomino', 'h.palomino@istv.edu.pe', 'Hernan', 'Palomino', '00000003', 'DOC003', 'Desarrollo de Sistemas'],
        ['r.jara', 'r.jara@istv.edu.pe', 'Rosa Luz', 'Jara', '00000004', 'DOC004', 'Desarrollo de Sistemas'],
        ['f.quispe', 'f.quispe@istv.edu.pe', 'Fredy', 'Quispe', '00000005', 'DOC005', 'Desarrollo de Sistemas'],
        ['p.lech', 'p.lech@istv.edu.pe', 'Pavel', 'Lech', '00000006', 'DOC006', 'Desarrollo de Sistemas'],
        ['f.cornejo', 'f.cornejo@istv.edu.pe', 'Fernando', 'Cornejo', '00000007', 'DOC007', 'Desarrollo de Sistemas'],
        ['emiliano', 'emilianoiestv@gmail.com', 'Emiliano', 'Mendoza', '74673465', 'DOC013', 'Desarrollo de Software'],
        ['vladimir', 'vladimiriestv@gmail.com', 'Vladimir', 'Florez', '95544335', 'DOC014', 'Desarrollo de Software'],
    ];

    /** [nombre_curso, semestre, codigo_docente] — asignacion original de I-V (VI lo asigna CorreccionCursosDsiSeeder). */
    private const ASIGNACIONES_I_A_V = [
        ['Lógica de programación', 'I', 'DOC001'],
        ['Diseño de software', 'I', 'DOC007'],
        ['Modelamiento de bases de datos', 'I', 'DOC005'],
        ['Técnicas de programación', 'I', 'DOC006'],
        ['Comunicación oral', 'I', 'DOC002'],
        ['Aplicaciones en internet', 'I', 'DOC003'],
        ['Algoritmos y estructuras de datos', 'II', 'DOC005'],
        ['Diseño web', 'II', 'DOC002'],
        ['Gestión de base de datos', 'II', 'DOC003'],
        ['Programación orientada a objetos', 'II', 'DOC006'],
        ['Interpretación y producción de textos', 'II', 'DOC001'],
        ['Ofimática', 'II', 'DOC007'],
        ['Administración de sitios web', 'III', 'DOC003'],
        ['Seguridad informática', 'III', 'DOC005'],
        ['Aplicaciones web', 'III', 'DOC004'],
        ['Aplicaciones móviles', 'III', 'DOC002'],
        ['Inglés para la comunicación oral', 'III', 'DOC006'],
        ['Lenguaje de programación concurrente', 'IV', 'DOC003'],
        ['Lenguaje de programación web dinámico', 'IV', 'DOC001'],
        ['Modelamiento de software de entretenimiento', 'IV', 'DOC004'],
        ['Comprensión y redacción en inglés', 'IV', 'DOC005'],
        ['Base de datos no relacionales', 'IV', 'DOC004'], // el SQL viejo decia V; ItinerarioDsiSeeder lo corrige a IV (ciclo oficial), ver Fase 1
        ['Gestión de proyectos de TI', 'V', 'DOC002'],
        ['Pruebas y calidad del software', 'V', 'DOC001'],
        ['Inteligencia de negocios', 'V', 'DOC006'],
        ['Gestión de servicios de TI', 'V', 'DOC003'],
        ['Fundamentos de innovación tecnológica', 'V', 'DOC007'],
        ['Comportamiento ético', 'V', 'DOC005'],
    ];

    public function run(): void
    {
        $idRolDocente = DB::table('roles')->where('codigo', 'docente')->value('id_rol');
        $idPrograma = ProgramaEstudio::where('codigo', 'DSI')->value('id_programa');

        if (! $idRolDocente || ! $idPrograma) {
            return;
        }

        foreach (self::DOCENTES as [$usuario, $correo, $nombres, $apellidos, $dni, $codigoDocente, $especialidad]) {
            $yaExiste = DB::table('usuarios')->where('usuario', $usuario)->exists();
            $password = Str::password(16);

            $idUsuario = DB::table('usuarios')->where('usuario', $usuario)->value('id_usuario');

            if ($idUsuario) {
                DB::table('usuarios')->where('id_usuario', $idUsuario)->update([
                    'id_rol' => $idRolDocente,
                    'correo' => $correo,
                    'nombres' => $nombres,
                    'apellidos' => $apellidos,
                    'dni' => $dni,
                    'estado' => 'ACTIVO',
                ]);
            } else {
                $idUsuario = DB::table('usuarios')->insertGetId([
                    'id_rol' => $idRolDocente,
                    'usuario' => $usuario,
                    'correo' => $correo,
                    'password_hash' => Hash::make($password),
                    'password_algoritmo' => 'bcrypt',
                    'nombres' => $nombres,
                    'apellidos' => $apellidos,
                    'dni' => $dni,
                    'estado' => 'ACTIVO',
                ]);
            }

            if (! $yaExiste) {
                $this->command?->warn("Cuenta docente creada: {$usuario} / contraseña temporal: {$password}");
            }

            DB::table('docentes')->updateOrInsert(
                ['codigo_docente' => $codigoDocente],
                [
                    'id_usuario' => $idUsuario,
                    'especialidad' => $especialidad,
                    'tipo_docente' => 'ESPECIFICO',
                    'estado_academico' => 'ACTIVO',
                ]
            );
        }

        $docentesPorCodigo = DB::table('docentes')->pluck('id_docente', 'codigo_docente');

        foreach (self::ASIGNACIONES_I_A_V as [$nombreCurso, $semestre, $codigoDocente]) {
            $idDocente = $docentesPorCodigo[$codigoDocente] ?? null;

            if (! $idDocente) {
                continue;
            }

            Curso::query()
                ->where('id_programa', $idPrograma)
                ->where('semestre', $semestre)
                ->where('nombre_curso', $nombreCurso)
                ->whereNull('id_docente')
                ->update(['id_docente' => $idDocente]);
        }
    }
}
