<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TravelRadiusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            '1 km',
            '3 km',
            '5 km',
            '10 km'


        ];

        foreach ($data as $d) {
            DB::table('travel_radius')->insert([
                'name' => $d,
            ]);
        }
    }
}
