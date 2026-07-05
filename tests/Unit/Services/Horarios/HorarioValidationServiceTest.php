<?php

namespace Tests\Unit\Services\Horarios;

use App\Services\Horarios\HorarioConflictService;
use App\Services\Horarios\HorarioValidationService;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreatesHorarioIaSchema;
use Tests\TestCase;

class HorarioValidationServiceTest extends TestCase
{
    use CreatesHorarioIaSchema;

    private HorarioValidationService $validacion;
    private HorarioConflictService $conflictos;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crearEsquemaHorarioIa();

        $this->validacion = new HorarioValidationService();
        $this->conflictos = new HorarioConflictService();

        DB::table('programas_estudio')->insert(['id_programa' => 1, 'nombre' => 'Desarrollo de Sistemas', 'estado' => 'ACTIVO']);
        DB::table('cursos')->insert([
            ['id_curso' => 1, 'id_programa' => 1, 'nombre_curso' => 'Programación I', 'semestre' => 'I', 'total_horas' => 2, 'estado' => 'ACTIVO'],
            ['id_curso' => 2, 'id_programa' => 1, 'nombre_curso' => 'Programación II', 'semestre' => 'I', 'total_horas' => 2, 'estado' => 'INACTIVO'],
        ]);
        DB::table('docentes')->insert([
            ['id_docente' => 1, 'especialidad' => 'Software', 'estado_academico' => 'ACTIVO'],
            ['id_docente' => 2, 'especialidad' => 'Software', 'estado_academico' => 'INACTIVO'],
        ]);
        DB::table('aulas')->insert([
            ['id_aula' => 1, 'codigo' => 'A-101', 'tipo' => 'AULA', 'estado' => 'DISPONIBLE'],
            ['id_aula' => 2, 'codigo' => 'A-102', 'tipo' => 'AULA', 'estado' => 'MANTENIMIENTO'],
        ]);
    }

    private function bloque(array $overrides = []): array
    {
        return array_merge([
            'id_curso' => 1,
            'id_docente' => 1,
            'id_aula' => 1,
            'dia' => 'LUNES',
            'hora_inicio' => '08:00',
            'hora_fin' => '08:45',
        ], $overrides);
    }

    public function test_propuesta_valida_no_devuelve_errores(): void
    {
        $detalles = [
            $this->bloque(['hora_inicio' => '08:00', 'hora_fin' => '08:45']),
            $this->bloque(['hora_inicio' => '08:45', 'hora_fin' => '09:30']),
        ];

        $this->assertSame([], $this->validacion->validarPropuestaIa($detalles));
    }

    public function test_rechaza_ids_inexistentes(): void
    {
        $errores = $this->validacion->validarPropuestaIa([$this->bloque(['id_curso' => 999])]);

        $this->assertNotEmpty($errores);
        $this->assertStringContainsString('id_curso 999 no existe', implode(' ', $errores));
    }

    public function test_rechaza_curso_docente_y_aula_inactivos(): void
    {
        $errores = $this->validacion->validarPropuestaIa([$this->bloque(['id_curso' => 2, 'id_docente' => 2, 'id_aula' => 2])]);

        $texto = implode(' | ', $errores);
        $this->assertStringContainsString('no esta activo', $texto);
        $this->assertStringContainsString('no esta disponible', $texto);
    }

    public function test_detecta_bloques_duplicados(): void
    {
        $errores = $this->validacion->validarPropuestaIa([$this->bloque(), $this->bloque()]);

        $this->assertStringContainsString('duplicado', implode(' ', $errores));
    }

    public function test_detecta_horas_insuficientes_para_el_curso(): void
    {
        // El curso 1 requiere 2 horas (total_horas=2) pero solo se asigna 1 bloque.
        $errores = $this->validacion->validarPropuestaIa([$this->bloque()]);

        $this->assertStringContainsString('requiere 2', implode(' ', $errores));
    }

    public function test_cruce_de_docente_es_detectado(): void
    {
        $detalles = [
            $this->bloque(['id_curso' => 1, 'id_aula' => 1]),
            $this->bloque(['id_curso' => 2, 'id_aula' => 2]),
        ];

        $conflictos = $this->conflictos->detectarParaPropuestaIa($detalles);

        $this->assertNotEmpty(array_filter($conflictos, fn ($c) => $c['tipo'] === 'CRUCE_DOCENTE'));
    }

    public function test_cruce_de_aula_es_detectado(): void
    {
        $detalles = [
            $this->bloque(['id_curso' => 1, 'id_docente' => 1, 'id_aula' => 1]),
            $this->bloque(['id_curso' => 2, 'id_docente' => 2, 'id_aula' => 1]),
        ];

        $conflictos = $this->conflictos->detectarParaPropuestaIa($detalles);

        $this->assertNotEmpty(array_filter($conflictos, fn ($c) => $c['tipo'] === 'CRUCE_AULA'));
    }

    public function test_sin_cruce_cuando_dia_hora_docente_y_aula_difieren(): void
    {
        $detalles = [
            $this->bloque(['id_curso' => 1, 'hora_inicio' => '08:00', 'hora_fin' => '08:45']),
            $this->bloque(['id_curso' => 2, 'hora_inicio' => '08:45', 'hora_fin' => '09:30']),
        ];

        $this->assertSame([], $this->conflictos->detectarParaPropuestaIa($detalles));
    }

    public function test_docente_que_supera_el_maximo_de_bloques_es_conflicto_critico(): void
    {
        config(['services.horarios_ai.docente_max_bloques' => 20]);

        // 18 bloques ya existentes en BD para el docente 1 + 3 nuevos = 21 > 20.
        for ($i = 0; $i < 18; $i++) {
            DB::table('horarios')->insert([
                'id_curso' => 1,
                'id_docente' => 1,
                'dia' => 'LUNES',
                'hora_inicio' => sprintf('%02d:00', 6 + $i),
                'hora_fin' => sprintf('%02d:45', 6 + $i),
                'estado' => 'Confirmado',
                'fuente' => 'MANUAL',
            ]);
        }

        $detalles = [
            $this->bloque(['dia' => 'MARTES', 'hora_inicio' => '08:00', 'hora_fin' => '08:45']),
            $this->bloque(['dia' => 'MARTES', 'hora_inicio' => '08:45', 'hora_fin' => '09:30']),
            $this->bloque(['dia' => 'MARTES', 'hora_inicio' => '09:30', 'hora_fin' => '10:15']),
        ];

        $conflictos = $this->validacion->validarCargaDocenteSemanal($detalles);

        $this->assertCount(1, $conflictos);
        $this->assertSame('DOCENTE_SUPERA_CARGA', $conflictos[0]['tipo']);
        $this->assertSame('CRITICA', $conflictos[0]['severidad']);
        $this->assertSame(21, $conflictos[0]['data']['bloques_asignados']);
        $this->assertSame(20, $conflictos[0]['data']['maximo_permitido']);
    }

    public function test_docente_dentro_del_limite_no_genera_conflicto(): void
    {
        $detalles = [$this->bloque()];

        $this->assertSame([], $this->validacion->validarCargaDocenteSemanal($detalles));
    }
}
