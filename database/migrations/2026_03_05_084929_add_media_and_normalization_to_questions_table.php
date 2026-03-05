<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->enum('media_type', ['none', 'image', 'video', 'audio'])->default('none')->after('question_text');
            $table->string('media_url')->nullable()->after('media_type');
            $table->string('media_path')->nullable()->after('media_url');
            $table->string('normalized_question_text', 500)->nullable()->after('question_text');
            $table->unsignedInteger('points')->default(1)->after('is_required');
            $table->unique('normalized_question_text', 'idx_unique_normalized_text');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropUnique('idx_unique_normalized_text');
            $table->dropColumn(['media_type', 'media_url', 'media_path', 'normalized_question_text']);
        });
    }
};
