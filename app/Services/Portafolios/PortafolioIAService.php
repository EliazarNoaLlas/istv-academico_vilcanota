<?php

namespace App\Services\Portafolios;

use App\Models\PortafolioDocumento;

/**
 * Estructura preparada para la validacion con IA (Groq) de la Fase 6.
 * A proposito NO llama a ningun proveedor de IA todavia: solo valida que el
 * documento exista y tenga los datos minimos, y devuelve un estado
 * "pendiente" explicito. La integracion real con GeminiClient/GroqClient
 * se implementa en la fase de IA, nunca desde el navegador.
 */
class PortafolioIAService
{
    public function analizar(PortafolioDocumento $documento): array
    {
        if (! $documento->archivo) {
            return [
                'ok' => false,
                'estado_ia' => 'SIN_ARCHIVO',
                'mensaje' => 'El documento no tiene un archivo asociado para analizar.',
            ];
        }

        return [
            'ok' => true,
            'estado_ia' => 'PENDIENTE_IA',
            'mensaje' => 'Documento recibido. La validacion automatica con IA se activa en la Fase 6.',
            'documento' => [
                'id_documento' => $documento->id_documento,
                'tipo' => $documento->tipo,
                'titulo' => $documento->titulo,
            ],
        ];
    }
}
