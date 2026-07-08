<?php

namespace App\Services\Academic;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Reportes de carga académica docente (Excel/PDF) para el panel de Director. */
class DocenteExportService
{
    private const COLOR_CABECERA = 'FF0B1C3A';
    private const COLOR_CABECERA_CLARO = 'FF1A3160';

    /** Mismos colores que la leyenda de carga semanal del panel de director (dir-docentes-*.css). */
    private const ESTADO = [
        'SIN_CARGA' => ['bg' => 'FFE5E7EB', 'texto' => 'FF6B7280', 'label' => 'Sin carga'],
        'NORMAL' => ['bg' => 'FFBDF3CF', 'texto' => 'FF0E9980', 'label' => 'Carga normal'],
        'MODERADA' => ['bg' => 'FFFDE7A4', 'texto' => 'FFC9922A', 'label' => 'Carga moderada'],
        'ALTA' => ['bg' => 'FFFBD0A6', 'texto' => 'FFC2680F', 'label' => 'Carga alta'],
        'SOBRECARGA' => ['bg' => 'FFF6B3B3', 'texto' => 'FFE05050', 'label' => 'Sobrecarga'],
    ];

    private const TIPO_DOCENTE = ['ESPECIFICO' => 'Específico', 'GENERAL' => 'General'];

    public function __construct(private readonly DocenteService $docentes) {}

    public function exportExcel(): StreamedResponse
    {
        $docentes = $this->docentes->listarConCarga();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Docentes');

        $ultimaColumna = 'H';
        $sheet->getColumnDimension('A')->setWidth(23);
        foreach (['B', 'C', 'D', 'E', 'F', 'G'] as $col) {
            $sheet->getColumnDimension($col)->setWidth([
                'B' => 28, 'C' => 24, 'D' => 14, 'E' => 10, 'F' => 55, 'G' => 12,
            ][$col]);
        }
        $sheet->getColumnDimension('H')->setWidth(16);

        if (is_file(public_path('images/ministerioeducaionlogo.png'))) {
            $logoIzq = new Drawing();
            $logoIzq->setPath(public_path('images/ministerioeducaionlogo.png'));
            $logoIzq->setHeight(52);
            $logoIzq->setCoordinates('A1');
            $logoIzq->setOffsetX(4);
            $logoIzq->setOffsetY(4);
            $logoIzq->setWorksheet($sheet);
        }

        if (is_file(public_path('images/logo_pdf.png'))) {
            $logoDer = new Drawing();
            $logoDer->setPath(public_path('images/logo_pdf.png'));
            $logoDer->setHeight(52);
            $logoDer->setCoordinates('H1');
            $logoDer->setOffsetX(20);
            $logoDer->setOffsetY(4);
            $logoDer->setWorksheet($sheet);
        }

        $encabezados = [
            ['texto' => 'INSTITUTO DE EDUCACIÓN SUPERIOR TECNOLÓGICO PÚBLICO "VILCANOTA"', 'size' => 12],
            ['texto' => 'REPORTE DE CARGA ACADÉMICA DOCENTE', 'size' => 15],
            ['texto' => 'Dirección Académica · Gestión de carga docente por periodo', 'size' => 10, 'bold' => false, 'italic' => true],
        ];

        $fila = 1;
        foreach ($encabezados as $encabezado) {
            $sheet->mergeCells("A{$fila}:{$ultimaColumna}{$fila}");
            $sheet->setCellValue("A{$fila}", $encabezado['texto']);
            $estilo = $sheet->getStyle("A{$fila}");
            $estilo->getFont()->setBold($encabezado['bold'] ?? true)->setItalic($encabezado['italic'] ?? false)
                ->setSize($encabezado['size'])->getColor()->setARGB(self::COLOR_CABECERA);
            $estilo->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension($fila)->setRowHeight($fila === 2 ? 22 : 16);
            $fila++;
        }

        $fila++;
        $cabecera = ['N°', 'Docente', 'Especialidad', 'Tipo docente', 'Cursos asignados', 'Detalle de cursos', 'Horas/sem.', 'Estado'];
        $sheet->fromArray($cabecera, null, "A{$fila}");
        $estiloCabecera = $sheet->getStyle("A{$fila}:{$ultimaColumna}{$fila}");
        $estiloCabecera->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $estiloCabecera->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_CABECERA);
        $estiloCabecera->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getRowDimension($fila)->setRowHeight(26);
        $inicioTabla = $fila;
        $fila++;

        foreach ($docentes as $i => $docente) {
            $estado = self::ESTADO[$docente->estado_carga] ?? self::ESTADO['SIN_CARGA'];
            $detalle = $docente->cursos->isEmpty()
                ? 'Sin cursos asignados'
                : $docente->cursos->pluck('nombre_curso')->implode(' · ');

            $sheet->fromArray([
                $i + 1,
                trim("{$docente->usuario?->nombres} {$docente->usuario?->apellidos}"),
                $docente->especialidad ?? '—',
                self::TIPO_DOCENTE[$docente->tipo_docente] ?? $docente->tipo_docente,
                $docente->cursos_count,
                $detalle,
                $docente->carga_semanal,
                $estado['label'],
            ], null, "A{$fila}");

            $sheet->getStyle("A{$fila}:{$ultimaColumna}{$fila}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $sheet->getStyle("A{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            if ($i % 2 === 1) {
                $sheet->getStyle("A{$fila}:{$ultimaColumna}{$fila}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF7F9FC');
            }

            $estiloEstado = $sheet->getStyle("H{$fila}");
            $estiloEstado->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($estado['bg']);
            $estiloEstado->getFont()->setBold(true)->getColor()->setARGB($estado['texto']);
            $estiloEstado->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $fila++;
        }

        $sheet->getStyle("A{$inicioTabla}:{$ultimaColumna}".($fila - 1))
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFD8E3F0');

        $fila++;
        $sheet->setCellValue("A{$fila}", 'Fecha de emisión: '.now()->format('d/m/Y H:i'));
        $sheet->setCellValue("F{$fila}", 'Total docentes: '.$docentes->count());
        $sheet->getStyle("A{$fila}:{$ultimaColumna}{$fila}")->getFont()->setItalic(true)->setSize(9)->getColor()->setARGB('FF4A5E7A');

        $sheet->setSelectedCell('A1');

        $nombreArchivo = 'docentes-'.now()->format('Ymd-His').'.xlsx';

        return response()->streamDownload(
            fn () => (new Xlsx($spreadsheet))->save('php://output'),
            $nombreArchivo,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }

    public function exportPdf(?User $usuario = null): Response
    {
        $docentes = $this->docentes->listarConCarga();

        $pdf = Pdf::loadView('reportes.docentes-pdf', [
            'docentes' => $docentes,
            'estados' => self::ESTADO,
            'tiposDocente' => self::TIPO_DOCENTE,
            'generadoEn' => now(),
            'generadoPor' => $usuario ? trim("{$usuario->nombres} {$usuario->apellidos}") : null,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('docentes-'.now()->format('Ymd-His').'.pdf');
    }
}
