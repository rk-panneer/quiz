<?php

namespace App\Http\Controllers;

use App\Http\Requests\StartQuizRequest;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class AttemptController extends Controller
{
    /**
     * Validate email, create new attempt, redirect to Livewire player.
     */
    public function start(StartQuizRequest $request, string $slug)
    {
        $quiz = Quiz::published()
            ->where('slug', $slug)
            ->firstOrFail();

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'email' => $request->email,
            'started_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $request->session()->put("active_attempt_{$quiz->id}", $attempt->id);

        return redirect()->route('quiz.play', [
            'slug' => $quiz->slug,
            'attemptId' => $attempt->id,
        ]);
    }
}
