<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itinerario_modulos', function (Blueprint $table) {
            $table->increments('id_modulo');
            $table->unsignedInteger('id_itinerario');
            $table->tinyInteger('numero_modulo');
            $table->string('codigo', 30);
            $table->string('nombre', 255);
            $table->text('competencia')->nullable();
            $table->text('descripcion')->nullable();
            $table->tinyInteger('orden');
            $table->string('color_hex', 20)->default('#FFFFFF');
            $table->integer('total_creditos')->default(0);
            $table->integer('total_horas')->default(0);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();

            $table->foreign('id_itinerario')->references('id_itinerario')->on('itinerarios_formativos')
                ->cascadeOnDelete()->cascadeOnUpdate();

            $table->unique(['id_itinerario', 'numero_modulo'], 'uq_modulo_itinerario_numero');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itinerario_modulos');
    }
};
