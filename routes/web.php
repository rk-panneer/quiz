<?php

use App\Http\Controllers\AttemptController;
use App\Http\Controllers\QuizController;
use App\Livewire\QuizPlayer;
use App\Models\Quiz;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $quizzes = Quiz::published()->withCount('questions')->get();
    return view('home', compact('quizzes'));
})->name('home');

Route::middleware(['web'])->group(function () {
    // Quiz Flow
    Route::get('/quiz/{slug}', [QuizController::class, 'show'])->name('quiz.show');
    Route::post('/quiz/{slug}/start', [AttemptController::class, 'start'])->name('quiz.start');

    // Livewire Quiz Player (SPA-like experience)
    Route::get('/quiz/{slug}/play/{attemptId}', QuizPlayer::class)->name('quiz.play');
    Route::get('/quiz/{slug}/result/{attempt}', [QuizController::class, 'result'])->name('quiz.result');
});
