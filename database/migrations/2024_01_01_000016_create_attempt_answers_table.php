<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->json('answer')->nullable();
            $table->string('answer_media_path')->nullable();
            $table->integer('score_awarded')->default(0);
            $table->timestamps();
            $table->unique(['attempt_id', 'question_id']);
            $table->index('attempt_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempt_answers');
    }
};
