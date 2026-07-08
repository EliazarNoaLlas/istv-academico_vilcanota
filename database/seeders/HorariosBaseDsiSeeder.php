<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\ProgramaEstudio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Version en seeder de database/migracion_datos_reales.sql (seccion 11,
 * HORARIOS): los 60 bloques reales de Semestre I y III de DSI, cargados
 * originalmente a mano via phpMyAdmin y nunca antes integrados al pipeline
 * de artisan migrate --seed. Sin esto, una instalacion nueva desde GitHub
 * arranca con la tabla horarios vacia y GenerarHorariosDsiSeeder no tiene
 * base contra la que evitar cruces al generar II/V/VI.
 *
 * Los nombres de curso se resuelven por consulta (collation
 * utf8mb4_unicode_ci, insensible a tildes) en vez de comparar strings en
 * PHP, porque CursoSeeder e ItinerarioDsiSeeder pueden dejar el nombre
 * acentuado o no segun el orden de ejecucion. Idempotente via
 * updateOrInsert por (id_curso, dia, hora_inicio).
 */
class HorariosBaseDsiSeeder extends Seeder
{
    /** [nombre_curso, codigo_docente, dia, hora_inicio, hora_fin, aula] */
    private const BLOQUES = [
        ['Aplicaciones web', 'DOC004', 'Lunes', '08:00:00', '08:45:00', 'A202'],
        ['Aplicaciones web', 'DOC004', 'Lunes', '08:45:00', '09:30:00', 'A203'],
        ['Aplicaciones web', 'DOC004', 'Lunes', '09:30:00', '10:15:00', 'A202'],
        ['Aplicaciones web', 'DOC004', 'Martes', '08:00:00', '08:45:00', 'A202'],
        ['Aplicaciones web', 'DOC004', 'Martes', '08:45:00', '09:30:00', 'A203'],
        ['Aplicaciones web', 'DOC004', 'Martes', '09:30:00', '10:15:00', 'A201'],
        ['Aplicaciones web', 'DOC004', 'Miércoles', '08:00:00', '08:45:00', 'A201'],
        ['Administración de sitios web', 'DOC003', 'Jueves', '09:30:00', '10:15:00', 'Invernadero'],
        ['Administración de sitios web', 'DOC003', 'Miércoles', '12:00:00', '12:45:00', 'Campo Experimental'],
        ['Administración de sitios web', 'DOC003', 'Jueves', '10:15:00', '11:00:00', 'A205'],
        ['Administración de sitios web', 'DOC003', 'Jueves', '08:45:00', '09:30:00', 'A205'],
        ['Administración de sitios web', 'DOC003', 'Miércoles', '11:15:00', '12:00:00', 'A205'],
        ['Administración de sitios web', 'DOC003', 'Martes', '12:00:00', '12:45:00', 'A203'],
        ['Administración de sitios web', 'DOC003', 'Lunes', '11:15:00', '12:00:00', 'Campo Experimental'],
        ['Administración de sitios web', 'DOC003', 'Viernes', '10:15:00', '11:00:00', 'A201'],
        ['Seguridad informática', 'DOC005', 'Martes', '11:15:00', '12:00:00', 'Lab. Cómputo'],
        ['Seguridad informática', 'DOC005', 'Jueves', '11:15:00', '12:00:00', 'A201'],
        ['Seguridad informática', 'DOC005', 'Jueves', '12:00:00', '12:45:00', 'A202'],
        ['Aplicaciones web', 'DOC004', 'Miércoles', '08:45:00', '09:30:00', 'A204'],
        ['Aplicaciones web', 'DOC004', 'Miércoles', '09:30:00', '10:15:00', 'A203'],
        ['Aplicaciones móviles', 'DOC002', 'Martes', '10:15:00', '11:00:00', 'Lab. Cómputo'],
        ['Aplicaciones móviles', 'DOC002', 'Miércoles', '10:15:00', '11:00:00', 'A204'],
        ['Aplicaciones móviles', 'DOC002', 'Lunes', '12:00:00', '12:45:00', 'Lab. Cómputo'],
        ['Aplicaciones móviles', 'DOC002', 'Viernes', '08:00:00', '08:45:00', 'A203'],
        ['Aplicaciones móviles', 'DOC002', 'Viernes', '08:45:00', '09:30:00', 'A204'],
        ['Aplicaciones móviles', 'DOC002', 'Viernes', '09:30:00', '10:15:00', 'A205'],
        ['Aplicaciones móviles', 'DOC002', 'Jueves', '08:00:00', '08:45:00', 'A205'],
        ['Inglés para la comunicación oral', 'DOC006', 'Viernes', '12:00:00', '12:45:00', 'A203'],
        ['Inglés para la comunicación oral', 'DOC006', 'Lunes', '10:15:00', '11:00:00', 'A204'],
        ['Inglés para la comunicación oral', 'DOC006', 'Viernes', '11:15:00', '12:00:00', 'A205'],
        ['Lógica de programación', 'DOC001', 'Lunes', '08:00:00', '08:45:00', 'A201'],
        ['Lógica de programación', 'DOC001', 'Lunes', '08:45:00', '09:30:00', 'A201'],
        ['Lógica de programación', 'DOC001', 'Lunes', '09:30:00', '10:15:00', 'A201'],
        ['Lógica de programación', 'DOC001', 'Martes', '08:00:00', '08:45:00', 'A203'],
        ['Lógica de programación', 'DOC001', 'Jueves', '08:45:00', '09:30:00', 'Lab. Cómputo'],
        ['Diseño de software', 'DOC007', 'Miércoles', '08:00:00', '08:45:00', 'A202'],
        ['Diseño de software', 'DOC007', 'Miércoles', '08:45:00', '09:30:00', 'A202'],
        ['Diseño de software', 'DOC007', 'Martes', '09:30:00', '10:15:00', 'Lab. Cómputo'],
        ['Diseño de software', 'DOC007', 'Lunes', '12:00:00', '12:45:00', 'A203'],
        ['Diseño de software', 'DOC007', 'Martes', '10:15:00', '11:00:00', 'A201'],
        ['Diseño de software', 'DOC007', 'Miércoles', '09:30:00', '10:15:00', 'Invernadero'],
        ['Modelamiento de bases de datos', 'DOC005', 'Martes', '08:45:00', '09:30:00', 'Campo Experimental'],
        ['Modelamiento de bases de datos', 'DOC005', 'Jueves', '08:00:00', '08:45:00', 'A203'],
        ['Modelamiento de bases de datos', 'DOC005', 'Viernes', '11:15:00', '12:00:00', 'Campo Experimental'],
        ['Modelamiento de bases de datos', 'DOC005', 'Miércoles', '11:15:00', '12:00:00', 'Invernadero'],
        ['Modelamiento de bases de datos', 'DOC005', 'Miércoles', '12:00:00', '12:45:00', 'Invernadero'],
        ['Modelamiento de bases de datos', 'DOC005', 'Lunes', '11:15:00', '12:00:00', 'A202'],
        ['Modelamiento de bases de datos', 'DOC005', 'Viernes', '12:00:00', '12:45:00', 'Lab. Redes'],
        ['Técnicas de programación', 'DOC006', 'Martes', '11:15:00', '12:00:00', 'A204'],
        ['Técnicas de programación', 'DOC006', 'Miércoles', '10:15:00', '11:00:00', 'A201'],
        ['Técnicas de programación', 'DOC006', 'Martes', '12:00:00', '12:45:00', 'Lab. Cómputo'],
        ['Técnicas de programación', 'DOC006', 'Jueves', '11:15:00', '12:00:00', 'A203'],
        ['Técnicas de programación', 'DOC006', 'Jueves', '12:00:00', '12:45:00', 'Invernadero'],
        ['Técnicas de programación', 'DOC006', 'Viernes', '10:15:00', '11:00:00', 'A205'],
        ['Comunicación oral', 'DOC002', 'Lunes', '10:15:00', '11:00:00', 'Campo Experimental'],
        ['Comunicación oral', 'DOC002', 'Jueves', '09:30:00', '10:15:00', 'A202'],
        ['Comunicación oral', 'DOC002', 'Jueves', '10:15:00', '11:00:00', 'A201'],
        ['Aplicaciones en internet', 'DOC003', 'Viernes', '08:45:00', '09:30:00', 'A201'],
        ['Aplicaciones en internet', 'DOC003', 'Viernes', '08:00:00', '08:45:00', 'A202'],
        ['Aplicaciones en internet', 'DOC003', 'Viernes', '09:30:00', '10:15:00', 'A203'],
    ];

