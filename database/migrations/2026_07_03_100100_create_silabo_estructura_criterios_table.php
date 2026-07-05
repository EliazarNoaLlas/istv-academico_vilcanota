<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('silabo_estructura_criterios', function (Blueprint $table) {
            $table->increments('id_criterio');
            $table->unsignedInteger('id_estructura');
            $table->tinyInteger('orden');
            $table->string('seccion', 120);
            $table->text('descripcion');
            $table->text('campos_json');
            $table->text('validaciones_json');
            $table->decimal('peso', 5, 2)->default(0);
            $table->boolean('obligatorio')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_actualizacion')->nullable()->useCurrentOnUpdate();

            $table->unique(['id_estructura', 'seccion'], 'uk_silabo_criterio_seccion');
            $table->foreign('id_estructura')->references('id_estructura')->on('silabo_estructuras')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('silabo_estructura_criterios');
    }
};
