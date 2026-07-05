<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crea un esquema minimo en sqlite :memory: con solo las tablas que tocan
 * los servicios de Horarios IA. No se usa RefreshDatabase con las
 * migraciones reales porque el stack completo de migraciones del proyecto
 * (anterior a esta funcionalidad, ver 2026_07_05_000000_move_docente_...)
 * ya falla sobre sqlite por columnas MySQL-especificas; corregir eso queda
 * fuera del alcance de esta tarea. Estas tablas replican solo las columnas
 * que las queries de Horarios IA realmente usan.
 */
trait CreatesHorarioIaSchema
{
    protected function crearEsquemaHorarioIa(): void
    {
        foreach (['docente_programa', 'horarios_ia_generados', 'horarios', 'cursos', 'docentes', 'usuarios', 'aulas', 'periodos_academicos', 'programas_estudio'] as $tabla) {
            Schema::dropIfExists($tabla);
        }

        Schema::create('programas_estudio', function (Blueprint $table) {
            $table->increments('id_programa');
            $table->string('codigo', 20)->nullable();
            $table->string('nombre', 150)->nullable();
            $table->string('familia_profesional', 150)->nullable();
            $table->unsignedTinyInteger('duracion_ciclos')->nullable();
            $table->string('estado', 20)->default('ACTIVO');
        });

        Schema::create('periodos_academicos', function (Blueprint $table) {
            $table->increments('id_periodo');
            $table->string('codigo', 20)->nullable();
            $table->string('nombre', 100)->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('estado', 20)->default('ACTIVO');
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id_usuario');
            $table->string('nombres', 100)->nullable();
            $table->string('apellidos', 100)->nullable();
            $table->softDeletes();
        });

        Schema::create('docentes', function (Blueprint $table) {
            $table->increments('id_docente');
            $table->unsignedInteger('id_usuario')->nullable();
            $table->string('codigo_docente', 30)->nullable();
            $table->string('especialidad', 150)->nullable();
            $table->string('tipo_docente', 30)->nullable();
            $table->string('estado_academico', 20)->default('ACTIVO');
            $table->timestamp('fecha_registro')->nullable();
            $table->softDeletes();
        });

        Schema::create('cursos', function (Blueprint $table) {
            $table->increments('id_curso');
            $table->unsignedInteger('id_docente')->nullable();
            $table->unsignedInteger('id_programa')->nullable();
            $table->string('tipo_curso', 30)->nullable();
            $table->string('nombre_curso', 150)->nullable();
            $table->string('modulo', 100)->nullable();
            $table->string('semestre', 10)->nullable();
            $table->decimal('creditos', 5, 2)->nullable();
            $table->decimal('horas_teoria', 5, 2)->default(0);
            $table->decimal('horas_practica', 5, 2)->default(0);
            $table->decimal('horas_ud', 5, 2)->nullable();
            $table->decimal('total_teoria', 6, 2)->nullable();
            $table->decimal('total_practica', 6, 2)->nullable();
            $table->decimal('total_horas', 6, 2)->default(0);
            $table->string('estado', 20)->default('ACTIVO');
            $table->softDeletes();
        });

        Schema::create('aulas', function (Blueprint $table) {
            $table->increments('id_aula');
            $table->string('codigo', 20)->nullable();
            $table->string('nombre', 100)->nullable();
            $table->string('tipo', 20)->default('AULA');
            $table->unsignedInteger('capacidad')->nullable();
            $table->string('ubicacion', 150)->nullable();
            $table->string('estado', 20)->default('DISPONIBLE');
        });

        Schema::create('horarios', function (Blueprint $table) {
            $table->increments('id_horario');
            $table->unsignedInteger('id_curso');
            $table->unsignedInteger('id_docente');
            $table->unsignedInteger('id_aula')->nullable();
            $table->unsignedInteger('id_periodo')->nullable();
            $table->unsignedInteger('id_programa')->nullable();
            $table->string('semestre', 10)->nullable();
            $table->string('dia', 20)->nullable();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->string('aula', 80)->nullable();
            $table->string('estado', 30)->default('Confirmado');
            $table->enum('fuente', ['MANUAL', 'IA'])->default('MANUAL');
            $table->text('observacion')->nullable();
        });

        Schema::create('docente_programa', function (Blueprint $table) {
            $table->increments('id_docente_programa');
            $table->unsignedInteger('id_docente');
            $table->unsignedInteger('id_programa');
            $table->string('tipo_asignacion', 30)->nullable();
            $table->boolean('es_principal')->default(false);
            $table->string('estado', 20)->default('ACTIVO');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->text('observacion')->nullable();
            $table->timestamps();
        });

        Schema::create('horarios_ia_generados', function (Blueprint $table) {
            $table->increments('id_generacion');
            $table->unsignedInteger('id_usuario')->nullable();
            $table->unsignedInteger('id_periodo')->nullable();
            $table->string('programa', 150)->nullable();
            $table->string('modelo', 80)->nullable();
            $table->text('prompt_resumen')->nullable();
            $table->json('resultado_json')->nullable();
            $table->json('metadata_json')->nullable();
            $table->json('errores_json')->nullable();
            $table->enum('estado', ['BORRADOR', 'APROBADO', 'DESCARTADO'])->default('BORRADOR');
            $table->timestamp('fecha_generacion')->useCurrent();
        });
    }
}
