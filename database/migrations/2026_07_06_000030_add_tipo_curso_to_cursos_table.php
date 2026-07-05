<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->enum('tipo_curso', ['ESPECIFICO', 'TRANSVERSAL'])->default('ESPECIFICO')->after('id_programa');
            $table->index('tipo_curso');
        });
    }

    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropIndex(['tipo_curso']);
            $table->dropColumn('tipo_curso');
        });
    }
};
