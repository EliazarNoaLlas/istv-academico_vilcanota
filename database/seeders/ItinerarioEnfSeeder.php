<?php

namespace Database\Seeders;

/**
 * Itinerario Formativo oficial de Enfermería Técnica
 * (OFICIO 00883-2020-MINEDU/VMGP-DIGESUTPA-DISERTPA).
 */
class ItinerarioEnfSeeder extends ItinerarioProgramaSeeder
{
    protected function programa(): array
    {
        return [
            'codigo' => 'ENF',
            'codigos_anteriores' => [],
            'nombre' => 'Enfermería Técnica',
            'familia_profesional' => 'Salud',
            'duracion_ciclos' => 6,
        ];
    }

    protected function itinerario(): array
    {
        return [
            'codigo' => 'IT-ENF-2026',
            'nombre' => 'Itinerario Formativo del Programa de Estudios Enfermería Técnica',
            'resolucion_oficio' => 'OFICIO 00883-2020-MINEDU/VMGP-DIGESUTPA-DISERTPA',
            'descripcion' => 'Itinerario formativo oficial del programa de estudios Enfermería Técnica del IESTP Vilcanota.',
            'version' => '2026',
        ];
    }

    protected function estructura(): array
    {
        return [
            [
                'codigo' => 'MOD-01',
                'nombre' => 'Asistencia en la promoción de la salud y prevención de enfermedades',
                'competencia' => 'Asistir en actividades de promoción de la salud, prevención de enfermedades y primeros auxilios.',
                'bloques' => [
                    [
                        'nombre' => 'Especialidad técnica - Módulo 1',
                        'tipo' => 'ESPECIALIDAD',
                        'color' => '#FFFFFF',
                        'orden' => 1,
                        'unidades' => [
                            ['Salud comunitaria', 'ENF-M1-UD01', 'I', 1, 2],
                            ['Atención de enfermería a la persona, familia y comunidad', 'ENF-M1-UD02', 'I', 1, 1],
                            ['Promoción de la salud', 'ENF-M1-UD03', 'I', 1, 2],
                            ['Educación para la salud', 'ENF-M1-UD04', 'I', 1, 2],
                            ['Anatomía y fisiología humana', 'ENF-M1-UD05', 'I', 2, 2],
                            ['Asistencia de enfermería en inmunizaciones', 'ENF-M1-UD06', 'II', 1, 3],
                            ['Epidemiología', 'ENF-M1-UD07', 'II', 1, 2],
                            ['Primeros auxilios', 'ENF-M1-UD08', 'II', 1, 3],
                            ['Salud ocupacional', 'ENF-M1-UD09', 'II', 1, 2],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Comunicación y aplicaciones en internet',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#DDEEFF',
                        'orden' => 2,
                        'unidades' => [
                            ['Comunicación oral', 'ENF-M1-UD10', 'I', 1, 1],
                            ['Aplicaciones en internet', 'ENF-M1-UD11', 'I', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Textos y ofimática',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#D9F0C8',
                        'orden' => 3,
                        'unidades' => [
                            ['Interpretación y producción textos', 'ENF-M1-UD12', 'II', 1, 1],
                            ['Ofimática', 'ENF-M1-UD13', 'II', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Experiencias formativas en situaciones reales de trabajo - Módulo 1',
                        'tipo' => 'ESRT',
                        'color' => '#BFDDF2',
                        'orden' => 4,
                        'unidades' => [
                            ['Experiencias formativas en situaciones reales de trabajo (ESRT)', 'ENF-M1-ESRT', 'II', 0, 4],
                        ],
                    ],
                ],
            ],
            [
                'codigo' => 'MOD-02',
                'nombre' => 'Asistencia en la atención de las necesidades básicas de la salud',
                'competencia' => 'Asistir en procedimientos hospitalarios, administración de medicamentos y atención básica de salud.',
                'bloques' => [
                    [
                        'nombre' => 'Especialidad técnica - Módulo 2',
                        'tipo' => 'ESPECIALIDAD',
                        'color' => '#FFFFFF',
                        'orden' => 1,
                        'unidades' => [
                            ['Asistencia básica hospitalaria', 'ENF-M2-UD01', 'III', 2, 2],
                            ['Administración de medicamentos', 'ENF-M2-UD02', 'III', 2, 3],
                            ['Documentación en salud', 'ENF-M2-UD03', 'III', 1, 1],
                            ['Bioseguridad hospitalaria', 'ENF-M2-UD04', 'III', 1, 3],
                            ['Atención de enfermería al usuario quirúrgico', 'ENF-M2-UD05', 'IV', 1, 2],
                            ['Procesos de enfermería en urgencias y emergencias', 'ENF-M2-UD06', 'IV', 1, 3],
                            ['Procedimientos invasivos y no invasivos', 'ENF-M2-UD07', 'IV', 2, 2],
                            ['Estadística aplicada a la enfermería', 'ENF-M2-UD08', 'IV', 1, 1],
                            ['Nutrición hospitalaria', 'ENF-M2-UD09', 'IV', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Inglés e innovación',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#DDEEFF',
                        'orden' => 2,
                        'unidades' => [
                            ['Inglés para la comunicación oral', 'ENF-M2-UD10', 'III', 1, 1],
                            ['Fundamentos de innovación tecnológica', 'ENF-M2-UD11', 'III', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Redacción e innovación',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#D9F0C8',
                        'orden' => 3,
                        'unidades' => [
                            ['Comprensión y redacción en inglés', 'ENF-M2-UD12', 'IV', 1, 1],
                            ['Innovación tecnológica', 'ENF-M2-UD13', 'IV', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Experiencias formativas en situaciones reales de trabajo - Módulo 2',
                        'tipo' => 'ESRT',
                        'color' => '#BFDDF2',
                        'orden' => 4,
                        'unidades' => [
                            ['Experiencias formativas en situaciones reales de trabajo (ESRT)', 'ENF-M2-ESRT', 'IV', 0, 4],
                        ],
                    ],
                ],
            ],
            [
                'codigo' => 'MOD-03',
                'nombre' => 'Asistencia en los cuidados integrales de la salud especializada',
                'competencia' => 'Asistir en cuidados especializados de enfermería en diferentes etapas y condiciones de salud.',
                'bloques' => [
                    [
                        'nombre' => 'Especialidad técnica - Módulo 3',
                        'tipo' => 'ESPECIALIDAD',
                        'color' => '#FFFFFF',
                        'orden' => 1,
                        'unidades' => [
                            ['Atención en salud materno neonatal', 'ENF-M3-UD01', 'V', 1, 3],
                            ['Asistencia en salud del niño y adolescente con patologías', 'ENF-M3-UD02', 'V', 2, 2],
                            ['Atención de enfermería al adulto con patologías', 'ENF-M3-UD03', 'V', 1, 3],
                            ['Atención de enfermería en salud mental', 'ENF-M3-UD04', 'V', 1, 3],
                            ['Atención de enfermería en salud bucal', 'ENF-M3-UD05', 'VI', 1, 2],
                            ['Atención de enfermería al adulto mayor', 'ENF-M3-UD06', 'VI', 1, 2],
                            ['Asistencia en fisioterapia y rehabilitación', 'ENF-M3-UD07', 'VI', 1, 3],
                            ['Atención de enfermería al paciente en estado crítico', 'ENF-M3-UD08', 'VI', 1, 2],
                            ['Asistencia de enfermería en cuidados oncológicos', 'ENF-M3-UD09', 'VI', 1, 2],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Ética',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#DDEEFF',
                        'orden' => 2,
                        'unidades' => [
                            ['Comportamiento ético', 'ENF-M3-UD10', 'V', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Empleabilidad - Solución de problemas',
                        'tipo' => 'EMPLEABILIDAD',
                        'color' => '#D9F0C8',
                        'orden' => 3,
                        'unidades' => [
                            ['Solución de problemas', 'ENF-M3-UD11', 'VI', 1, 1],
                        ],
                    ],
                    [
                        'nombre' => 'Experiencias formativas en situaciones reales de trabajo - Módulo 3',
                        'tipo' => 'ESRT',
                        'color' => '#BFDDF2',
                        'orden' => 4,
                        'unidades' => [
                            ['Experiencias formativas en situaciones reales de trabajo (ESRT)', 'ENF-M3-ESRT', 'VI', 0, 4],
                        ],
                    ],
                ],
            ],
        ];
    }
}
