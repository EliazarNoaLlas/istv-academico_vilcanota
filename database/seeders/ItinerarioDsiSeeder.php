<?php

namespace Database\Seeders;

/**
 * Itinerario Formativo oficial de Desarrollo de Sistemas de Información
 * (OFICIO 00883-2020-MINEDU/VMGP-DIGESUTPA-DISERTPA).
 */
class ItinerarioDsiSeeder extends ItinerarioProgramaSeeder
{
    protected function programa(): array
    {
        return [
            'codigo' => 'DSI',
            'codigos_anteriores' => [],
            'nombre' => 'Desarrollo de Sistemas de Información',
            'familia_profesional' => 'Computación e Informática',
            'duracion_ciclos' => 6,
        ];
    }

    protected function itinerario(): array
    {
        return [
            'codigo' => 'IT-DSI-2026',
            'nombre' => 'Itinerario Formativo del Programa de Estudios Desarrollo de Sistemas de Información',
            'resolucion_oficio' => 'OFICIO 00883-2020-MINEDU/VMGP-DIGESUTPA-DISERTPA',
            'descripcion' => 'Itinerario formativo oficial del programa de estudios Desarrollo de Sistemas de Información del IESTP Vilcanota.',
            'version' => '2026',
        ];
    }

    protected function estructura(): array
    {
        return [
            [
                'codigo' => 'MOD-01',
                'nombre' => 'Desarrollo de programas y pruebas integrales de sistemas de información',
                'competencia' => 'Desarrollar programas y pruebas integrales de sistemas de información.',
                'descripcion' => 'Módulo orientado a fundamentos de programación, diseño de software, bases de datos, algoritmos, web y programación orientada a objetos.',
                'bloques' => [
                    [
                        'nombre' => 'Especialidad técnica - Módulo 1',
                        'tipo' => 'ESPECIALIDAD',
                        'color' => '#FFFFFF',
                        'orden' => 1,
                        'descripcion' => 'Cursos técnicos específicos del primer módulo.',
                        'unidades' => [
                            ['Lógica de programación', 'DSI-M1-UD01', 'I', 1, 2],
                            ['Diseño de software', 'DSI-M1-UD02', 'I', 2, 2],
                            ['Modelamiento de bases de datos', 'DSI-M1-UD03', 'I', 1, 3],
                            ['Técnicas de programación', 'DSI-M1-UD04', 'I', 2, 2],
                            ['Algoritmos y estructuras de datos', 'DSI-M1-UD05', 'II', 2, 2],
                            ['Diseño web', 'DSI-M1-UD06', 'II', 2, 2],
                            ['Gestión de base de datos', 'DSI-M1-UD07', 'II', 1, 3],
                            ['Programación orientada a objetos', 'DSI-M1-UD08', 'II', 1, 2],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Comunicación y aplicaciones en internet',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#DDEEFF',
                        'orden' => 2,
                        'descripcion' => 'Competencias de empleabilidad en comunicación oral y uso de internet.',
                        'unidades' => [
                            ['Comunicación oral', 'DSI-M1-UD09', 'I', 1, 1],
                            ['Aplicaciones en internet', 'DSI-M1-UD10', 'I', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Textos y ofimática',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#D9F0C8',
                        'orden' => 3,
                        'descripcion' => 'Competencias de empleabilidad en interpretación de textos y herramientas ofimáticas.',
                        'unidades' => [
                            ['Interpretación y producción de textos', 'DSI-M1-UD11', 'II', 1, 1],
                            ['Ofimática', 'DSI-M1-UD12', 'II', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Experiencias formativas en situaciones reales de trabajo - Módulo 1',
                        'tipo' => 'ESRT',
                        'color' => '#BFDDF2',
                        'orden' => 4,
                        'descripcion' => 'Experiencias formativas en situaciones reales de trabajo del primer módulo.',
                        'unidades' => [
                            ['Experiencias formativas en situaciones reales de trabajo (ESRT)', 'DSI-M1-ESRT', 'II', 0, 4],
                        ],
                    ],
                ],
            ],
            [
                'codigo' => 'MOD-02',
                'nombre' => 'Desarrollo y puesta en producción de sistemas de información',
                'competencia' => 'Desarrollar y poner en producción sistemas de información.',
                'descripcion' => 'Módulo orientado al desarrollo web, móvil, seguridad, bases de datos no relacionales y despliegue de soluciones informáticas.',
                'bloques' => [
                    [
                        'nombre' => 'Especialidad técnica - Módulo 2',
                        'tipo' => 'ESPECIALIDAD',
                        'color' => '#FFFFFF',
                        'orden' => 1,
                        'descripcion' => 'Cursos técnicos específicos del segundo módulo.',
                        'unidades' => [
                            ['Administración de sitios web', 'DSI-M2-UD01', 'III', 2, 3],
                            ['Seguridad informática', 'DSI-M2-UD02', 'III', 1, 1],
                            ['Aplicaciones web', 'DSI-M2-UD03', 'III', 1, 4],
                            ['Aplicaciones móviles', 'DSI-M2-UD04', 'III', 1, 3],
                            ['Lenguaje de programación concurrente', 'DSI-M2-UD05', 'IV', 2, 3],
                            ['Lenguaje de programación web dinámico', 'DSI-M2-UD06', 'IV', 2, 3],
                            ['Base de datos no relacionales', 'DSI-M2-UD07', 'IV', 1, 3],
                            ['Modelamiento de software de entretenimiento', 'DSI-M2-UD08', 'IV', 1, 2],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Inglés para comunicación oral',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#DDEEFF',
                        'orden' => 2,
                        'descripcion' => 'Competencia de empleabilidad en comunicación oral en inglés.',
                        'unidades' => [
                            ['Inglés para la comunicación oral', 'DSI-M2-UD09', 'III', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Comprensión y redacción en inglés',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#D9F0C8',
                        'orden' => 3,
                        'descripcion' => 'Competencia de empleabilidad en comprensión y redacción en inglés.',
                        'unidades' => [
                            ['Comprensión y redacción en inglés', 'DSI-M2-UD10', 'IV', 0, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Experiencias formativas en situaciones reales de trabajo - Módulo 2',
                        'tipo' => 'ESRT',
                        'color' => '#BFDDF2',
                        'orden' => 4,
                        'descripcion' => 'Experiencias formativas en situaciones reales de trabajo del segundo módulo.',
                        'unidades' => [
                            ['Experiencias formativas en situaciones reales de trabajo (ESRT)', 'DSI-M2-ESRT', 'IV', 0, 4],
                        ],
                    ],
                ],
            ],
            [
                'codigo' => 'MOD-03',
                'nombre' => 'Administración de sistemas de información',
                'competencia' => 'Administrar sistemas de información en organizaciones.',
                'descripcion' => 'Módulo orientado a gestión de proyectos, calidad de software, inteligencia de negocios, servidores, redes, auditoría, inteligencia artificial e innovación.',
                'bloques' => [
                    [
                        'nombre' => 'Especialidad técnica - Módulo 3',
                        'tipo' => 'ESPECIALIDAD',
                        'color' => '#FFFFFF',
                        'orden' => 1,
                        'descripcion' => 'Cursos técnicos específicos del tercer módulo.',
                        'unidades' => [
                            ['Gestión de proyectos de TI', 'DSI-M3-UD01', 'V', 1, 2],
                            ['Pruebas y calidad del software', 'DSI-M3-UD02', 'V', 1, 3],
                            ['Inteligencia de negocios', 'DSI-M3-UD03', 'V', 1, 2],
                            ['Gestión de servicios de TI', 'DSI-M3-UD04', 'V', 1, 3],
                            ['Gestión de servidores', 'DSI-M3-UD05', 'VI', 1, 2],
                            ['Gestión de redes informáticas', 'DSI-M3-UD06', 'VI', 1, 3],
                            ['Soporte de auditoría de TI', 'DSI-M3-UD07', 'VI', 2, 2],
                            ['Auditoría de software', 'DSI-M3-UD08', 'VI', 1, 1],
                            ['Inteligencia artificial', 'DSI-M3-UD09', 'VI', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Innovación y ética',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#DDEEFF',
                        'orden' => 2,
                        'descripcion' => 'Competencias de empleabilidad en innovación tecnológica y comportamiento ético.',
                        'unidades' => [
                            ['Fundamentos de innovación tecnológica', 'DSI-M3-UD10', 'V', 1, 1],
                            ['Comportamiento ético', 'DSI-M3-UD11', 'V', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Solución de problemas e innovación tecnológica',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#D9F0C8',
                        'orden' => 3,
                        'descripcion' => 'Competencias de empleabilidad en solución de problemas e innovación tecnológica.',
                        'unidades' => [
                            ['Solución de problemas', 'DSI-M3-UD12', 'VI', 1, 1],
                            ['Innovación tecnológica', 'DSI-M3-UD13', 'VI', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Experiencias formativas en situaciones reales de trabajo - Módulo 3',
                        'tipo' => 'ESRT',
                        'color' => '#BFDDF2',
                        'orden' => 4,
                        'descripcion' => 'Experiencias formativas en situaciones reales de trabajo del tercer módulo.',
                        'unidades' => [
                            ['Experiencias formativas en situaciones reales de trabajo (ESRT)', 'DSI-M3-ESRT', 'VI', 0, 4],
                        ],
                    ],
                ],
            ],
        ];
    }
}
