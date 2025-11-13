<?php

namespace Database\Seeders;

use App\Models\AgeRange;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgeRangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run()
    {
        $ranges = [
            [
                'en' => 'Wiggler (<1 yr)',
                'tr' => 'Minik (<1 yaş)',
            ],
            [
                'en' => 'Teen (1–4)',
                'tr' => 'Genç (1–4 yaş)',
            ],
            [
                'en' => 'Wise & Mature (5–8)',
                'tr' => 'Olgun & Bilge (5–8 yaş)',
            ],
            [
                'en' => 'Oldies Goldies (9+)',
                'tr' => 'Yaşlı Dostlar (9+ yaş)',
            ],
        ];

        foreach ($ranges as $data) {
            $range = AgeRange::create();

            $range->setTranslation('name', 'en', $data['en']);
            $range->setTranslation('name', 'tr', $data['tr']);
        }
    }
}
