<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionEmbedding;
use App\Models\LLMUsageLog;
use App\Services\LLM\Contracts\LLMProviderInterface;
use Illuminate\Support\Collection;

class QuestionService
{
    protected LLMProviderInterface $llm;

    public function __construct(LLMProviderInterface $llm)
    {
        $this->llm = $llm;
    }

    /**
     * Check for duplicates (exact and semantic).
     *
     * @param string $text
     * @param int|null $excludeId
     * @return array
     */
    public function detectDuplicates(string $text, ?int $excludeId = null): array
    {
        $normalizedText = Question::normalizeText($text);

        // 1. Exact duplicate check
        $exactDuplicate = Question::where('normalized_question_text', $normalizedText)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->first();

        if ($exactDuplicate) {
            return [
                'type' => 'exact',
                'question' => $exactDuplicate,
                'score' => 1.0,
            ];
        }

        // 2. Semantic duplicate check
        $embedding = $this->llm->generateEmbedding($text);

        // Log usage if tokens were returned (Gemini embeddings usually don't return tokens in metadata the same way)
        LLMUsageLog::log('embedding', $this->llm->getUsage());

        $existingEmbeddings = QuestionEmbedding::with('question')
            ->when($excludeId, fn($q) => $q->where('question_id', '!=', $excludeId))
            ->get();

        foreach ($existingEmbeddings as $record) {
            $similarity = $this->calculateCosineSimilarity($embedding, $record->embedding);
            if ($similarity > 0.80) {
                return [
                    'type' => 'semantic',
                    'question' => $record->question,
                    'score' => $similarity,
                ];
            }
        }

        return [];
    }

    /**
     * Generate and save embedding for a question.
     */
    public function syncEmbedding(Question $question): void
    {
        $embedding = $this->llm->generateEmbedding($question->question_text);
        LLMUsageLog::log('embedding', $this->llm->getUsage());

        QuestionEmbedding::updateOrCreate(
            ['question_id' => $question->id],
            ['embedding' => $embedding]
        );
    }

    /**
     * Calculate cosine similarity between two vectors.
     */
    protected function calculateCosineSimilarity(array $vec1, array $vec2): float
    {
        $dotProduct = 0;
        $mag1 = 0;
        $mag2 = 0;

        $count = count($vec1);
        for ($i = 0; $i < $count; $i++) {
            $dotProduct += ($vec1[$i] * $vec2[$i]);
            $mag1 += ($vec1[$i] ** 2);
            $mag2 += ($vec2[$i] ** 2);
        }

        $mag1 = sqrt($mag1);
        $mag2 = sqrt($mag2);

        if ($mag1 * $mag2 == 0) {
            return 0;
        }

        return $dotProduct / ($mag1 * $mag2);
    }
}
