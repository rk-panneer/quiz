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
            'question_text' => 'Which planet is known as the "Red Planet"?',
            'question_type' => Question::TYPE_MCQ_SINGLE,
            
            'order' => 1,
        ]);
        $quiz->questions()->syncWithoutDetaching([$q1->id => ['order' => 1]]);

        $q1->options()->createMany([
            ['option_text' => 'Venus', 'is_correct' => false, 'score' => 0, 'order' => 1],
            ['option_text' => 'Mars', 'is_correct' => true, 'score' => 2, 'order' => 2],
            ['option_text' => 'Jupiter', 'is_correct' => false, 'score' => 0, 'order' => 3],
            ['option_text' => 'Saturn', 'is_correct' => false, 'score' => 0, 'order' => 4],
        ]);

        // Question 2: MCQ Multiple
        $q2 = Question::create([
            'question_text' => 'Which of the following are primary colors?',
            'question_type' => Question::TYPE_MCQ_MULTIPLE,
            
            'order' => 2,
        ]);
        $quiz->questions()->syncWithoutDetaching([$q2->id => ['order' => 2]]);

        $q2->options()->createMany([
            ['option_text' => 'Red', 'is_correct' => true, 'score' => 1, 'order' => 1],
            ['option_text' => 'Green', 'is_correct' => false, 'score' => 0, 'order' => 2],
            ['option_text' => 'Blue', 'is_correct' => true, 'score' => 1, 'order' => 3],
            ['option_text' => 'Yellow', 'is_correct' => true, 'score' => 1, 'order' => 4],
        ]);

        // Question 3: Boolean
        $q3 = Question::create([
            'question_text' => 'Is the Earth flat?',
            'question_type' => Question::TYPE_BOOLEAN,
            
            'order' => 3,
        ]);
        $quiz->questions()->syncWithoutDetaching([$q3->id => ['order' => 3]]);

        $q3->options()->createMany([
            ['option_text' => 'Yes', 'is_correct' => false, 'score' => 0, 'order' => 1],
            ['option_text' => 'No', 'is_correct' => true, 'score' => 1, 'order' => 2],
        ]);

        // Question 4: Number Range
        $q4 = Question::create([
            'question_text' => 'In what year did the Apollo 11 moon landing happen?',
            'question_type' => Question::TYPE_NUMBER_RANGE,
            
            'order' => 4,
        ]);
        $quiz->questions()->syncWithoutDetaching([$q4->id => ['order' => 4]]);

        $q4->numberRanges()->createMany([
            ['min_value' => 1969, 'max_value' => 1969, 'score' => 2],
            ['min_value' => 1960, 'max_value' => 1970, 'score' => 1],
        ]);

        // Question 5: Text Keywords
        $q5 = Question::create([
            'question_text' => 'What are the three branches of the US government?',
            'question_type' => Question::TYPE_TEXT_KEYWORDS,
            
            'order' => 5,
        ]);
        $quiz->questions()->syncWithoutDetaching([$q5->id => ['order' => 5]]);

        $q5->keywords()->createMany([
            ['keyword' => 'legislative', 'score' => 1],
            ['keyword' => 'executive', 'score' => 1],
            ['keyword' => 'judicial', 'score' => 1],
        ]);

        // Question 6: Image Media Question
        $q6 = Question::create([
            'question_text' => 'What landmark is shown in the image above?',
            'question_type' => Question::TYPE_TEXT_KEYWORDS,
            'media_type' => Question::MEDIA_TYPE_VIDEO,
            'media_url' => 'https://youtu.be/NUMz00m8ySk?si=R49OHgUgsDUL4Lle',
            
            'order' => 6,
        ]);
        $quiz->questions()->syncWithoutDetaching([$q6->id => ['order' => 6]]);
        $q6->keywords()->create(['keyword' => 'Eiffel Tower', 'score' => 5]);

        // Question 7: Image Response Question
        $q7 = Question::create([
            'question_text' => 'Take a photo of your handwritten math solution.',
            'question_type' => Question::TYPE_IMAGE_ANSWER,
            
            'order' => 7,
        ]);
        $quiz->questions()->syncWithoutDetaching([$q7->id => ['order' => 7]]);

        // Question 8: Audio Media Question
        $q8 = Question::create([
            'question_text' => 'Listen to the audio clip. What is the main theme?',
            'question_type' => Question::TYPE_TEXT_KEYWORDS,
            'media_type' => Question::MEDIA_TYPE_AUDIO,
            'media_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
            
            'order' => 8,
        ]);
        $quiz->questions()->syncWithoutDetaching([$q8->id => ['order' => 8]]);
        $q8->keywords()->create(['keyword' => 'Melancholy', 'score' => 3]);

        // Question 9: Visual Evidence
        $q9 = Question::create([
            'question_text' => 'Upload a photo of your project workspace.',
            'question_type' => Question::TYPE_IMAGE_ANSWER,
            
            'order' => 9,
        ]);
        $quiz->questions()->syncWithoutDetaching([$q9->id => ['order' => 9]]);
    }
}
