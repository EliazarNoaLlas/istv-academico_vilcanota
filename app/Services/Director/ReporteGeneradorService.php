<?php

namespace App\Services\Director;

use App\Models\ReporteGenerado;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteGeneradorService
{
    public function __construct(private readonly ReporteDataService $datos) {}

    public function generar(string $tipo, string $formato, User $usuario): ReporteGenerado
    {
        $reporte = $this->datos->obtener($tipo);
        $nombreArchivo = Str::slug($reporte['titulo']).'-'.now()->format('Ymd-His');

        $rutaRelativa = match ($formato) {
            'PDF' => $this->generarPdf($reporte, $nombreArchivo),
            'EXCEL' => $this->generarExcel($reporte, $nombreArchivo),
            'CSV' => $this->generarCsv($reporte, $nombreArchivo),
            default => throw new \InvalidArgumentException("Formato no soportado: {$formato}"),
        };

        return ReporteGenerado::create([
            'id_usuario' => $usuario->id_usuario,
            'tipo' => $tipo,
            'titulo' => $reporte['titulo'],
            'formato' => $formato,
            'filtros_json' => [],
            'archivo' => $rutaRelativa,
        ]);
    }

    private function generarPdf(array $reporte, string $nombreArchivo): string
    {
        $pdf = Pdf::loadView('reportes.pdf', $reporte);
        $ruta = "reportes/{$nombreArchivo}.pdf";
        Storage::disk('local')->put($ruta, $pdf->output());

        return $ruta;
    }

    private function generarExcel(array $reporte, string $nombreArchivo): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($reporte['columnas'], null, 'A1');
        $sheet->fromArray($reporte['filas'], null, 'A2');

        $ruta = "reportes/{$nombreArchivo}.xlsx";
        $rutaAbsoluta = Storage::disk('local')->path($ruta);
        Storage::disk('local')->makeDirectory('reportes');

        (new Xlsx($spreadsheet))->save($rutaAbsoluta);

        return $ruta;
    }

    private function generarCsv(array $reporte, string $nombreArchivo): string
    {
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, $reporte['columnas']);
        foreach ($reporte['filas'] as $fila) {
            fputcsv($handle, $fila);
        }
        rewind($handle);
        $contenido = stream_get_contents($handle);
        fclose($handle);

        $ruta = "reportes/{$nombreArchivo}.csv";
        Storage::disk('local')->put($ruta, $contenido);

        return $ruta;
    }
}
