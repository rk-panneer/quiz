<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * Show the quiz landing page (description + email form).
     */
    public function show(string $slug)
    {
        $quiz = Quiz::published()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('quiz.show', compact('quiz'));
    }

    /**
     * Display the result page.
     */
    public function result(string $slug, int $attemptId)
    {
        $quiz = Quiz::where('slug', $slug)->firstOrFail();
        $attempt = QuizAttempt::where('id', $attemptId)
            ->where('quiz_id', $quiz->id)
            ->completed()
            ->firstOrFail();

        $this->authorize('view', $attempt);
        $attempt->setRelation('quiz', $quiz);
        $maxScore = $this->calculateMaxScore($quiz);

        return view('quiz.result', compact('quiz', 'attempt', 'maxScore'));
    }

    private function calculateMaxScore(Quiz $quiz): int
    {
        $quiz->loadMissing(['questions.options', 'questions.numberRanges', 'questions.keywords']);
        $max = 0;

        foreach ($quiz->questions as $question) {
            $max += match ($question->question_type) {
                'mcq_single', 'boolean', 'image_answer' => $question->options->where('is_correct', true)->sum('score'),
                'mcq_multiple' => $question->options->where('is_correct', true)->sum('score'),
                'number_range' => $question->numberRanges->max('score') ?? 0,
                'text_keywords' => $question->keywords->sum('score'),
                default => 0,
            };
        }

        return (int) $max;
    }
}
