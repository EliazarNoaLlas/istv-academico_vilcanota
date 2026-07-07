<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            if (!Schema::hasColumn('cursos', 'id_unidad_itinerario')) {
                $table->unsignedInteger('id_unidad_itinerario')->nullable()->after('id_programa');
            }
            if (!Schema::hasColumn('cursos', 'ciclo')) {
                $table->enum('ciclo', ['I', 'II', 'III', 'IV', 'V', 'VI'])->nullable()->after('semestre');
            }
            if (!Schema::hasColumn('cursos', 'tipo_formacion')) {
                $table->enum('tipo_formacion', ['ESPECIALIDAD', 'EMPLEABILIDAD', 'ESRT', 'TRANSVERSAL', 'OTRO'])
                    ->default('ESPECIALIDAD')->after('tipo_curso');
            }
            if (!Schema::hasColumn('cursos', 'color_hex')) {
                $table->string('color_hex', 20)->nullable()->after('tipo_formacion');
            }
            if (!Schema::hasColumn('cursos', 'orden_malla')) {
                $table->integer('orden_malla')->nullable()->after('color_hex');
            }
            if (!Schema::hasColumn('cursos', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('orden_malla');
            }
        });

        Schema::table('cursos', function (Blueprint $table) {
            $table->foreign('id_unidad_itinerario', 'fk_cursos_unidad_itinerario')
                ->references('id_unidad')->on('itinerario_unidades_didacticas')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropForeign('fk_cursos_unidad_itinerario');
        });

        Schema::table('cursos', function (Blueprint $table) {
            foreach (['descripcion', 'orden_malla', 'color_hex', 'tipo_formacion', 'ciclo', 'id_unidad_itinerario'] as $columna) {
                if (Schema::hasColumn('cursos', $columna)) {
                    $table->dropColumn($columna);
                }
            }
        });
    }
};
