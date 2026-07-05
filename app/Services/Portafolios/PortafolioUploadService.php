<?php

namespace App\Services\Portafolios;

use App\Models\PortafolioDocente;
use App\Models\PortafolioDocumento;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PortafolioUploadService
{
    public function subir(
        UploadedFile $archivo,
        int $idDocente,
        int $idCurso,
        int $idPeriodo,
        string $tipo,
        string $titulo
    ): PortafolioDocumento {
        try {
            return DB::transaction(function () use ($archivo, $idDocente, $idCurso, $idPeriodo, $tipo, $titulo) {
                $portafolio = PortafolioDocente::firstOrCreate([
                    'id_docente' => $idDocente,
                    'id_curso' => $idCurso,
                    'id_periodo' => $idPeriodo,
                ]);

                $nombreArchivo = bin2hex(random_bytes(8)).'.'.$archivo->getClientOriginalExtension();
                $ruta = $archivo->storeAs('portafolios', $nombreArchivo, 'local');

                $documento = PortafolioDocumento::updateOrCreate(
                    ['id_portafolio' => $portafolio->id_portafolio, 'tipo' => $tipo, 'titulo' => $titulo],
                    ['archivo' => $ruta, 'estado' => 'SUBIDO', 'fecha_subida' => now()]
                );

                if ($campo = PortafolioDocumentoService::CAMPO_POR_TIPO[$tipo] ?? null) {
                    $portafolio->{$campo} = 'EN_REVISION';
                    $portafolio->estado = app(PortafolioDocumentoService::class)->estadoGeneral($portafolio);
                    $portafolio->save();
                }

                return $documento;
            });
        } catch (\Throwable $e) {
            Log::error('Error al subir documento de portafolio', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
