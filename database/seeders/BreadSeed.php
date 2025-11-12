<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BreadSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $breeds = [
            'Akita',
            'Alaskan Malamute',
            'American Bulldog',
            'American Eskimo Dog',
            'American Pit Bull Terrier',

        ];

        foreach ($breeds as $breed) {
            DB::table('breads')->insert([
                'name' => $breed,
            ]);
        }
    }
}
