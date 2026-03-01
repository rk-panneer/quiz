<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('12345678'),
        ]);

        // 2. Create Sample Quiz
        $quiz = Quiz::create([
            'title' => 'General Knowledge Challenge',
            'slug' => 'general-knowledge',
            'description' => 'Test your wits across a variety of topics from science to history. This quiz demonstrates all supported question types.',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        // Question 1: MCQ Single
        $q1 = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Which planet is known as the "Red Planet"?',
            'question_type' => Question::TYPE_MCQ_SINGLE,
            'order' => 1,
        ]);
        $q1->options()->createMany([
            ['option_text' => 'Venus', 'is_correct' => false, 'score' => 0, 'order' => 1],
            ['option_text' => 'Mars', 'is_correct' => true, 'score' => 10, 'order' => 2],
            ['option_text' => 'Jupiter', 'is_correct' => false, 'score' => 0, 'order' => 3],
            ['option_text' => 'Saturn', 'is_correct' => false, 'score' => 0, 'order' => 4],
        ]);

        // Question 2: MCQ Multiple
        $q2 = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Which of the following are primary colors?',
            'question_type' => Question::TYPE_MCQ_MULTIPLE,
            'order' => 2,
        ]);
        $q2->options()->createMany([
            ['option_text' => 'Red', 'is_correct' => true, 'score' => 5, 'order' => 1],
            ['option_text' => 'Green', 'is_correct' => false, 'score' => 0, 'order' => 2],
            ['option_text' => 'Blue', 'is_correct' => true, 'score' => 5, 'order' => 3],
            ['option_text' => 'Yellow', 'is_correct' => true, 'score' => 5, 'order' => 4],
        ]);

        // Question 3: Boolean
        $q3 = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Is the Earth flat?',
            'question_type' => Question::TYPE_BOOLEAN,
            'order' => 3,
        ]);
        $q3->options()->createMany([
            ['option_text' => 'Yes', 'is_correct' => false, 'score' => 0, 'order' => 1],
            ['option_text' => 'No', 'is_correct' => true, 'score' => 10, 'order' => 2],
        ]);

        // Question 4: Number Range
        $q4 = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'In what year did the Apollo 11 moon landing happen?',
            'question_type' => Question::TYPE_NUMBER_RANGE,
            'order' => 4,
        ]);
        $q4->numberRanges()->createMany([
            ['min_value' => 1969, 'max_value' => 1969, 'score' => 20], // Exact match
            ['min_value' => 1960, 'max_value' => 1970, 'score' => 5],  // Close match
        ]);

        // Question 5: Text Keywords
        $q5 = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'What are the three branches of the US government?',
            'question_type' => Question::TYPE_TEXT_KEYWORDS,
            'order' => 5,
        ]);
        $q5->keywords()->createMany([
            ['keyword' => 'legislative', 'score' => 10],
            ['keyword' => 'executive', 'score' => 10],
            ['keyword' => 'judicial', 'score' => 10],
        ]);
    }
}
