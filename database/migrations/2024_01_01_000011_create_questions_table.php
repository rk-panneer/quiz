<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->text('question_text');
            $table->string('question_type'); // NOT enum – allows future types without schema change
            $table->unsignedInteger('order')->default(0)->index();
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->index(['quiz_id', 'order']); // composite index for ordered fetching
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
