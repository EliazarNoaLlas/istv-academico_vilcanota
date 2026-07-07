<?php

namespace App\Services\Portafolios;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\PortafolioDocente;
use App\Models\PortafolioDocumento;
use App\Models\Scopes\CoordinadorDocenteProgramaScope;
use App\Models\Scopes\CoordinadorProgramaDirectoScope;
use App\Services\Notificaciones\NotificacionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PortafolioUploadService
{
    public function __construct(private NotificacionService $notificaciones) {}

    public function subir(
        UploadedFile $archivo,
        int $idDocente,
        int $idCurso,
        int $idPeriodo,
        string $tipo,
        string $titulo
    ): PortafolioDocumento {
        try {
            $documento = DB::transaction(function () use ($archivo, $idDocente, $idCurso, $idPeriodo, $tipo, $titulo) {
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

        // Se buscan sin el scope de aislamiento porque $idCurso/$idDocente ya
        // fueron resueltos de forma segura por el controlador (nunca vienen
        // del cliente sin validar); aqui solo se usan para armar el aviso.
        $curso = Curso::withoutGlobalScope(CoordinadorProgramaDirectoScope::class)->find($idCurso);
        $docente = Docente::withoutGlobalScope(CoordinadorDocenteProgramaScope::class)->with('usuario')->find($idDocente);

        if ($curso && $docente) {
            $this->notificaciones->notificarNuevoDocumentoPortafolio($documento, $curso, $docente);
        }

        return $documento;
    }
}
