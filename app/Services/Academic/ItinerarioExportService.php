<?php

namespace App\Services\Academic;

use App\Models\ItinerarioFormativo;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ItinerarioExportService
{
    private const CICLOS = ['I', 'II', 'III', 'IV', 'V', 'VI'];
    private const TOTAL_COLUMNAS = 16; // A..P
    private const COLOR_CABECERA = 'FF0B1C3A';

    private const COLOR_BLOQUE_DEFECTO = [
        'ESPECIALIDAD' => 'FFFFFFFF',
        'EMPLEABILIDAD' => 'FFDDEBF7',
        'ESRT' => 'FFBDD7EE',
        'TRANSVERSAL' => 'FFE2EFDA',
        'OTRO' => 'FFF2F2F2',
    ];

    public function exportExcel(ItinerarioFormativo $itinerario): StreamedResponse
    {
        $itinerario->loadMissing('programa', 'modulos.bloques.unidades');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Itinerario');

        $ultimaColumna = Coordinate::stringFromColumnIndex(self::TOTAL_COLUMNAS);

        $encabezados = [
            'INSTITUTO DE EDUCACIÓN SUPERIOR TECNOLÓGICO PÚBLICO "VILCANOTA"',
            'ITINERARIO FORMATIVO DEL PROGRAMA DE ESTUDIOS',
            mb_strtoupper($itinerario->programa->nombre),
            $itinerario->resolucion_oficio ?: 'SIN OFICIO / RESOLUCIÓN REGISTRADA',
        ];

        foreach ($encabezados as $i => $texto) {
            $fila = $i + 1;
            $sheet->mergeCells("A{$fila}:{$ultimaColumna}{$fila}");
            $sheet->setCellValue("A{$fila}", $texto);
            $sheet->getStyle("A{$fila}")->getFont()->setBold(true)->setSize($fila <= 2 ? 12 : 11);
            $sheet->getStyle("A{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $fila = 6;
        $cabecera = array_merge(
            ['Módulo', 'Unidades didácticas'],
            self::CICLOS,
            ['Teóricos', 'Prácticos', 'Créditos', 'Créditos módulo', 'De teoría', 'De práctica', 'Horas U.D.', 'Total horas'],
        );
        $sheet->fromArray($cabecera, null, "A{$fila}");
        $estiloCabecera = $sheet->getStyle("A{$fila}:{$ultimaColumna}{$fila}");
        $estiloCabecera->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $estiloCabecera->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_CABECERA);
        $estiloCabecera->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        $inicioTabla = $fila;
        $fila++;

        foreach ($itinerario->modulos as $modulo) {
            $sheet->mergeCells("A{$fila}:{$ultimaColumna}{$fila}");
            $sheet->setCellValue("A{$fila}", "MÓDULO {$modulo->numero_modulo}: ".mb_strtoupper($modulo->nombre));
            $estiloModulo = $sheet->getStyle("A{$fila}:{$ultimaColumna}{$fila}");
            $estiloModulo->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $estiloModulo->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1A3160');
            $fila++;

            foreach ($modulo->bloques as $bloque) {
                $color = $this->colorBloque($bloque->color_hex, $bloque->tipo_bloque);

                foreach ($bloque->unidades as $unidad) {
                    $filaDatos = [$bloque->nombre, $unidad->nombre];
                    foreach (self::CICLOS as $ciclo) {
                        $filaDatos[] = $unidad->ciclo === $ciclo ? $unidad->horas_ciclo : '';
                    }
                    $filaDatos = array_merge($filaDatos, [
                        $unidad->horas_teoricas_semanales,
                        $unidad->horas_practicas_semanales,
                        $unidad->creditos,
                        '',
                        $unidad->total_horas_teoria,
                        $unidad->total_horas_practica,
                        $unidad->horas_ud,
                        '',
                    ]);

                    $sheet->fromArray($filaDatos, null, "A{$fila}");
                    if ($color !== 'FFFFFFFF') {
                        $sheet->getStyle("A{$fila}:{$ultimaColumna}{$fila}")
                            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($color);
                    }
                    $fila++;
                }

                $sheet->setCellValue("B{$fila}", 'TOTAL '.mb_strtoupper($bloque->nombre));
                $sheet->setCellValue("L{$fila}", $bloque->creditos_bloque);
                $sheet->setCellValue("P{$fila}", $bloque->horas_bloque);
                $estiloSubtotal = $sheet->getStyle("A{$fila}:{$ultimaColumna}{$fila}");
                $estiloSubtotal->getFont()->setBold(true);
                $estiloSubtotal->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEBF0F8');
                $fila++;
            }

            $sheet->setCellValue("B{$fila}", "TOTAL MÓDULO {$modulo->numero_modulo}");
            $sheet->setCellValue("L{$fila}", $modulo->total_creditos);
            $sheet->setCellValue("P{$fila}", $modulo->total_horas);
            $estiloTotalModulo = $sheet->getStyle("A{$fila}:{$ultimaColumna}{$fila}");
            $estiloTotalModulo->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $estiloTotalModulo->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF122347');
            $fila++;
        }

        $sheet->setCellValue("B{$fila}", 'TOTAL ITINERARIO');
        $sheet->setCellValue("L{$fila}", $itinerario->total_creditos);
        $sheet->setCellValue("P{$fila}", $itinerario->total_horas);
        $estiloTotal = $sheet->getStyle("A{$fila}:{$ultimaColumna}{$fila}");
        $estiloTotal->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $estiloTotal->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_CABECERA);

        $sheet->getStyle("A{$inicioTabla}:{$ultimaColumna}{$fila}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FF000000');

        $sheet->getColumnDimension('A')->setWidth(26);
        $sheet->getColumnDimension('B')->setWidth(42);
        foreach (range(3, self::TOTAL_COLUMNAS) as $indice) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($indice))->setWidth(11);
        }

        $nombreArchivo = $this->nombreArchivo($itinerario, 'xlsx');

        return response()->streamDownload(
            fn () => (new Xlsx($spreadsheet))->save('php://output'),
            $nombreArchivo,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }

    public function exportPdf(ItinerarioFormativo $itinerario, ?User $usuario = null): Response
    {
        $itinerario->loadMissing('programa', 'modulos.bloques.unidades');

        $pdf = Pdf::loadView('reportes.itinerario-pdf', [
            'itinerario' => $itinerario,
            'ciclos' => self::CICLOS,
            'generadoEn' => now(),
            'generadoPor' => $usuario ? trim("{$usuario->nombres} {$usuario->apellidos}") : null,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($this->nombreArchivo($itinerario, 'pdf'));
    }

    private function colorBloque(?string $hex, string $tipo): string
    {
        $hex = ltrim((string) $hex, '#');

        if (strlen($hex) === 6 && ctype_xdigit($hex)) {
            return 'FF'.strtoupper($hex);
        }

        return self::COLOR_BLOQUE_DEFECTO[$tipo] ?? 'FFFFFFFF';
    }

    private function nombreArchivo(ItinerarioFormativo $itinerario, string $extension): string
    {
        return Str::slug("itinerario-{$itinerario->codigo}-{$itinerario->version}").'-'.now()->format('Ymd-His').".{$extension}";
    }
}
