<?php

namespace App\Rules;

use App\Models\Question;
use App\Services\QuestionService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SemanticUniqueQuestion implements ValidationRule
{
    protected ?int $ignoreId;
    protected float $threshold;

    public function __construct(?int $ignoreId = null, float $threshold = 0.80)
    {
        $this->ignoreId = $ignoreId;
        $this->threshold = $threshold;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value))
            return;

        $service = app(QuestionService::class);

        try {
            $result = $service->detectDuplicates($value, $this->ignoreId);

            if (!empty($result)) {
                $type = $result['type'] === 'exact' ? 'An exact' : 'A very similar';
                $fail("{$type} version of this question already exists: \"{$result['question']->question_text}\" (Similarity: " . number_format($result['score'] * 100, 1) . "%)");
            }
        } catch (\Exception $e) {
            // If LLM fails, we don't want to block the user, just log it.
            \Illuminate\Support\Facades\Log::warning("Semantic validation failed: " . $e->getMessage());
        }
    }
}
