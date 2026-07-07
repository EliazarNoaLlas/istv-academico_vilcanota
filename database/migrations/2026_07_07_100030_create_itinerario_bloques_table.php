<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itinerario_bloques', function (Blueprint $table) {
            $table->increments('id_bloque');
            $table->unsignedInteger('id_modulo');
            $table->string('nombre', 150);
            $table->enum('tipo_bloque', ['ESPECIALIDAD', 'EMPLEABILIDAD', 'ESRT', 'TRANSVERSAL', 'OTRO']);
            $table->string('color_hex', 20)->default('#FFFFFF');
            $table->tinyInteger('orden');
            $table->integer('creditos_bloque')->default(0);
            $table->integer('horas_bloque')->default(0);
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->foreign('id_modulo')->references('id_modulo')->on('itinerario_modulos')
                ->cascadeOnDelete()->cascadeOnUpdate();

            $table->index(['id_modulo', 'tipo_bloque'], 'idx_bloque_modulo_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itinerario_bloques');
    }
};
