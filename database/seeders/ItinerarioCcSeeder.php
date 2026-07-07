<?php

namespace Database\Seeders;

/**
 * Itinerario Formativo oficial de Construcción Civil
 * (OFICIO 00883-2020-MINEDU/VMGP-DIGESUTPA-DISERTPA).
 */
class ItinerarioCcSeeder extends ItinerarioProgramaSeeder
{
    protected function programa(): array
    {
        return [
            'codigo' => 'CC',
            'codigos_anteriores' => ['CON'],
            'nombre' => 'Construcción Civil',
            'familia_profesional' => 'Construcción Civil',
            'duracion_ciclos' => 6,
        ];
    }

    protected function itinerario(): array
    {
        return [
            'codigo' => 'IT-CC-2026',
            'nombre' => 'Itinerario Formativo del Programa de Estudios Construcción Civil',
            'resolucion_oficio' => 'OFICIO 00883-2020-MINEDU/VMGP-DIGESUTPA-DISERTPA',
            'descripcion' => 'Itinerario formativo oficial del programa de estudios Construcción Civil del IESTP Vilcanota.',
            'version' => '2026',
        ];
    }

    protected function estructura(): array
    {
        return [
            [
                'codigo' => 'MOD-01',
                'nombre' => 'Topografía y elaboración de planos',
                'competencia' => 'Realizar levantamientos topográficos y elaborar planos técnicos para obras civiles.',
                'bloques' => [
                    [
                        'nombre' => 'Especialidad técnica - Módulo 1',
                        'tipo' => 'ESPECIALIDAD',
                        'color' => '#FFFFFF',
                        'orden' => 1,
                        'unidades' => [
                            ['Topografía general', 'CC-M1-UD01', 'I', 1, 3],
                            ['Dibujo topográfico asistido por computador', 'CC-M1-UD02', 'I', 1, 2],
                            ['Topografía para catastro urbano y rural', 'CC-M1-UD03', 'I', 1, 2],
                            ['Topografía para irrigaciones y saneamiento', 'CC-M1-UD04', 'I', 1, 3],
                            ['Topografía para caminos y vías urbanas', 'CC-M1-UD05', 'II', 2, 3],
                            ['Fotogrametría', 'CC-M1-UD06', 'II', 1, 2],
                            ['Dibujo asistido por computador', 'CC-M1-UD07', 'II', 2, 1],
                            ['Dibujo de planos para obras civiles', 'CC-M1-UD08', 'II', 1, 3],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Comunicación y aplicaciones en internet',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#DDEEFF',
                        'orden' => 2,
                        'unidades' => [
                            ['Comunicación oral', 'CC-M1-UD09', 'I', 1, 1],
                            ['Aplicaciones en internet', 'CC-M1-UD10', 'I', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Textos y ofimática',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#D9F0C8',
                        'orden' => 3,
                        'unidades' => [
                            ['Interpretación y producción textos', 'CC-M1-UD11', 'II', 1, 1],
                            ['Ofimática', 'CC-M1-UD12', 'II', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Experiencias formativas en situaciones reales de trabajo - Módulo 1',
                        'tipo' => 'ESRT',
                        'color' => '#BFDDF2',
                        'orden' => 4,
                        'unidades' => [
                            ['Experiencias formativas en situaciones reales de trabajo (ESRT)', 'CC-M1-ESRT', 'II', 0, 4],
                        ],
                    ],
                ],
            ],
            [
                'codigo' => 'MOD-02',
                'nombre' => 'Procesos de elaboración de expediente técnico y gestión de calidad',
                'competencia' => 'Elaborar expedientes técnicos, presupuestos, metrados y gestionar la calidad en obras civiles.',
                'bloques' => [
                    [
                        'nombre' => 'Especialidad técnica - Módulo 2',
                        'tipo' => 'ESPECIALIDAD',
                        'color' => '#FFFFFF',
                        'orden' => 1,
                        'unidades' => [
                            ['Procesos constructivos en edificaciones', 'CC-M2-UD01', 'III', 1, 2],
                            ['Programación de obra', 'CC-M2-UD02', 'III', 1, 1],
                            ['Expediente técnico', 'CC-M2-UD03', 'III', 1, 2],
                            ['Gestión de riesgos', 'CC-M2-UD04', 'III', 1, 2],
                            ['Seguridad y salud laboral en la construcción', 'CC-M2-UD05', 'III', 1, 1],
                            ['Metrados de obra', 'CC-M2-UD06', 'IV', 2, 3],
                            ['Costos y presupuestos', 'CC-M2-UD07', 'IV', 2, 3],
                            ['Valorización y liquidación de obra', 'CC-M2-UD08', 'IV', 1, 2],
                            ['Gestión de calidad', 'CC-M2-UD09', 'IV', 2, 2],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Inglés y redacción',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#DDEEFF',
                        'orden' => 2,
                        'unidades' => [
                            ['Inglés para la comunicación oral', 'CC-M2-UD10', 'III', 1, 1],
                            ['Comprensión y redacción en inglés', 'CC-M2-UD11', 'III', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Ética y solución de problemas',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#D9F0C8',
                        'orden' => 3,
                        'unidades' => [
                            ['Solución de problemas', 'CC-M2-UD12', 'IV', 1, 1],
                            ['Comportamiento ético', 'CC-M2-UD13', 'IV', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Experiencias formativas en situaciones reales de trabajo - Módulo 2',
                        'tipo' => 'ESRT',
                        'color' => '#BFDDF2',
                        'orden' => 4,
                        'unidades' => [
                            ['Experiencias formativas en situaciones reales de trabajo (ESRT)', 'CC-M2-ESRT', 'IV', 0, 4],
                        ],
                    ],
                ],
            ],
            [
                'codigo' => 'MOD-03',
                'nombre' => 'Ejecución y supervisión de obras civiles',
                'competencia' => 'Ejecutar, controlar y supervisar procesos constructivos de obras civiles.',
                'bloques' => [
                    [
                        'nombre' => 'Especialidad técnica - Módulo 3',
                        'tipo' => 'ESPECIALIDAD',
                        'color' => '#FFFFFF',
                        'orden' => 1,
                        'unidades' => [
                            ['Mecánica de suelos', 'CC-M3-UD01', 'V', 1, 2],
                            ['Construcción de estructuras de concreto armado', 'CC-M3-UD02', 'V', 2, 3],
                            ['Instalaciones sanitarias, eléctricas y gas natural', 'CC-M3-UD03', 'V', 1, 3],
                            ['Supervisión de procesos constructivos', 'CC-M3-UD04', 'V', 2, 1],
                            ['Especificaciones de los materiales de construcción', 'CC-M3-UD05', 'V', 1, 2],
                            ['Tecnología de concreto y asfalto', 'CC-M3-UD06', 'VI', 2, 3],
                            ['Construcción de estructuras en albañilería', 'CC-M3-UD07', 'VI', 1, 3],
                            ['Organización de equipos de trabajo en obra', 'CC-M3-UD08', 'VI', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Innovación y negocios',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#DDEEFF',
                        'orden' => 2,
                        'unidades' => [
                            ['Fundamentos de innovación tecnológica', 'CC-M3-UD09', 'V', 1, 1],
                            ['Oportunidades de negocios', 'CC-M3-UD10', 'V', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Innovación y plan de negocios',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#D9F0C8',
                        'orden' => 3,
                        'unidades' => [
                            ['Innovación tecnológica', 'CC-M3-UD11', 'VI', 0, 1],
                            ['Plan de negocios', 'CC-M3-UD12', 'VI', 0, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Experiencias formativas en situaciones reales de trabajo - Módulo 3',
                        'tipo' => 'ESRT',
                        'color' => '#BFDDF2',
                        'orden' => 4,
                        'unidades' => [
                            ['Experiencias formativas en situaciones reales de trabajo (ESRT)', 'CC-M3-ESRT', 'VI', 0, 4],
                        ],
                    ],
                ],
            ],
        ];
    }
}
