<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HealthInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Spayed/Neutered',
            'Microchipped',
            'Regular Vet Checkups',


        ];

        foreach ($data as $d) {
            DB::table('health_infos')->insert([
                'name' => $d,
            ]);
        }
    }
}
