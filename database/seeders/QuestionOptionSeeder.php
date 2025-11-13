<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Database\Seeder;

class QuestionOptionSeeder extends Seeder
{
    public function run()
    {
        $languages = Language::pluck('code')->toArray(); // ['en', 'tr']

        $questions = [

            // 1️⃣ WHAT ARE YOU HOPING TO FETCH...
            [
                'translations' => [
                    'question_text' => [
                        'en' => 'What are you hoping to fetch from PupCrawl? (Rank your Top 5)',
                        'tr' => 'PupCrawl’dan ne umuyorsun? (En iyi 5\'ini sırala)',
                    ],
                ],
                'options' => [
                    ['en' => 'Social dog walks',               'tr' => 'Sosyal köpek yürüyüşleri'],
                    ['en' => 'Play dates',                    'tr' => 'Oyun buluşmaları'],
                    ['en' => 'Regular walking buddies',       'tr' => 'Düzenli yürüyüş arkadaşları'],
                    ['en' => 'Café hangs',                    'tr' => 'Kafede takılmalar'],
                    ['en' => 'Training & tips',               'tr' => 'Eğitim ve tüyolar'],
                    ['en' => 'Active walkies / running',      'tr' => 'Aktif yürüyüş / koşu'],
                    ['en' => 'Adventures & day trips',        'tr' => 'Macera ve günlük geziler'],
                    ['en' => 'Fun content for socials',       'tr' => 'Sosyal medya için eğlenceli içerikler'],
                    ['en' => 'Chill / relax days',            'tr' => 'Rahatlama günleri'],
                ]
            ],

            // 2️⃣ WALK & PLAY VIBE
            [
                'translations' => [
                    'question_text' => [
                        'en' => "What's your preferred walk & play vibe?",
                        'tr' => 'Tercih ettiğin yürüyüş ve oyun tarzı nedir?',
                    ],
                ],
                'options' => [
                    ['en' => 'Chill strolls',               'tr' => 'Rahat yürüyüşler'],
                    ['en' => 'Active walkies',              'tr' => 'Aktif yürüyüşler'],
                    ['en' => 'Social & meet-everyone',      'tr' => 'Sosyalleşme ve herkesle tanışma'],
                    ['en' => 'Structured & training-focused','tr' => 'Planlı ve eğitim odaklı'],
                ]
            ],

            // 3️⃣ MEETUP PLANNING
            [
                'translations' => [
                    'question_text' => [
                        'en' => 'How do you like to plan your meetups?',
                        'tr' => 'Buluşmalarını nasıl planlamayı tercih edersin?',
                    ],
                ],
                'options' => [
                    ['en' => 'Regular & scheduled',       'tr' => 'Düzenli ve planlı'],
                    ['en' => 'Flexible week-to-week',     'tr' => 'Haftalık esnek'],
                    ['en' => 'Occasional / now and then', 'tr' => 'Ara sıra'],
                    ['en' => 'Go with the flow',          'tr' => 'Akışına bırakırım'],
                ]
            ],

            // 4️⃣ CONNECTION TYPE
            [
                'translations' => [
                    'question_text' => [
                        'en' => 'What kind of connection are you hoping to make?',
                        'tr' => 'Ne tür bir bağlantı kurmayı umuyorsun?',
                    ],
                ],
                'options' => [
                    ['en' => 'New friends',             'tr' => 'Yeni arkadaşlar'],
                    ['en' => 'Walk & talk buddies',     'tr' => 'Yürüyüş ve sohbet arkadaşları'],
                    ['en' => 'Learning partners',       'tr' => 'Öğrenme partnerleri'],
                    ['en' => 'Lifestyle matches',       'tr' => 'Yaşam tarzı uyumları'],
                ]
            ],

            // 5️⃣ WHAT WOULD YOUR PUP SAY ABOUT YOU?
            [
                'translations' => [
                    'question_text' => [
                        'en' => 'What would your pup (or future pup) say about you?',
                        'tr' => 'Köpeğin (veya gelecekteki köpeğin) senin hakkında ne söylerdi?',
                    ],
                ],
                'options' => [
                    ['en' => 'Laid-back',              'tr' => 'Sakin'],
                    ['en' => 'Sociable',               'tr' => 'Sosyal'],
                    ['en' => 'Organised',              'tr' => 'Organize'],
                    ['en' => 'Easygoing',              'tr' => 'Uyumlu'],
                ]
            ],
        ];


        // 📌 Tüm soruları ve seçenekleri oluştur
        foreach ($questions as $qIndex => $qData) {

            // Soru kaydı (question_text kolon yok → translation kullanılacak)
            $question = Question::create([
                'is_active'    => true,
                'order_index'  => $qIndex + 1,
            ]);

            // Soru çevirileri
            foreach ($qData['translations']['question_text'] as $locale => $value) {
                $question->setTranslation('question_text', $locale, $value);
            }

            // Seçenekleri ekle
            foreach ($qData['options'] as $oIndex => $opt) {

                $option = Option::create([
                    'question_id' => $question->id,
                    'order_index' => $oIndex + 1,
                    'is_active'   => true,
                ]);

                // Seçenek çevirileri
                foreach ($languages as $lang) {
                    $option->setTranslation('option_text', $lang, $opt[$lang] ?? $opt['en']);
                }
            }
        }
    }
}
