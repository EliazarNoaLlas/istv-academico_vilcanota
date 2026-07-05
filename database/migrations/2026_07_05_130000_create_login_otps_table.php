<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_otps', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_usuario');
            $table->string('email', 150);
            $table->string('code_hash', 255);
            $table->dateTime('expires_at');
            $table->dateTime('used_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->cascadeOnDelete();

            $table->index('id_usuario');
            $table->index('email');
            $table->index('expires_at');
            $table->index('used_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_otps');
    }
};
