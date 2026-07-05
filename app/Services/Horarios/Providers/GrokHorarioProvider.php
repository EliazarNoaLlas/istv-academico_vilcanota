<?php

namespace App\Services\Horarios\Providers;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GrokHorarioProvider implements LlmHorarioProviderInterface
{
    public function generar(string $prompt): string
    {
        $apiKey = config('services.grok.api_key');
        $model = config('services.grok.model');

        if (! $apiKey) {
            throw new RuntimeException('GROK_API_KEY no esta configurado.');
        }

        $response = Http::withToken($apiKey)
            ->timeout(120)
            ->post('https://api.x.ai/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.2,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Grok respondio con error: '.$response->status().' '.$response->body());
        }

        $texto = $response->json('choices.0.message.content');

        if (! is_string($texto) || trim($texto) === '') {
            throw new RuntimeException('Grok no devolvio contenido de texto utilizable.');
        }

        return $texto;
    }
}
