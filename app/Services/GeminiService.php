<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    /**
     * Create a new class instance.
     */
    public function analyze(string $prompt): string
    {
        $response = Http::post(env('GEMINI_API_URL') . '?key=' . env('GEMINI_API_KEY'), [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            return $response->json('candidates.0.content.parts.0.text') ?? 'Tidak ada respon dari AI.';
        }

        return 'Gagal terhubung ke Gemini API: ' . $response->body();
    }
}
