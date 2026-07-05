<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->increments('id_notificacion');
            $table->unsignedInteger('id_usuario')->nullable();
            $table->string('tipo', 40);
            $table->string('titulo', 150);
            $table->string('detalle', 255)->nullable();
            $table->string('url_destino', 255)->nullable();
            $table->boolean('leido')->default(false);
            $table->timestamp('fecha_creacion')->useCurrent();

            // Sin UNIQUE(id_usuario,tipo,titulo): los datos reales ya tienen
            // combinaciones repetidas (ver riesgos de Fase 2), agregarla rompería el import.
            $table->index('tipo');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