    private array $cacheCursos = [];

    public function run(): void
    {
        $idPrograma = ProgramaEstudio::where('codigo', 'DSI')->value('id_programa');

        if (! $idPrograma) {
            return;
        }

        $docentesPorCodigo = DB::table('docentes')->pluck('id_docente', 'codigo_docente');

        foreach (self::BLOQUES as [$nombreCurso, $codigoDocente, $dia, $horaInicio, $horaFin, $aula]) {
            $idCurso = $this->idCurso($idPrograma, $nombreCurso);
            $idDocente = $docentesPorCodigo[$codigoDocente] ?? null;

            if (! $idCurso || ! $idDocente) {
                continue;
            }

            DB::table('horarios')->updateOrInsert(
                ['id_curso' => $idCurso, 'dia' => $dia, 'hora_inicio' => $horaInicio],
                [
                    'id_docente' => $idDocente,
                    'hora_fin' => $horaFin,
                    'aula' => $aula,
                    'estado' => 'Confirmado',
                    'fuente' => 'MANUAL',
                ]
            );
        }
    }

    /** Collation utf8mb4_unicode_ci de la columna: el WHERE es insensible a tildes, por eso no importa como quedo guardado el nombre. */
    private function idCurso(int $idPrograma, string $nombre): ?int
    {
        return $this->cacheCursos[$nombre] ??= Curso::query()
            ->where('id_programa', $idPrograma)
            ->where('nombre_curso', $nombre)
            ->min('id_curso');
    }
}
