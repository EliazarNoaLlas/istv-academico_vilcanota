<?php

namespace App\Services\Horarios\Providers;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiHorarioProvider implements LlmHorarioProviderInterface
{
    public function generar(string $prompt): string
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-1.5-flash');

        if (! $apiKey) {
            throw new RuntimeException('GEMINI_API_KEY no esta configurado.');
        }

        $response = Http::timeout(120)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Gemini respondio con error: '.$response->status().' '.$response->body());
        }

        $texto = $response->json('candidates.0.content.parts.0.text');

        if (! is_string($texto) || trim($texto) === '') {
            throw new RuntimeException('Gemini no devolvio contenido de texto utilizable.');
        }

        return $texto;
    }
}
