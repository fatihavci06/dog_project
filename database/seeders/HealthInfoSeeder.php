<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HealthInfo;

class HealthInfoSeeder extends Seeder
{
    public function run()
    {
        $items = [
            [
                'en' => 'Spayed/Neutered',
                'tr' => 'Kısırlaştırılmış',
            ],
            [
                'en' => 'Microchipped',
                'tr' => 'Mikroçipli',
            ],
            [
                'en' => 'Regular Vet Checkups',
                'tr' => 'Düzenli Veteriner Kontrolleri',
            ],
        ];

        foreach ($items as $data) {
            $item = HealthInfo::create();

            $item->setTranslation('name', 'en', $data['en']);
            $item->setTranslation('name', 'tr', $data['tr']);
        }
    }
}
