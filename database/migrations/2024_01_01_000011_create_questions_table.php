<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('question_text');
            $table->string('normalized_question_text', 500)->nullable()->unique();
            $table->string('question_type');
            $table->enum('media_type', ['none', 'image', 'video', 'audio'])->default('none');
            $table->string('media_url')->nullable();
            $table->string('media_path')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
