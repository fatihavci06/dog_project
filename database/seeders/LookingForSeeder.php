<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LookingForSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $breeds = [
            'Playmates',
            'Walking Buddies',
            'Training Partners',
            'Best Friends',

        ];

        foreach ($breeds as $breed) {
            DB::table('looking_fors')->insert([
                'name' => $breed,
            ]);
        }
    }
}
