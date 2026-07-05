<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracionSistemaSeeder extends Seeder
{
    public function run(): void
    {
        // Union de las claves sembradas por base_sistema_segura...sql (las que
        // realmente corrieron en produccion) y por vilcanotaistv_complementaria.sql
        // (que nunca llego a ejecutarse). Ninguna clave se repite entre ambas.
        $config = [
            ['clave' => 'nota_minima_aprobatoria', 'valor' => '10.5', 'descripcion' => 'Nota minima para aprobar una unidad didactica'],
            ['clave' => 'porcentaje_riesgo_asistencia', 'valor' => '70', 'descripcion' => 'Umbral de asistencia para alerta academica'],
            ['clave' => 'ia_predictiva_modelo', 'valor' => 'reglas-academicas-v1', 'descripcion' => 'Modelo activo para deteccion preventiva'],
            ['clave' => 'horarios_protegidos', 'valor' => '1', 'descripcion' => 'La tabla horarios no debe modificarse desde scripts complementarios'],
            ['clave' => 'semestre_activo', 'valor' => '2026-I', 'descripcion' => 'Periodo academico activo por defecto'],
            ['clave' => 'institucion_nombre', 'valor' => 'Instituto Superior Tecnologico Vilcanota', 'descripcion' => 'Nombre institucional'],
            ['clave' => 'max_horas_docente_semana', 'valor' => '20', 'descripcion' => 'Limite recomendado de horas academicas por docente'],
        ];

        foreach ($config as $item) {
            DB::table('configuracion_sistema')->updateOrInsert(['clave' => $item['clave']], $item);
        }
    }
}
