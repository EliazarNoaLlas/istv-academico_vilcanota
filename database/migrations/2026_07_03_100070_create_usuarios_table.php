<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id_usuario');
            $table->unsignedInteger('id_rol');
            $table->unsignedInteger('id_docente')->nullable();
            $table->string('usuario', 80)->unique();
            $table->string('correo', 150)->unique();
            $table->string('password_hash', 255);
            $table->string('password_algoritmo', 40)->default('sha256-demo');
            $table->string('nombres', 120);
            $table->string('apellidos', 120)->nullable();
            $table->char('dni', 8)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->enum('estado', ['ACTIVO', 'INACTIVO', 'BLOQUEADO'])->default('ACTIVO');
            $table->dateTime('ultimo_acceso')->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_actualizacion')->nullable()->useCurrentOnUpdate();
            $table->softDeletes();

            $table->foreign('id_rol')->references('id_rol')->on('roles');
            $table->foreign('id_docente')->references('id_docente')->on('docentes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
