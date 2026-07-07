<?php

namespace Database\Seeders;

/**
 * Itinerario Formativo oficial de Producción Agropecuaria
 * (OFICIO 00883-2020-MINEDU/VMGP-DIGESUTPA-DISERTPA).
 */
class ItinerarioPaSeeder extends ItinerarioProgramaSeeder
{
    protected function programa(): array
    {
        return [
            'codigo' => 'PA',
            'codigos_anteriores' => ['AGRO'],
            'nombre' => 'Producción Agropecuaria',
            'familia_profesional' => 'Agropecuaria',
            'duracion_ciclos' => 6,
        ];
    }

    protected function itinerario(): array
    {
        return [
            'codigo' => 'IT-PA-2026',
            'nombre' => 'Itinerario Formativo del Programa de Estudios Producción Agropecuaria',
            'resolucion_oficio' => 'OFICIO 00883-2020-MINEDU/VMGP-DIGESUTPA-DISERTPA',
            'descripcion' => 'Itinerario formativo oficial del programa de estudios Producción Agropecuaria del IESTP Vilcanota.',
            'version' => '2026',
        ];
    }

    protected function estructura(): array
    {
        return [
            [
                'codigo' => 'MOD-01',
                'nombre' => 'Gestión de la producción de cultivos',
                'competencia' => 'Gestionar la producción agrícola, conservación de suelos, riego y cultivos.',
                'bloques' => [
                    [
                        'nombre' => 'Especialidad técnica - Módulo 1',
                        'tipo' => 'ESPECIALIDAD',
                        'color' => '#FFFFFF',
                        'orden' => 1,
                        'unidades' => [
                            ['Manejo y conservación de suelos', 'PA-M1-UD01', 'I', 1, 1],
                            ['Mecanización agrícola', 'PA-M1-UD02', 'I', 1, 2],
                            ['Botánica y fisiología vegetal', 'PA-M1-UD03', 'I', 1, 1],
                            ['Sistemas de riego', 'PA-M1-UD04', 'I', 1, 2],
                            ['Topografía agrícola', 'PA-M1-UD05', 'I', 1, 2],
                            ['Producción de pastos y forrajes', 'PA-M1-UD06', 'I', 1, 2],
                            ['Propagación de plantas en viveros', 'PA-M1-UD07', 'II', 1, 2],
                            ['Horticultura', 'PA-M1-UD08', 'II', 2, 2],
                            ['Producción de raíces y tuberosas', 'PA-M1-UD09', 'II', 1, 2],
                            ['Producción de leguminosas y cereales', 'PA-M1-UD10', 'II', 1, 2],
                            ['Fruticultura', 'PA-M1-UD11', 'II', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Comunicación e internet',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#DDEEFF',
                        'orden' => 2,
                        'unidades' => [
                            ['Comunicación oral', 'PA-M1-UD12', 'I', 0, 1],
                            ['Aplicaciones en internet', 'PA-M1-UD13', 'I', 0, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Textos y ofimática',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#D9F0C8',
                        'orden' => 3,
                        'unidades' => [
                            ['Interpretación y producción textos', 'PA-M1-UD14', 'II', 1, 1],
                            ['Ofimática', 'PA-M1-UD15', 'II', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Experiencias formativas en situaciones reales de trabajo - Módulo 1',
                        'tipo' => 'ESRT',
                        'color' => '#BFDDF2',
                        'orden' => 4,
                        'unidades' => [
                            ['Experiencias formativas en situaciones reales de trabajo (ESRT)', 'PA-M1-ESRT', 'II', 0, 4],
                        ],
                    ],
                ],
            ],
            [
                'codigo' => 'MOD-02',
                'nombre' => 'Gestión de la producción pecuaria',
                'competencia' => 'Gestionar la producción animal, reproducción, alimentación y crianza pecuaria.',
                'bloques' => [
                    [
                        'nombre' => 'Especialidad técnica - Módulo 2',
                        'tipo' => 'ESPECIALIDAD',
                        'color' => '#FFFFFF',
                        'orden' => 1,
                        'unidades' => [
                            ['Anatomía y fisiología animal', 'PA-M2-UD01', 'III', 1, 1],
                            ['Técnicas de mejoramiento animal', 'PA-M2-UD02', 'III', 1, 2],
                            ['Nutrición y alimentación animal', 'PA-M2-UD03', 'III', 1, 1],
                            ['Reproducción animal e inseminación artificial', 'PA-M2-UD04', 'III', 1, 2],
                            ['Producción de aves', 'PA-M2-UD05', 'III', 1, 1],
                            ['Producción de cuyes y conejos', 'PA-M2-UD06', 'III', 2, 2],
                            ['Producción de vacunos', 'PA-M2-UD07', 'IV', 1, 2],
                            ['Producción de porcinos', 'PA-M2-UD08', 'IV', 1, 2],
                            ['Producción de ovinos y caprinos', 'PA-M2-UD09', 'IV', 1, 2],
                            ['Apicultura', 'PA-M2-UD10', 'IV', 1, 2],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Inglés y ética',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#DDEEFF',
                        'orden' => 2,
                        'unidades' => [
                            ['Inglés para la comunicación oral', 'PA-M2-UD11', 'III', 0, 1],
                            ['Comportamiento ético', 'PA-M2-UD12', 'III', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Redacción y problemas',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#D9F0C8',
                        'orden' => 3,
                        'unidades' => [
                            ['Comprensión y redacción en inglés', 'PA-M2-UD13', 'IV', 1, 1],
                            ['Solución de problemas', 'PA-M2-UD14', 'IV', 0, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Experiencias formativas en situaciones reales de trabajo - Módulo 2',
                        'tipo' => 'ESRT',
                        'color' => '#BFDDF2',
                        'orden' => 4,
                        'unidades' => [
                            ['Experiencias formativas en situaciones reales de trabajo (ESRT)', 'PA-M2-ESRT', 'IV', 0, 4],
                        ],
                    ],
                ],
            ],
            [
                'codigo' => 'MOD-03',
                'nombre' => 'Prevención y manejo sanitario y fitosanitario',
                'competencia' => 'Controlar enfermedades, plagas y procesos sanitarios agropecuarios.',
                'bloques' => [
                    [
                        'nombre' => 'Especialidad técnica - Módulo 3',
                        'tipo' => 'ESPECIALIDAD',
                        'color' => '#FFFFFF',
                        'orden' => 1,
                        'unidades' => [
                            ['Control de enfermedades metabólicas e infecciosas', 'PA-M3-UD01', 'V', 2, 2],
                            ['Control de enfermedades parasitarias', 'PA-M3-UD02', 'V', 2, 2],
                            ['Manejo integrado de plagas agrícolas', 'PA-M3-UD03', 'V', 2, 2],
                            ['Manejo y control de enfermedades agrícolas', 'PA-M3-UD04', 'V', 2, 2],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Innovación y negocios',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#DDEEFF',
                        'orden' => 2,
                        'unidades' => [
                            ['Fundamentos de innovación tecnológica', 'PA-M3-UD05', 'V', 1, 1],
                            ['Oportunidades de negocios', 'PA-M3-UD06', 'V', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Experiencias formativas en situaciones reales de trabajo - Módulo 3',
                        'tipo' => 'ESRT',
                        'color' => '#BFDDF2',
                        'orden' => 4,
                        'unidades' => [
                            ['Experiencias formativas en situaciones reales de trabajo (ESRT)', 'PA-M3-ESRT', 'V', 0, 3],
                        ],
                    ],
                ],
            ],
            [
                'codigo' => 'MOD-04',
                'nombre' => 'Aprovechamiento y mercadeo de productos agropecuarios',
                'competencia' => 'Gestionar poscosecha, procesamiento, calidad y comercialización agropecuaria.',
                'bloques' => [
                    [
                        'nombre' => 'Especialidad técnica - Módulo 4',
                        'tipo' => 'ESPECIALIDAD',
                        'color' => '#FFFFFF',
                        'orden' => 1,
                        'unidades' => [
                            ['Manejo poscosecha', 'PA-M4-UD01', 'VI', 1, 2],
                            ['Procesamiento primario de productos agrícolas', 'PA-M4-UD02', 'VI', 1, 2],
                            ['Procesamiento primario de productos pecuarios', 'PA-M4-UD03', 'VI', 1, 2],
                            ['Control de calidad de productos agropecuarios', 'PA-M4-UD04', 'VI', 1, 1],
                            ['Plan de explotación agropecuaria', 'PA-M4-UD05', 'VI', 1, 1],
                            ['Marketing de productos agropecuarios', 'PA-M4-UD06', 'VI', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Innovación y plan de negocios',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#D9F0C8',
                        'orden' => 2,
                        'unidades' => [
                            ['Innovación tecnológica', 'PA-M4-UD07', 'VI', 1, 1],
                            ['Plan de negocios', 'PA-M4-UD08', 'VI', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Experiencias formativas en situaciones reales de trabajo - Módulo 4',
                        'tipo' => 'ESRT',
                        'color' => '#BFDDF2',
                        'orden' => 3,
                        'unidades' => [
                            ['Experiencias formativas en situaciones reales de trabajo (ESRT)', 'PA-M4-ESRT', 'VI', 0, 3],
                        ],
                    ],
                ],
            ],
        ];
    }
}
