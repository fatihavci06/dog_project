<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Rolleri oluştur
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Sistem yöneticisi']
        );

        $userRole = Role::firstOrCreate(
            ['name' => 'user'],
            ['description' => 'Normal kullanıcı']
        );

        // Admin oluştur
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'), // şifre: password
                'status' => 'active'
            ]
        );
        $admin->roles()->sync([$adminRole->id]);

        // 5 user oluştur
        User::factory(5)->create()->each(function ($user) use ($userRole) {
            $user->roles()->sync([$userRole->id]);
        });
    }
}
