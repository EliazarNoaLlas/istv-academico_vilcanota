<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensajes', function (Blueprint $table) {
            $table->increments('id_mensaje');
            $table->unsignedInteger('id_remitente');
            $table->unsignedInteger('id_destinatario');
            $table->string('asunto', 180);
            $table->text('mensaje');
            $table->boolean('leido')->default(false);
            $table->timestamp('fecha_envio')->useCurrent();

            $table->foreign('id_remitente')->references('id_usuario')->on('usuarios');
            $table->foreign('id_destinatario')->references('id_usuario')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes');
    }
};
