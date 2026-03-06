<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->string('email')->index();
            $table->string('email_hash')->index()->nullable();
            $table->integer('total_score')->nullable(); // null until attempt completed
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->string('ip_address', 45); // supports IPv6
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['quiz_id', 'email']);
            $table->index(['quiz_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
