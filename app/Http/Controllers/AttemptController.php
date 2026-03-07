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
        $key = 'quiz-start:' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);
            return back()->withErrors(['email' => "Too many attempts. Please try again in {$seconds} seconds."]);
        }
        \Illuminate\Support\Facades\RateLimiter::hit($key, 300); // 5 minutes

        $quiz = Quiz::published()
            ->where('slug', $slug)
            ->firstOrFail();

        $emailHash = hash('sha256', $request->email);

        if ($quiz->max_attempts_per_user !== null) {
            $attemptCount = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('email_hash', $emailHash)
                ->count();

            if ($attemptCount >= $quiz->max_attempts_per_user) {
                return back()->withErrors(['email' => 'Maximum attempts reached for this quiz.']);
            }
        }

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'email' => $request->email,
            'email_hash' => $emailHash,
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
