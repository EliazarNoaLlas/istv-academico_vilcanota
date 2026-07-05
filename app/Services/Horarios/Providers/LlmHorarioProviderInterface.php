<?php

namespace App\Services\Horarios\Providers;

interface LlmHorarioProviderInterface
{
    /** Envia el prompt al modelo y devuelve el texto crudo de la respuesta (puede traer JSON con texto alrededor). */
    public function generar(string $prompt): string;
}
