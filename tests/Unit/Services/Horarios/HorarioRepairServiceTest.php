<?php

namespace Tests\Unit\Services\Horarios;

use App\Services\Horarios\HorarioConflictService;
use App\Services\Horarios\HorarioRepairService;
use App\Services\Horarios\HorarioValidationService;
use Tests\Concerns\CreatesHorarioIaSchema;
use Tests\TestCase;

class HorarioRepairServiceTest extends TestCase
{
    use CreatesHorarioIaSchema;

    private HorarioRepairService $repair;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crearEsquemaHorarioIa();

        $this->repair = new HorarioRepairService(new HorarioConflictService(), new HorarioValidationService());
    }

    private function contexto(array $overrides = []): array
    {
        return array_merge([
            'id_periodo' => 1,
            'dias' => ['Lunes', 'Martes'],
            'bloques' => [
                ['inicio' => '08:00', 'fin' => '08:45'],
                ['inicio' => '08:45', 'fin' => '09:30'],
            ],
            'aulas' => [
                ['id_aula' => 1, 'tipo' => 'AULA'],
                ['id_aula' => 2, 'tipo' => 'AULA'],
            ],
            'docentes' => [
                ['id_docente' => 1, 'carga_actual_bloques' => 0],
                ['id_docente' => 2, 'carga_actual_bloques' => 0],
            ],
            'cursos' => [
                ['id_curso' => 1, 'id_docente' => null, 'horas_practica' => 0],
                ['id_curso' => 2, 'id_docente' => null, 'horas_practica' => 0],
                ['id_curso' => 3, 'id_docente' => null, 'horas_practica' => 0],
            ],
            'docente_max_bloques' => 20,
        ], $overrides);
    }

    public function test_repara_cruce_de_docente_moviendo_el_bloque_a_otro_horario_libre(): void
    {
        $detalles = [
            ['id_curso' => 1, 'id_docente' => 1, 'id_aula' => 1, 'dia' => 'LUNES', 'hora_inicio' => '08:00', 'hora_fin' => '08:45'],
            ['id_curso' => 2, 'id_docente' => 1, 'id_aula' => 1, 'dia' => 'LUNES', 'hora_inicio' => '08:00', 'hora_fin' => '08:45'],
        ];

        $resultado = $this->repair->reparar($detalles, $this->contexto(), 10);

        $this->assertSame([], $resultado['conflictos_restantes']);
        $this->assertNotEmpty($resultado['cambios']);
        $this->assertNotSame($resultado['detalles'][0]['hora_inicio'], $resultado['detalles'][1]['hora_inicio']);
    }

    public function test_repara_cruce_de_aula_cambiando_de_aula_cuando_no_hay_horario_libre(): void
    {
        $contexto = $this->contexto(['dias' => ['Lunes'], 'bloques' => [['inicio' => '08:00', 'fin' => '08:45']]]);

        $detalles = [
            ['id_curso' => 1, 'id_docente' => 1, 'id_aula' => 1, 'dia' => 'LUNES', 'hora_inicio' => '08:00', 'hora_fin' => '08:45'],
            ['id_curso' => 2, 'id_docente' => 2, 'id_aula' => 1, 'dia' => 'LUNES', 'hora_inicio' => '08:00', 'hora_fin' => '08:45'],
        ];

        $resultado = $this->repair->reparar($detalles, $contexto, 10);

        $this->assertSame([], $resultado['conflictos_restantes']);
        $this->assertNotSame($resultado['detalles'][0]['id_aula'], $resultado['detalles'][1]['id_aula']);
    }

    public function test_reasigna_docente_cuando_no_hay_horario_libre_y_el_curso_no_tiene_docente_fijo(): void
    {
        $contexto = $this->contexto(['dias' => ['Lunes'], 'bloques' => [['inicio' => '08:00', 'fin' => '08:45']]]);

        $detalles = [
            ['id_curso' => 1, 'id_docente' => 1, 'id_aula' => 1, 'dia' => 'LUNES', 'hora_inicio' => '08:00', 'hora_fin' => '08:45'],
            ['id_curso' => 2, 'id_docente' => 1, 'id_aula' => 2, 'dia' => 'LUNES', 'hora_inicio' => '08:00', 'hora_fin' => '08:45'],
        ];

        $resultado = $this->repair->reparar($detalles, $contexto, 10);

        $this->assertSame([], $resultado['conflictos_restantes']);
        $this->assertNotSame($resultado['detalles'][0]['id_docente'], $resultado['detalles'][1]['id_docente']);
    }

    public function test_no_reasigna_docente_fijo_y_deja_conflicto_no_reparable(): void
    {
        $contexto = $this->contexto([
            'dias' => ['Lunes'],
            'bloques' => [['inicio' => '08:00', 'fin' => '08:45']],
            'cursos' => [
                ['id_curso' => 1, 'id_docente' => null, 'horas_practica' => 0],
                ['id_curso' => 2, 'id_docente' => 1, 'horas_practica' => 0], // docente fijo = 1
            ],
        ]);

        $detalles = [
            ['id_curso' => 1, 'id_docente' => 1, 'id_aula' => 1, 'dia' => 'LUNES', 'hora_inicio' => '08:00', 'hora_fin' => '08:45'],
            ['id_curso' => 2, 'id_docente' => 1, 'id_aula' => 2, 'dia' => 'LUNES', 'hora_inicio' => '08:00', 'hora_fin' => '08:45'],
        ];

        $resultado = $this->repair->reparar($detalles, $contexto, 10);

        $this->assertNotEmpty($resultado['conflictos_restantes']);
        $this->assertSame([], $resultado['cambios']);
        $this->assertSame(1, $resultado['detalles'][1]['id_docente']);
    }

    public function test_repara_sobrecarga_de_docente_reasignando_cursos_sin_docente_fijo(): void
    {
        config(['services.horarios_ai.docente_max_bloques' => 2]);
        $contexto = $this->contexto([
            'docente_max_bloques' => 2,
            'dias' => ['Lunes'],
            'bloques' => [
                ['inicio' => '08:00', 'fin' => '08:45'],
                ['inicio' => '08:45', 'fin' => '09:30'],
                ['inicio' => '09:30', 'fin' => '10:15'],
            ],
        ]);

        $detalles = [
            ['id_curso' => 1, 'id_docente' => 1, 'id_aula' => 1, 'dia' => 'LUNES', 'hora_inicio' => '08:00', 'hora_fin' => '08:45'],
            ['id_curso' => 2, 'id_docente' => 1, 'id_aula' => 1, 'dia' => 'LUNES', 'hora_inicio' => '08:45', 'hora_fin' => '09:30'],
            ['id_curso' => 3, 'id_docente' => 1, 'id_aula' => 1, 'dia' => 'LUNES', 'hora_inicio' => '09:30', 'hora_fin' => '10:15'],
        ];

        $resultado = $this->repair->reparar($detalles, $contexto, 10);

        $this->assertSame([], $resultado['conflictos_restantes']);
        $bloquesDocente1 = count(array_filter($resultado['detalles'], fn ($d) => $d['id_docente'] === 1));
        $this->assertLessThanOrEqual(2, $bloquesDocente1);
    }
}
