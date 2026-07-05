<?php

namespace Tests\Feature\Horarios;

use App\Models\Horario;
use App\Models\HorarioIaGenerado;
use App\Services\Horarios\HorarioAiGeneratorService;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreatesHorarioIaSchema;
use Tests\TestCase;

class HorarioAiGeneratorServiceTest extends TestCase
{
    use CreatesHorarioIaSchema;

    private HorarioAiGeneratorService $generador;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crearEsquemaHorarioIa();
        $this->generador = app(HorarioAiGeneratorService::class);

        DB::table('programas_estudio')->insert(['id_programa' => 1, 'nombre' => 'Desarrollo de Sistemas', 'estado' => 'ACTIVO']);
        DB::table('periodos_academicos')->insert(['id_periodo' => 1, 'codigo' => '2026-I', 'estado' => 'ACTIVO']);
        DB::table('usuarios')->insert(['id_usuario' => 1, 'nombres' => 'Ana', 'apellidos' => 'Coordinadora']);
        DB::table('docentes')->insert([
            ['id_docente' => 1, 'id_usuario' => 1, 'especialidad' => 'Software', 'estado_academico' => 'ACTIVO'],
            ['id_docente' => 2, 'id_usuario' => 1, 'especialidad' => 'Software', 'estado_academico' => 'ACTIVO'],
        ]);
        DB::table('docente_programa')->insert(['id_docente' => 2, 'id_programa' => 1, 'estado' => 'ACTIVO']);
        DB::table('aulas')->insert([
            ['id_aula' => 1, 'codigo' => 'A-101', 'tipo' => 'AULA', 'estado' => 'DISPONIBLE'],
            ['id_aula' => 2, 'codigo' => 'A-102', 'tipo' => 'AULA', 'estado' => 'DISPONIBLE'],
        ]);
        DB::table('cursos')->insert([
            ['id_curso' => 1, 'id_programa' => 1, 'id_docente' => 1, 'nombre_curso' => 'Programación I', 'semestre' => 'I', 'total_horas' => 1, 'estado' => 'ACTIVO'],
            ['id_curso' => 2, 'id_programa' => 1, 'id_docente' => null, 'nombre_curso' => 'Base de Datos', 'semestre' => 'I', 'total_horas' => 1, 'estado' => 'ACTIVO'],
        ]);
    }

    private function datosBase(array $overrides = []): array
    {
        return array_merge([
            'id_programa' => 1,
            'id_periodo' => 1,
            'semestre' => 'I',
            'provider' => 'fake',
            'id_usuario' => 1,
        ], $overrides);
    }

    public function test_genera_y_guarda_directamente_cuando_la_propuesta_es_valida(): void
    {
        $resultado = $this->generador->generar($this->datosBase(['modo' => 'guardar_si_valido']));

        $this->assertTrue($resultado['ok']);
        $this->assertSame('APROBADO', $resultado['generacion']['estado']);
        $this->assertSame('fake', $resultado['generacion']['modelo']);
        $this->assertNull($resultado['generacion']['errores']);

        $this->assertSame(2, Horario::where('fuente', 'IA')->count());
        $this->assertSame(1, HorarioIaGenerado::count());
    }

    public function test_modo_borrador_no_persiste_hasta_que_se_aprueba(): void
    {
        $resultado = $this->generador->generar($this->datosBase(['modo' => 'borrador']));

        $this->assertTrue($resultado['ok']);
        $this->assertSame('BORRADOR', $resultado['generacion']['estado']);
        $this->assertSame(0, Horario::count());

        $idGeneracion = $resultado['generacion']['id_generacion'];
        $aprobado = $this->generador->aprobar($idGeneracion);

        $this->assertTrue($aprobado['ok']);
        $this->assertSame('APROBADO', $aprobado['generacion']['estado']);
        $this->assertSame(2, Horario::where('fuente', 'IA')->count());
    }

    public function test_descartar_generacion_no_persiste_horarios(): void
    {
        $resultado = $this->generador->generar($this->datosBase(['modo' => 'borrador']));
        $idGeneracion = $resultado['generacion']['id_generacion'];

        $descartado = $this->generador->descartar($idGeneracion);

        $this->assertTrue($descartado['ok']);
        $this->assertSame('DESCARTADO', $descartado['generacion']['estado']);
        $this->assertSame(0, Horario::count());

        $aprobado = $this->generador->aprobar($idGeneracion);
        $this->assertFalse($aprobado['ok']);
        $this->assertSame(0, Horario::count());
    }

    public function test_estado_devuelve_la_generacion_actual(): void
    {
        $resultado = $this->generador->generar($this->datosBase(['modo' => 'borrador']));
        $idGeneracion = $resultado['generacion']['id_generacion'];

        $estado = $this->generador->estado($idGeneracion);

        $this->assertSame($idGeneracion, $estado['generacion']['id_generacion']);
        $this->assertSame('BORRADOR', $estado['generacion']['estado']);
    }
}
