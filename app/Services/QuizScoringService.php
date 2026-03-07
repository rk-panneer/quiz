<?php

namespace App\Services;

use App\Models\AttemptAnswer;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;

/**
 * QuizScoringService
 *
 * Responsible for server-side calculation of quiz scores.
 * NEVER expose scoring logic, correct answers, or keyword lists to the frontend.
 */
class QuizScoringService
{
    /**
     * Calculate scores for all answers in an attempt and persist results.
     *
     * @param  Quiz        $quiz    The quiz being scored
     * @param  QuizAttempt $attempt The attempt record
     * @param  array       $answers Raw submitted answers keyed by question_id
     * @return int                  Total score awarded
     */
    public function calculate(Quiz $quiz, QuizAttempt $attempt, array $answers): int
    {
        // Eager-load all question data needed for scoring
        $questions = $quiz->questions()
            ->with(['options', 'numberRanges', 'keywords'])
            ->get()
            ->keyBy('id');

        $totalScore = 0;

        foreach ($questions as $question) {
            $rawAnswer = $answers[$question->id] ?? null;

            $score = match ($question->question_type) {
                Question::TYPE_MCQ_SINGLE => $this->scoreMcqSingle($question, $rawAnswer),
                Question::TYPE_MCQ_MULTIPLE => $this->scoreMcqMultiple($question, $rawAnswer),
                Question::TYPE_NUMBER_RANGE => $this->scoreNumberRange($question, $rawAnswer),
                Question::TYPE_TEXT_KEYWORDS => $this->scoreTextKeywords($question, $rawAnswer),
                Question::TYPE_BOOLEAN => $this->scoreBoolean($question, $rawAnswer),
                Question::TYPE_IMAGE_ANSWER => $this->scoreMcqSingle($question, $rawAnswer),
                default => 0,
            };

            $answerData = [
                'score_awarded' => $score,
                'answer' => $rawAnswer,
            ];

            AttemptAnswer::updateOrCreate(
                ['attempt_id' => $attempt->id, 'question_id' => $question->id],
                $answerData
            );

            $totalScore += $score;
        }

        return $totalScore;
    }

    // ─── Scoring Logic Per Type ───────────────────────────────────

    /**
     * MCQ Single: only one option may be selected; award its score if correct.
     */
    private function scoreMcqSingle(Question $question, mixed $rawAnswer): int
    {
        if (!is_numeric($rawAnswer)) {
            return 0;
        }

        $selectedId = (int) $rawAnswer;
        $option = $question->options->firstWhere('id', $selectedId);

        if (!$option) {
            return 0;
        }

        return $option->is_correct ? ($option->score ?? 0) : 0;
    }

    /**
     * MCQ Multiple: sum scores for each correctly selected option.
     * Validates that selected option IDs belong to this question.
     */
    private function scoreMcqMultiple(Question $question, mixed $rawAnswer): int
    {
        if (!is_array($rawAnswer) || empty($rawAnswer)) {
            return 0;
        }

        $selectedIds = array_unique(array_map('intval', $rawAnswer));
        $validOptionIds = $question->options->pluck('id')->toArray();

        $total = 0;

        foreach ($selectedIds as $id) {
            if (!in_array($id, $validOptionIds)) {
                continue;
            }

            $option = $question->options->firstWhere('id', $id);

            if ($option && $option->is_correct) {
                $total += ($option->score ?? 0);
            }
        }

        return max(0, $total);
    }

    /**
     * Number Range: find the matching range for the submitted numeric value.
     * Ranges must not overlap; only one range will match.
     */
    private function scoreNumberRange(Question $question, mixed $rawAnswer): int
    {
        if (!is_numeric($rawAnswer)) {
            return 0;
        }

        $value = (float) $rawAnswer;

        foreach ($question->numberRanges as $range) {
            if ($value >= (float) $range->min_value && $value <= (float) $range->max_value) {
                return $range->score ?? 0;
            }
        }

        return 0;
    }

    /**
     * Text Keywords: normalize input, split into words, match against DB keywords.
     * Case-insensitive, duplicates ignored. Score = sum of matched keyword scores.
     */
    private function scoreTextKeywords(Question $question, mixed $rawAnswer): int
    {
        if (!is_string($rawAnswer) || empty(trim($rawAnswer))) {
            return 0;
        }

        // Normalize full input text: lowercase, trim, collapse whitespace
        $normalized = mb_strtolower(trim(preg_replace('/\s+/', ' ', $rawAnswer)));

        if ($normalized === '') {
            return 0;
        }

        $total = 0;

        foreach ($question->keywords as $keywordModel) {
            $kw = mb_strtolower(trim($keywordModel->keyword));

            if ($kw === '') {
                continue;
            }

            // Match keyword as substring inside the normalized text; supports multi-word keywords
            if (mb_stripos($normalized, $kw) !== false) {
                $total += ($keywordModel->score ?? 0);
            }
        }

        return max(0, $total);
    }

    /**
     * Boolean: treated identically to MCQ Single (Yes/No are options).
     */
    private function scoreBoolean(Question $question, mixed $rawAnswer): int
    {
        return $this->scoreMcqSingle($question, $rawAnswer);
    }
}
