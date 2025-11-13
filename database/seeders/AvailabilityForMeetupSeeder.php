<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AvailabilityForMeetup;

class AvailabilityForMeetupSeeder extends Seeder
{
    public function run()
    {
        $items = [
            [
                'en' => 'Weekday Mornings',
                'tr' => 'Hafta İçi Sabah',
            ],
            [
                'en' => 'Weekday Lunch',
                'tr' => 'Hafta İçi Öğle',
            ],
            [
                'en' => 'Weekday Evenings',
                'tr' => 'Hafta İçi Akşam',
            ],
            [
                'en' => 'Weekend Mornings',
                'tr' => 'Hafta Sonu Sabah',
            ],
            [
                'en' => 'Weekend Evenings',
                'tr' => 'Hafta Sonu Akşam',
            ],
            [
                'en' => 'Weekend Lunch',
                'tr' => 'Hafta Sonu Öğle',
            ],
            [
                'en' => 'I have a flexible schedule',
                'tr' => 'Esnek bir programım var',
            ],
        ];

        foreach ($items as $data) {
            $item = AvailabilityForMeetup::create();

            $item->setTranslation('name', 'en', $data['en']);
            $item->setTranslation('name', 'tr', $data['tr']);
        }
    }
}
