<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Esquema adoptado de vilcanotaistv_complementaria.sql (registro polimorfico
        // tabla_afectada + registro_id). La tabla vive vacia en produccion, por lo
        // que no hay perdida de datos al preferir este diseno sobre el de
        // base_sistema_segura...sql (que es el que realmente corrio en la BD real).
        Schema::create('auditoria_sistema', function (Blueprint $table) {
            $table->bigIncrements('id_auditoria');
            $table->unsignedInteger('id_usuario')->nullable();
            $table->string('accion', 80);
            $table->string('tabla_afectada', 80)->nullable();
            $table->string('registro_id', 80)->nullable();
            $table->text('detalle')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('fecha_accion')->useCurrent();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_sistema');
    }
};
