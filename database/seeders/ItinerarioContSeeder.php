<?php

namespace Database\Seeders;

/**
 * Itinerario Formativo oficial de Contabilidad
 * (OFICIO 00883-2020-MINEDU/VMGP-DIGESUTPA-DISERTPA).
 */
class ItinerarioContSeeder extends ItinerarioProgramaSeeder
{
    protected function programa(): array
    {
        return [
            'codigo' => 'CONT',
            'codigos_anteriores' => ['CTB'],
            'nombre' => 'Contabilidad',
            'familia_profesional' => 'Contabilidad',
            'duracion_ciclos' => 6,
        ];
    }

    protected function itinerario(): array
    {
        return [
            'codigo' => 'IT-CONT-2026',
            'nombre' => 'Itinerario Formativo del Programa de Estudios Contabilidad',
            'resolucion_oficio' => 'OFICIO 00883-2020-MINEDU/VMGP-DIGESUTPA-DISERTPA',
            'descripcion' => 'Itinerario formativo oficial del programa de estudios Contabilidad del IESTP Vilcanota.',
            'version' => '2026',
        ];
    }

    protected function estructura(): array
    {
        return [
            [
                'codigo' => 'MOD-01',
                'nombre' => 'Procesos y registros contables',
                'competencia' => 'Registrar operaciones contables, comerciales, laborales y tributarias básicas.',
                'bloques' => [
                    [
                        'nombre' => 'Especialidad técnica - Módulo 1',
                        'tipo' => 'ESPECIALIDAD',
                        'color' => '#FFFFFF',
                        'orden' => 1,
                        'unidades' => [
                            ['Contabilidad de libros principales', 'CONT-M1-UD01', 'I', 2, 2],
                            ['Plan contable', 'CONT-M1-UD02', 'I', 2, 2],
                            ['Documentación comercial y contable', 'CONT-M1-UD03', 'I', 2, 2],
                            ['Contabilidad de libros auxiliares', 'CONT-M1-UD04', 'I', 2, 2],
                            ['Legislación tributaria', 'CONT-M1-UD05', 'II', 1, 2],
                            ['Legislación laboral', 'CONT-M1-UD06', 'II', 2, 2],
                            ['Administración empresarial', 'CONT-M1-UD07', 'II', 2, 2],
                            ['Legislación comercial', 'CONT-M1-UD08', 'II', 1, 3],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Comunicación y aplicaciones en internet',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#DDEEFF',
                        'orden' => 2,
                        'unidades' => [
                            ['Comunicación oral', 'CONT-M1-UD09', 'I', 1, 1],
                            ['Aplicaciones en internet', 'CONT-M1-UD10', 'I', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Textos y ofimática',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#D9F0C8',
                        'orden' => 3,
                        'unidades' => [
                            ['Interpretación y producción textos', 'CONT-M1-UD11', 'II', 1, 1],
                            ['Ofimática', 'CONT-M1-UD12', 'II', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Experiencias formativas en situaciones reales de trabajo - Módulo 1',
                        'tipo' => 'ESRT',
                        'color' => '#BFDDF2',
                        'orden' => 4,
                        'unidades' => [
                            ['Experiencias formativas en situaciones reales de trabajo (ESRT)', 'CONT-M1-ESRT', 'II', 0, 4],
                        ],
                    ],
                ],
            ],
            [
                'codigo' => 'MOD-02',
                'nombre' => 'Gestión de procesos contables',
                'competencia' => 'Gestionar costos, presupuestos, tributación y procesos contables informatizados.',
                'bloques' => [
                    [
                        'nombre' => 'Especialidad técnica - Módulo 2',
                        'tipo' => 'ESPECIALIDAD',
                        'color' => '#FFFFFF',
                        'orden' => 1,
                        'unidades' => [
                            ['Fundamentos de costos', 'CONT-M2-UD01', 'III', 1, 1],
                            ['Cálculo financiero', 'CONT-M2-UD02', 'III', 2, 2],
                            ['Contabilidad gubernamental', 'CONT-M2-UD03', 'III', 1, 3],
                            ['Contabilidad de sociedades', 'CONT-M2-UD04', 'III', 2, 3],
                            ['Contabilidad de costos', 'CONT-M2-UD05', 'IV', 2, 2],
                            ['Contabilidad aplicada', 'CONT-M2-UD06', 'IV', 2, 2],
                            ['Aplicativos informáticos contables', 'CONT-M2-UD07', 'IV', 1, 2],
                            ['Tributación y aplicación contable', 'CONT-M2-UD08', 'IV', 1, 1],
                            ['Técnica presupuestal', 'CONT-M2-UD09', 'IV', 1, 2],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Inglés e innovación',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#DDEEFF',
                        'orden' => 2,
                        'unidades' => [
                            ['Inglés para la comunicación oral', 'CONT-M2-UD10', 'III', 1, 1],
                            ['Fundamentos de innovación tecnológica', 'CONT-M2-UD11', 'III', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Redacción e innovación',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#D9F0C8',
                        'orden' => 3,
                        'unidades' => [
                            ['Comprensión y redacción en inglés', 'CONT-M2-UD12', 'IV', 1, 1],
                            ['Innovación tecnológica', 'CONT-M2-UD13', 'IV', 0, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Experiencias formativas en situaciones reales de trabajo - Módulo 2',
                        'tipo' => 'ESRT',
                        'color' => '#BFDDF2',
                        'orden' => 4,
                        'unidades' => [
                            ['Experiencias formativas en situaciones reales de trabajo (ESRT)', 'CONT-M2-ESRT', 'IV', 0, 4],
                        ],
                    ],
                ],
            ],
            [
                'codigo' => 'MOD-03',
                'nombre' => 'Gestión contable y financiera',
                'competencia' => 'Gestionar estados financieros, auditoría, finanzas y análisis contable.',
                'bloques' => [
                    [
                        'nombre' => 'Especialidad técnica - Módulo 3',
                        'tipo' => 'ESPECIALIDAD',
                        'color' => '#FFFFFF',
                        'orden' => 1,
                        'unidades' => [
                            ['Formulación de estados financieros', 'CONT-M3-UD01', 'V', 2, 2],
                            ['Fundamentos de auditoría', 'CONT-M3-UD02', 'V', 1, 2],
                            ['Auditoría tributaria', 'CONT-M3-UD03', 'V', 1, 2],
                            ['Análisis e interpretación de estados financieros', 'CONT-M3-UD04', 'V', 2, 2],
                            ['Formulación y evaluación de proyectos', 'CONT-M3-UD05', 'V', 1, 2],
                            ['Contabilidad financiera', 'CONT-M3-UD06', 'VI', 2, 2],
                            ['Finanzas empresariales', 'CONT-M3-UD07', 'VI', 2, 2],
                            ['Tratamiento estadístico contable', 'CONT-M3-UD08', 'VI', 1, 2],
                            ['Técnicas y procedimientos de auditoría', 'CONT-M3-UD09', 'VI', 1, 2],
                            ['Finanzas públicas', 'CONT-M3-UD10', 'VI', 1, 2],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Ética',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#DDEEFF',
                        'orden' => 2,
                        'unidades' => [
                            ['Comportamiento ético', 'CONT-M3-UD11', 'V', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Solución de problemas',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#D9F0C8',
                        'orden' => 3,
                        'unidades' => [
                            ['Solución de problemas', 'CONT-M3-UD12', 'VI', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Experiencias formativas en situaciones reales de trabajo - Módulo 3',
                        'tipo' => 'ESRT',
                        'color' => '#BFDDF2',
                        'orden' => 4,
                        'unidades' => [
                            ['Experiencias formativas en situaciones reales de trabajo (ESRT)', 'CONT-M3-ESRT', 'VI', 0, 4],
                        ],
                    ],
                ],
            ],
        ];
    }
}
