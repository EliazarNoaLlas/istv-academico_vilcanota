<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            $table->unsignedInteger('id_usuario')->nullable()->unique()->after('id_docente');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->cascadeOnDelete();
        });

        $idRolDocente = DB::table('roles')->where('codigo', 'docente')->value('id_rol');

        foreach (DB::table('docentes')->get() as $docente) {
            $usuario = DB::table('usuarios')->where('id_docente', $docente->id_docente)->first();

            if ($usuario) {
                $idUsuario = $usuario->id_usuario;

                DB::table('usuarios')->where('id_usuario', $idUsuario)->update(array_filter([
                    'nombres' => $usuario->nombres ?: $docente->nombres,
                    'apellidos' => $usuario->apellidos ?: trim("{$docente->apellido_paterno} {$docente->apellido_materno}"),
                    'dni' => $usuario->dni ?: $docente->dni,
                ]));
            } else {
                $idUsuario = DB::table('usuarios')->insertGetId([
                    'id_rol' => $idRolDocente,
                    'usuario' => strtolower($docente->codigo_docente),
                    'correo' => strtolower($docente->codigo_docente) . '@istv.edu.pe',
                    'password_hash' => Hash::make(Str::password(16)),
                    'password_algoritmo' => 'bcrypt',
                    'nombres' => $docente->nombres,
                    'apellidos' => trim("{$docente->apellido_paterno} {$docente->apellido_materno}"),
                    'dni' => $docente->dni,
                    'estado' => $docente->estado,
                ]);
            }

            DB::table('docentes')->where('id_docente', $docente->id_docente)->update(['id_usuario' => $idUsuario]);
        }

        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['id_docente']);
            $table->dropColumn('id_docente');
        });

        Schema::table('docentes', function (Blueprint $table) {
            $table->dropColumn(['dni', 'nombres', 'apellido_paterno', 'apellido_materno']);
        });

        // Se usa SQL directo en lugar de renameColumn() porque el compilador de
        // rename legado de Laravel para MariaDB < 10.5.2 duplica las comillas
        // del valor default en columnas enum (bug de compatibilidad conocido).
        DB::statement("ALTER TABLE docentes CHANGE estado estado_academico ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE docentes CHANGE estado_academico estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO'");

        Schema::table('docentes', function (Blueprint $table) {
            $table->char('dni', 8)->nullable()->after('codigo_docente');
            $table->string('nombres', 100)->nullable()->after('dni');
            $table->string('apellido_paterno', 50)->nullable()->after('nombres');
            $table->string('apellido_materno', 50)->nullable()->after('apellido_paterno');
        });

        Schema::table('usuarios', function (Blueprint $table) {
            $table->unsignedInteger('id_docente')->nullable()->after('id_rol');
            $table->foreign('id_docente')->references('id_docente')->on('docentes')->nullOnDelete();
        });

        foreach (DB::table('docentes')->whereNotNull('id_usuario')->get() as $docente) {
            $usuario = DB::table('usuarios')->where('id_usuario', $docente->id_usuario)->first();

            if ($usuario) {
                DB::table('usuarios')->where('id_usuario', $docente->id_usuario)->update([
                    'id_docente' => $docente->id_docente,
                ]);

                DB::table('docentes')->where('id_docente', $docente->id_docente)->update([
                    'dni' => $usuario->dni,
                    'nombres' => $usuario->nombres,
                    'apellido_paterno' => $usuario->apellidos,
                ]);
            }
        }

        Schema::table('docentes', function (Blueprint $table) {
            $table->dropForeign(['id_usuario']);
            $table->dropColumn('id_usuario');
        });
    }
};
