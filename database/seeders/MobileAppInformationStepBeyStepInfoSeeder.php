<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MobileAppInformationStepBeyStepInfo;
use App\Models\Language;

class MobileAppInformationStepBeyStepInfoSeeder extends Seeder
{
    public function run()
    {
        $languages = Language::pluck('code')->toArray();
        // ['en', 'tr']

        $steps = [
            [
                'step_number' => 1,
                'translations' => [
                    'title' => [
                        'en' => 'When pups connect, humans do too',
                        'tr' => 'Köpekler bağ kurduğunda insanlar da bağ kurar',
                    ],
                    'description' => [
                        'en' => 'A shared social life for you and your pup',
                        'tr' => 'Sen ve köpeğin için ortak bir sosyal yaşam',
                    ],
                ],
            ],
            [
                'step_number' => 2,
                'translations' => [
                    'title' => [
                        'en' => 'Find your pack',
                        'tr' => 'Sürünü bul',
                    ],
                    'description' => [
                        'en' => 'Meet dog owners and dog lovers for walks, playdates, and more.',
                        'tr' => 'Yürüyüşler, oyun buluşmaları ve daha fazlası için köpek sahipleri ve köpek severlerle tanış.',
                    ],
                ],
            ],
            [
                'step_number' => 3,
                'translations' => [
                    'title' => [
                        'en' => 'Find your pack',
                        'tr' => 'Sürünü bul',
                    ],
                    'description' => [
                        'en' => 'Meet dog owners and dog lovers for walks, playdates, and more.',
                        'tr' => 'Yürüyüşler, oyun buluşmaları ve daha fazlası için köpek sahipleri ve köpek severlerle tanış.',
                    ],
                ],
            ],
        ];

        foreach ($steps as $stepData) {

            // Ana tablo kaydı
            $step = MobileAppInformationStepBeyStepInfo::create([
                'step_number' => $stepData['step_number'],
                'image_path' => null,
            ]);

            // Translations ekleme
            foreach ($stepData['translations'] as $key => $langs) {
                foreach ($langs as $locale => $value) {
                    $step->setTranslation($key, $locale, $value);
                }
            }
        }
    }
}
