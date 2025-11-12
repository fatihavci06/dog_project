<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VibeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Chill',
            'Playful',
            'Energetic',
            'Independent',
            'Loving',
            'Social',
            'Shy',

        ];

        foreach ($data as $d) {
            DB::table('vibes')->insert([
                'name' => $d,
            ]);
        }
    }
}
