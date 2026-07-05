<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->timestamp('otp_verified_at')->nullable()->after('ultimo_acceso');
            $table->string('otp_last_verified_ip', 45)->nullable()->after('otp_verified_at');
            $table->text('otp_last_verified_user_agent')->nullable()->after('otp_last_verified_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['otp_verified_at', 'otp_last_verified_ip', 'otp_last_verified_user_agent']);
        });
    }
};
