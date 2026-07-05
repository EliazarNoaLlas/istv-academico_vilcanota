<?php

namespace App\Services\Portafolios;

use App\Models\PortafolioDocumento;
use Illuminate\Support\Facades\DB;

class PortafolioReviewService
{
    public function __construct(private PortafolioDocumentoService $documentos) {}

    public function validar(PortafolioDocumento $documento, string $estado, ?string $observacion): PortafolioDocumento
    {
        return DB::transaction(function () use ($documento, $estado, $observacion) {
            $documento->estado = $estado === 'APROBADO' ? 'APROBADO' : 'OBSERVADO';
            $documento->observacion = $observacion;
            $documento->save();

            $portafolio = $documento->portafolio;
            if ($portafolio && $campo = PortafolioDocumentoService::CAMPO_POR_TIPO[$documento->tipo] ?? null) {
                $portafolio->{$campo} = $estado;
                $portafolio->estado = $this->documentos->estadoGeneral($portafolio);
                $portafolio->save();
            }

            return $documento->fresh('portafolio');
        });
    }
}
