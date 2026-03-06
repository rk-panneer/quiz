<?php

namespace App\Services\LLM\Contracts;

interface LLMProviderInterface
{
    /**
     * Generate embeddings for a given text.
     */
    public function generateEmbedding(string $text): array;

    /**
     * Get usage statistics for the last operation.
     */
    public function getUsage(): array;
}
