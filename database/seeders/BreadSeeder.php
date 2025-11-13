<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bread;

class BreadSeeder extends Seeder
{
    public function run()
    {
        $breeds = [
            [
                'en' => 'Akita',
                'tr' => 'Akita',
            ],
            [
                'en' => 'Alaskan Malamute',
                'tr' => 'Alaska Kurdu',
            ],
            [
                'en' => 'American Bulldog',
                'tr' => 'Amerikan Bulldog',
            ],
            [
                'en' => 'American Eskimo Dog',
                'tr' => 'Amerikan Eskimo Köpeği',
            ],
            [
                'en' => 'American Pit Bull Terrier',
                'tr' => 'Amerikan Pitbull Terrier',
            ],
        ];

        foreach ($breeds as $data) {
            $breed = Bread::create();

            $breed->setTranslation('name', 'en', $data['en']);
            $breed->setTranslation('name', 'tr', $data['tr']);
        }
    }
}
