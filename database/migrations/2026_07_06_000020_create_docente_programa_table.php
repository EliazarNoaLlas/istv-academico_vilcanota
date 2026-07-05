<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docente_programa', function (Blueprint $table) {
            $table->increments('id_docente_programa');

            $table->unsignedInteger('id_docente');
            $table->unsignedInteger('id_programa');

            $table->enum('tipo_asignacion', ['ESPECIFICO', 'GENERAL'])->default('ESPECIFICO');
            $table->boolean('es_principal')->default(false);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');

            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('observacion', 255)->nullable();

            $table->timestamps();

            $table->foreign('id_docente')->references('id_docente')->on('docentes')->cascadeOnDelete();
            $table->foreign('id_programa')->references('id_programa')->on('programas_estudio')->cascadeOnDelete();

            $table->unique(['id_docente', 'id_programa'], 'uk_docente_programa');
            $table->index(['id_programa', 'estado']);
            $table->index(['id_docente', 'estado']);
        });

        $this->poblarDesdeAsignacionesActuales();
    }

    /**
     * Deriva docente_programa desde las asignaciones reales en cursos.id_docente
     * + cursos.id_programa: un docente que solo dicta en un programa queda
     * ESPECIFICO/principal; uno que dicta en varios queda GENERAL en todos,
     * marcando principal el programa donde tiene mas cursos asignados.
     */
    private function poblarDesdeAsignacionesActuales(): void
    {
        $asignaciones = DB::table('cursos')
            ->select('id_docente', 'id_programa', DB::raw('count(*) as total_cursos'))
            ->whereNotNull('id_docente')
            ->whereNotNull('id_programa')
            ->groupBy('id_docente', 'id_programa')
            ->get()
            ->groupBy('id_docente');

        foreach ($asignaciones as $idDocente => $programas) {
            $esGeneral = $programas->count() > 1;
            $principal = $programas->sortByDesc('total_cursos')->first();

            foreach ($programas as $programa) {
                DB::table('docente_programa')->insertOrIgnore([
                    'id_docente' => $idDocente,
                    'id_programa' => $programa->id_programa,
                    'tipo_asignacion' => $esGeneral ? 'GENERAL' : 'ESPECIFICO',
                    'es_principal' => $programa->id_programa === $principal->id_programa,
                    'estado' => 'ACTIVO',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('docentes')->where('id_docente', $idDocente)->update([
                'tipo_docente' => $esGeneral ? 'GENERAL' : 'ESPECIFICO',
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('docente_programa');
    }
};
