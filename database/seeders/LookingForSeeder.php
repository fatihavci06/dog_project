<?php

namespace Database\Seeders;

use App\Models\LookingFor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LookingForSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $items = [
            [
                'en' => 'Playmates',
                'tr' => 'Oyun Arkadaşları',
            ],
            [
                'en' => 'Walking Buddies',
                'tr' => 'Yürüyüş Dostları',
            ],
            [
                'en' => 'Training Partners',
                'tr' => 'Eğitim Partnerleri',
            ],
            [
                'en' => 'Best Friends',
                'tr' => 'En İyi Arkadaşlar',
            ],
        ];

        foreach ($items as $data) {
            $item = LookingFor::create();

            $item->setTranslation('name', 'en', $data['en']);
            $item->setTranslation('name', 'tr', $data['tr']);
        }
    }
}
