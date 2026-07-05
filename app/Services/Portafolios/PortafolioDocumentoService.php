<?php

namespace App\Services\Portafolios;

use App\Models\PortafolioDocumento;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PortafolioDocumentoService
{
    /** Mapea el tipo de documento al campo de estado en portafolio_docente. */
    public const CAMPO_POR_TIPO = [
        'SILABO' => 'silabo',
        'PLAN_SESION' => 'sesiones',
        'ASISTENCIA' => 'registro_asistencia',
        'NOTAS' => 'registro_notas',
        'ACTA' => 'actas',
    ];

    public function listar(
        ?int $idPortafolio = null,
        ?int $idCurso = null,
        ?string $tipo = null,
        ?int $idDocente = null,
        ?string $estado = null,
    ): Collection {
        return PortafolioDocumento::query()
            ->when($idPortafolio, fn ($q) => $q->where('id_portafolio', $idPortafolio))
            ->when($idCurso, fn ($q) => $q->whereHas('portafolio', fn ($p) => $p->where('id_curso', $idCurso)))
            ->when($idDocente, fn ($q) => $q->whereHas('portafolio', fn ($p) => $p->where('id_docente', $idDocente)))
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->with('portafolio.curso', 'portafolio.docente', 'portafolio.periodo')
            ->orderByDesc('fecha_subida')
            ->get();
    }

    public function eliminar(PortafolioDocumento $documento): void
    {
        $portafolio = $documento->portafolio;
        $archivo = $documento->archivo;

        $documento->delete();

        if ($archivo) {
            $this->borrarArchivoSeguro($archivo);
        }

        if ($portafolio && $campo = self::CAMPO_POR_TIPO[$documento->tipo] ?? null) {
            $portafolio->{$campo} = 'PENDIENTE';
            $portafolio->estado = $this->estadoGeneral($portafolio);
            $portafolio->save();
        }
    }

    public function estadoGeneral($portafolio): string
    {
        $componentes = [
            $portafolio->silabo,
            $portafolio->sesiones,
            $portafolio->registro_asistencia,
            $portafolio->registro_notas,
            $portafolio->actas,
        ];

        if (in_array('OBSERVADO', $componentes, true)) {
            return 'OBSERVADO';
        }
        if (in_array('EN_REVISION', $componentes, true)) {
            return 'EN_REVISION';
        }
        if (! in_array('PENDIENTE', $componentes, true)) {
            return 'COMPLETO';
        }

        return 'INCOMPLETO';
    }

    private function borrarArchivoSeguro(string $rutaRelativa): void
    {
        $disk = Storage::disk('local');
        $baseDir = realpath($disk->path('portafolios'));
        $rutaAbsoluta = realpath($disk->path($rutaRelativa));

        if ($baseDir && $rutaAbsoluta && str_starts_with($rutaAbsoluta, $baseDir)) {
            $disk->delete($rutaRelativa);

            return;
        }

        Log::error('Intento de eliminar archivo de portafolio fuera del directorio permitido', [
            'ruta' => $rutaRelativa,
        ]);
    }
}
