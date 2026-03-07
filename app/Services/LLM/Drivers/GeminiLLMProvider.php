<?php

namespace App\Services\LLM\Drivers;

use App\Services\LLM\Contracts\LLMProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\LLMUsageLog;

class GeminiLLMProvider implements LLMProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $embeddingModel;
    protected array $lastUsage = [];

    public function __construct()
    {
        $this->apiKey = config('llm.drivers.gemini.api_key');
        $this->model = config('llm.drivers.gemini.model');
        $this->embeddingModel = config('llm.drivers.gemini.embedding_model');
    }


    public function generateEmbedding(string $text): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->embeddingModel}:embedContent?key={$this->apiKey}";

        $response = Http::post($url, [
            'model' => "models/{$this->embeddingModel}",
            'content' => [
                'parts' => [['text' => $text]]
            ]
        ]);

        if ($response->failed()) {
            Log::error('Gemini Embedding Error: ' . $response->body());
            throw new \Exception('Failed to generate embedding from Gemini.');
        }

        $data = $response->json();

        // Embeddings usage is usually not billed per token in the same way or returned in the same metadata block
        $this->lastUsage = [
            'input_tokens' => 0,
            'output_tokens' => 0,
            'total_tokens' => 0,
        ];

        LLMUsageLog::log('gemini-embedding', $this->lastUsage);

        return $data['embedding']['values'] ?? [];
    }

    public function getUsage(): array
    {
        return $this->lastUsage;
    }
}
