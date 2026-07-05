<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioBaseSeeder extends Seeder
{
    /**
     * Crea unicamente las 3 cuentas institucionales fijas (director, jua,
     * coordinador). Deliberadamente NO siembra cuentas de docente: esas
     * contraseñas hardcodeadas en js/roles.js son el hallazgo de seguridad
     * critico de la Fase 1 y no deben reaparecer aqui. Las cuentas docente
     * se crean en Fase 5 desde el flujo real de la aplicacion.
     */
    public function run(): void
    {
        $cuentas = [
            ['codigo_rol' => 'director', 'usuario' => 'director', 'correo' => 'director@istv.edu.pe', 'nombres' => 'Director', 'apellidos' => 'Academico'],
            ['codigo_rol' => 'jua', 'usuario' => 'jua', 'correo' => 'jua@istv.edu.pe', 'nombres' => 'JUA', 'apellidos' => 'Academico'],
            ['codigo_rol' => 'coordinador', 'usuario' => 'coordinador', 'correo' => 'coordinador@istv.edu.pe', 'nombres' => 'Coordinador', 'apellidos' => 'Academico'],
        ];

        foreach ($cuentas as $cuenta) {
            $idRol = DB::table('roles')->where('codigo', $cuenta['codigo_rol'])->value('id_rol');

            $yaExiste = DB::table('usuarios')->where('usuario', $cuenta['usuario'])->exists();
            $password = Str::password(16);

            DB::table('usuarios')->updateOrInsert(
                ['usuario' => $cuenta['usuario']],
                [
                    'id_rol' => $idRol,
                    'correo' => $cuenta['correo'],
                    'password_hash' => Hash::make($password),
                    'password_algoritmo' => 'bcrypt',
                    'nombres' => $cuenta['nombres'],
                    'apellidos' => $cuenta['apellidos'],
                    'estado' => 'ACTIVO',
                ]
            );

            if (!$yaExiste) {
                $this->command?->warn("Cuenta creada: {$cuenta['usuario']} / contraseña temporal: {$password}");
            }
        }
    }
}
