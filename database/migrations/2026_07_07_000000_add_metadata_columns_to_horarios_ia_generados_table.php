<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horarios_ia_generados', function (Blueprint $table) {
            $table->json('metadata_json')->nullable()->after('resultado_json');
            $table->json('errores_json')->nullable()->after('metadata_json');
        });
    }

    public function down(): void
    {
        Schema::table('horarios_ia_generados', function (Blueprint $table) {
            $table->dropColumn(['metadata_json', 'errores_json']);
        });
    }
};
