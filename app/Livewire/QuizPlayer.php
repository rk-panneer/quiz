<?php

namespace App\Livewire;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\QuizScoringService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class QuizPlayer extends Component
{
    use WithFileUploads;

    public Quiz $quiz;
    public QuizAttempt $attempt;
    public $questions;
    public int $currentIndex = 0;
    public $answers = [];
    public bool $isCompleted = false;
    public int $totalScore = 0;
    public int $maxScore = 0;
    public ?int $timeLimitSeconds = null;
    public bool $timeExpired = false;

    public function mount(string $slug, int $attemptId)
    {
        $this->quiz = Quiz::published()
            ->where('slug', $slug)
            ->with(['questions.options', 'questions.numberRanges', 'questions.keywords'])
            ->firstOrFail();

        $this->attempt = QuizAttempt::where('id', $attemptId)
            ->where('quiz_id', $this->quiz->id)
            ->firstOrFail();

        // SECURITY: Verify browser session owns this attempt to prevent enumeration
        if (Session::get("active_attempt_{$this->quiz->id}") !== $this->attempt->id) {
            $this->redirectRoute('quiz.show', $this->quiz->slug);
            return;
        }

        if ($this->attempt->isCompleted()) {
            return redirect()->route('quiz.result', [$this->quiz->slug, $this->attempt->id]);
        }

        $this->questions = $this->quiz->questions;

        $this->answers = Session::get("quiz_answers_{$this->attempt->id}", []);
        $this->currentIndex = (int) Session::get("quiz_index_{$this->attempt->id}", 0);

        $this->initializeCurrentAnswer();

        $count = $this->questions->count();
        if ($count === 0) {
            $this->currentIndex = 0;
            Session::put("quiz_index_{$this->attempt->id}", 0);
        } else {
            if ($this->currentIndex < 0) {
                $this->currentIndex = 0;
            }
            if ($this->currentIndex >= $count) {
                $this->currentIndex = $count - 1;
            }
            Session::put("quiz_index_{$this->attempt->id}", $this->currentIndex);
        }

        // Initialize Time Limit
        if ($this->quiz->time_limit_minutes) {
            $startTime = $this->attempt->started_at ?? $this->attempt->created_at;
            $endTime = $startTime->addMinutes($this->quiz->time_limit_minutes);
            $this->timeLimitSeconds = max(0, now()->diffInSeconds($endTime, false));

            if ($this->timeLimitSeconds <= 0) {
                $this->timeExpired = true;
                $this->submit();
            }
        }
    }

    public function nextQuestion($answer)
    {
        if ($this->timeLimitSeconds !== null && $this->timeLimitSeconds <= 0) {
            $this->timeExpired = true;
            $this->submit();
            return;
        }

        $currentQuestion = $this->questions[$this->currentIndex];

        if ($currentQuestion->is_required) {
            if ($currentQuestion->question_type === 'mcq_multiple') {
                if (!is_array($answer) || count($answer) === 0) {
                    return;
                }
            } elseif ($currentQuestion->question_type === 'image_answer') {
                if (!$answer && !isset($this->answers[$currentQuestion->id])) {
                    return;
                }
            } else {
                if ($answer === null || $answer === '') {
                    return;
                }
            }
        }

        if ($currentQuestion->question_type === 'image_answer' && $answer instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
            $path = $answer->store('answers', 'public');
            $this->answers[$currentQuestion->id] = [
                'type' => 'image',
                'path' => $path,
            ];
        } else {
            $this->answers[$currentQuestion->id] = $answer;
        }

        Session::put("quiz_answers_{$this->attempt->id}", $this->answers);

        if ($this->currentIndex < $this->questions->count() - 1) {
            $this->currentIndex++;
            Session::put("quiz_index_{$this->attempt->id}", $this->currentIndex);
            $this->initializeCurrentAnswer();
        } else {
            $this->submit();
        }
    }

    private function initializeCurrentAnswer()
    {
        $currentQuestion = $this->questions[$this->currentIndex];

        if (!isset($this->answers[$currentQuestion->id])) {
            $this->answers[$currentQuestion->id] = ($currentQuestion->question_type === 'mcq_multiple') ? [] : null;
        }
    }

    public function previousQuestion()
    {
        if ($this->currentIndex > 0) {
            $this->currentIndex--;
            Session::put("quiz_index_{$this->attempt->id}", $this->currentIndex);
            $this->initializeCurrentAnswer();
        }
    }

    public function submit()
    {
        // Guard: Prevent double-execution if JS and PHP timers trigger simultaneously
        if ($this->isCompleted || $this->attempt->fresh()?->isCompleted()) {
            return;
        }
        $this->isCompleted = true;

        $scoringService = app(QuizScoringService::class);

        $this->totalScore = $scoringService->calculate($this->quiz, $this->attempt, $this->answers);

        $this->attempt->update([
            'total_score' => $this->totalScore,
            'completed_at' => now(),
        ]);

        Session::forget("quiz_answers_{$this->attempt->id}");
        Session::forget("active_attempt_{$this->quiz->id}");
        Session::forget("quiz_index_{$this->attempt->id}");

        $this->isCompleted = true;
        $this->maxScore = $this->calculateMaxScore();
    }

    private function calculateMaxScore(): int
    {
        $max = 0;
        foreach ($this->questions as $question) {
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

    public function render()
    {
        return view('livewire.quiz-player')->layout('layouts.quiz');
    }
}
