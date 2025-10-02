<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            'What are you hoping to fetch from PupCrawl? (Rank your Top 5)',
            'What’s your preferred walk & play vibe?',
            'How do you like to plan your meetups?',
            'What kind of connection are you hoping to make?',
            'What would your pup (or future pup) say about you?',
        ];

        foreach ($questions as $index => $title) {
            Question::create([
                'id' => $index + 1,
                'question_text' => $title,
                'is_active' => 1, // opsiyonel
            ]);
        }
    }
}
