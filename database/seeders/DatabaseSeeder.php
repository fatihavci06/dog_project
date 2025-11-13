<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // $this->call(RolesAndUsersSeeder::class);
        // $this->call(QuestionSeeder::class);
        // $this->call(OptionSeeder::class);
        $this->call([
            LanguageSeeder::class,
            BreadSeeder::class,
            AgeRangeSeeder::class,
            VibeSeeder::class,
            HealthInfoSeeder::class,
            TravelRadiusSeeder::class,
            AvailabilityForMeetupSeeder::class,
            LookingForSeeder::class, // ⭐ Burayı ekle
        ]);
    }
}
