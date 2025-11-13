<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vibe;

class VibeSeeder extends Seeder
{
    public function run()
    {
        $items = [
            [
                'en' => 'Chill',
                'tr' => 'Sakin',
            ],
            [
                'en' => 'Playful',
                'tr' => 'Oyuncu',
            ],
            [
                'en' => 'Energetic',
                'tr' => 'Enerjik',
            ],
            [
                'en' => 'Independent',
                'tr' => 'Bağımsız',
            ],
            [
                'en' => 'Loving',
                'tr' => 'Sevecen',
            ],
            [
                'en' => 'Social',
                'tr' => 'Sosyal',
            ],
            [
                'en' => 'Shy',
                'tr' => 'Utangaç',
            ],
        ];

        foreach ($items as $data) {
            $item = Vibe::create();

            $item->setTranslation('name', 'en', $data['en']);
            $item->setTranslation('name', 'tr', $data['tr']);
        }
    }
}

