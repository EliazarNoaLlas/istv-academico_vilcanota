<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            $table->enum('tipo_docente', ['ESPECIFICO', 'GENERAL'])->default('ESPECIFICO')->after('especialidad');
            $table->index('tipo_docente');
        });
    }

    public function down(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            $table->dropIndex(['tipo_docente']);
            $table->dropColumn('tipo_docente');
        });
    }
};
