<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('question_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->string('keyword');
            $table->integer('score')->default(0);
            $table->timestamps();

            // Prevent duplicate keywords per question (case insensitive handled at app layer)
            $table->unique(['question_id', 'keyword']);
            $table->index('question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_keywords');
    }
};
