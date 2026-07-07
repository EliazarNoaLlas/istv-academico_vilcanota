<?php

namespace App\Console\Commands;

use App\Models\Curso;
use App\Models\ProgramaEstudio;
use Illuminate\Console\Command;

class InferirProgramaCursosCommand extends Command
{
    protected $signature = 'cursos:inferir-programa {--dry-run : Solo mostrar lo que se asignaria, sin guardar}';

    protected $description = 'Asigna cursos.id_programa comparando docentes.especialidad contra programas_estudio.nombre, solo cuando el match es unico y no ambiguo. Deja nulo lo que no se pueda inferir con seguridad.';

    public function handle(): int
    {
        $dryRun = (bool)$this->option('dry-run');
        $programas = ProgramaEstudio::all();

        $cursos = Curso::whereNull('id_programa')->with('docente')->get();
        $asignados = 0;
        $sinMatch = 0;

        foreach ($cursos as $curso) {
            $especialidad = $curso->docente?->especialidad;

            if (!$especialidad) {
                $sinMatch++;

                continue;
            }

            $coincidencias = $programas->filter(
                fn($p) => str_contains(mb_strtolower($p->nombre), mb_strtolower($especialidad))
                    || str_contains(mb_strtolower($especialidad), mb_strtolower($p->nombre))
            );

            if ($coincidencias->count() !== 1) {
                $this->warn("Curso #{$curso->id_curso} ({$curso->nombre_curso}): especialidad '{$especialidad}' no tiene un match unico, se deja sin asignar.");
                $sinMatch++;

                continue;
            }

            $programa = $coincidencias->first();
            $this->line("Curso #{$curso->id_curso} ({$curso->nombre_curso}) -> {$programa->nombre}");

            if (!$dryRun) {
                $curso->update(['id_programa' => $programa->id_programa]);
            }

            $asignados++;
        }

        $this->info(($dryRun ? '[dry-run] ' : '') . "Asignados: {$asignados}. Sin match seguro (quedan nulos): {$sinMatch}.");

        return self::SUCCESS;
    }
}
