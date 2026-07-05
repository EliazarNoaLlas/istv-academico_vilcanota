<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_password', function (Blueprint $table) {
            $table->increments('id_solicitud');

            $table->unsignedInteger('id_usuario');
            $table->unsignedInteger('id_usuario_atiende')->nullable();

            $table->string('motivo', 255)->nullable();
            $table->enum('estado', ['PENDIENTE', 'APROBADA', 'RECHAZADA'])->default('PENDIENTE');
            $table->string('motivo_rechazo', 255)->nullable();

            $table->string('ip_solicitud', 45)->nullable();
            $table->timestamp('fecha_solicitud')->useCurrent();
            $table->dateTime('fecha_atencion')->nullable();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->cascadeOnDelete();
            $table->foreign('id_usuario_atiende')->references('id_usuario')->on('usuarios')->nullOnDelete();

            $table->index(['id_usuario', 'estado']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_password');
    }
};
