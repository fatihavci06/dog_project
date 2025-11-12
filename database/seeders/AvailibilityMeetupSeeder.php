<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AvailibilityMeetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Weekday Mornings',
            'Weekday Lunch',
            'Weekday Evenings',
            'Weekend Evenings',
            'Weekend Mornings',
            'Weekend Evenings',
            'Weekend Lunch',
            'I have a flexible schedule'


        ];

        foreach ($data as $d) {
            DB::table('availability_for_meetups')->insert([
                'name' => $d,
            ]);
        }
    }
}
