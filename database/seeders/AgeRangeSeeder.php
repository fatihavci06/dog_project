<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgeRangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $breeds = [
            'Wiggler (<1yr)',
            'Teen(1-4)',
            'Wise & Mature (5-8)',
            'Oldies Goldies (9+)',

        ];

        foreach ($breeds as $breed) {
            DB::table('age_ranges')->insert([
                'name' => $breed,
            ]);
        }
    }
}
