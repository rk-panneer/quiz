<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Services\QuestionService;
use Illuminate\Console\Command;

class SyncQuestionEmbeddings extends Command
{
    protected $signature = 'questions:sync-embeddings';
    protected $description = 'Generate missing embeddings for all questions';

    public function handle(QuestionService $service)
    {
        $questions = Question::all();
        $count = $questions->count();
        $this->info("Found {$count} questions. Updating embeddings...");

        $bar = $this->output->createProgressBar($count);

        foreach ($questions as $question) {
            try {
                $service->syncEmbedding($question);
            } catch (\Exception $e) {
                $this->error("\nFailed for question #{$question->id}: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Embeddings synchronization complete.");
    }
}
