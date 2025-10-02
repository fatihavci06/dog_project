<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Option;
use App\Models\Question;

class OptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            1 => [ // 1. soru
                '🐾 Social dog walks',
                '👥 Play dates',
                '📅 Regular walking buddies',
                '☕ Café hangs',
                '🧠 Training & tips',
                '🏃 Active walkies / running',
                '🏞️ Adventures & day trips',
                '🤳 Fun content for socials',
                '🧖 Chill / relax days',
            ],
            2 => [ // 2. soru
                '🌙 Chill strolls',
                '🐕 Active walkies',
                '🎉 Social & meet-everyone',
                '🎯 Structured & training-focused',
            ],
            3 => [ // 3. soru
                '⏰ Regular & scheduled',
                '📆 Flexible week-to-week',
                '🎈 Occasional / now and then',
                '🌀 Go with the flow',
            ],
            4 => [ // 4. soru
                '👯 New friends',
                '🌈 Walk & talk buddies',
                '🧑‍🏫 Learning partners',
                '🐾 Lifestyle matches',
            ],
            5 => [ // 5. soru
                '🌿 Laid-back',
                '🎉 Sociable',
                '🎯 Organised',
                '🤹 Easygoing',
            ],
        ];

        foreach ($options as $questionId => $optionList) {
            foreach ($optionList as $rank => $text) {
                Option::create([

                    'question_id' => $questionId,
                    'option_text' => $text,
                    'order_index' => $rank + 1,
                    'is_active' => 1, // opsiyonel
                ]);
            }
        }
    }
}
