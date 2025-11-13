<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TravelRadius;

class TravelRadiusSeeder extends Seeder
{
    public function run()
    {
        $items = [
            [
                'en' => '1 km',
                'tr' => '1 km',
            ],
            [
                'en' => '3 km',
                'tr' => '3 km',
            ],
            [
                'en' => '5 km',
                'tr' => '5 km',
            ],
            [
                'en' => '10 km',
                'tr' => '10 km',
            ],
        ];

        foreach ($items as $data) {
            $item = TravelRadius::create();

            $item->setTranslation('name', 'en', $data['en']);
            $item->setTranslation('name', 'tr', $data['tr']);
        }
    }
}
