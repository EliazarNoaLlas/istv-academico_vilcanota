<?php

namespace Tests\Unit\Services\Horarios;

use App\Services\Horarios\HorarioAiResponseParserService;
use RuntimeException;
use Tests\TestCase;

class HorarioAiResponseParserServiceTest extends TestCase
{
    private HorarioAiResponseParserService $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new HorarioAiResponseParserService();
    }

    public function test_parsea_json_limpio(): void
    {
        $json = '{"estado":"GENERADO","detalles":[{"id_curso":1}],"observaciones":["ok"],"conflictos":[]}';

        $resultado = $this->parser->parsear($json);

        $this->assertSame('GENERADO', $resultado['estado']);
        $this->assertCount(1, $resultado['detalles']);
        $this->assertSame(['ok'], $resultado['observaciones']);
        $this->assertSame([], $resultado['conflictos']);
    }

    public function test_extrae_json_aunque_venga_envuelto_en_texto_y_bloque_markdown(): void
    {
        $texto = "Aqui tienes la propuesta:\n```json\n{\"estado\":\"GENERADO\",\"detalles\":[{\"id_curso\":1,\"id_docente\":2}]}\n```\nEspero que sirva.";

        $resultado = $this->parser->parsear($texto);

        $this->assertSame('GENERADO', $resultado['estado']);
        $this->assertSame(1, $resultado['detalles'][0]['id_curso']);
    }

    public function test_no_se_confunde_con_llaves_dentro_de_strings(): void
    {
        $texto = '{"estado":"GENERADO","detalles":[],"observaciones":["contiene { y } dentro de un string"],"conflictos":[]}';

        $resultado = $this->parser->parsear($texto);

        $this->assertSame(['contiene { y } dentro de un string'], $resultado['observaciones']);
    }

    public function test_lanza_excepcion_si_no_hay_json(): void
    {
        $this->expectException(RuntimeException::class);

        $this->parser->parsear('No puedo generar un horario en este momento.');
    }

    public function test_lanza_excepcion_si_falta_la_clave_detalles(): void
    {
        $this->expectException(RuntimeException::class);

        $this->parser->parsear('{"estado":"GENERADO","observaciones":[]}');
    }

    public function test_completa_observaciones_y_conflictos_vacios_si_faltan(): void
    {
        $resultado = $this->parser->parsear('{"detalles":[]}');

        $this->assertSame([], $resultado['observaciones']);
        $this->assertSame([], $resultado['conflictos']);
        $this->assertSame('GENERADO', $resultado['estado']);
    }
}
