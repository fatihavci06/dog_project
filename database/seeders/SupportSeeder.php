<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;
use App\Models\Support;

class SupportSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Dilleri Tanımlayalım
        $languages = [
            ['code' => 'tr', 'name' => 'Turkish', 'is_active' => true],
            ['code' => 'en', 'name' => 'English', 'is_active' => true],
            ['code' => 'de', 'name' => 'German', 'is_active' => false], // Test için pasif dil
        ];

        foreach ($languages as $lang) {
            Language::updateOrCreate(['code' => $lang['code']], $lang);
        }

        // 2. Türkçe Destek Verisi
        Support::updateOrCreate(
            ['language_code' => 'tr'],
            [
                'title' => 'Yardım & Destek',
                'description' => 'Sorularınız, önerileriniz veya bir sorun yaşarsanız buradan bize ulaşabilirsiniz.',
                'email' => 'destek@pupcrawl.app',
                'phone' => '+90 212 000 00 00',
                'address' => 'İstanbul, Türkiye',
                'website_url' => 'https://pupcrawl.app',
                'instagram_url' => 'https://instagram.com/pupcrawl',
                'tiktok_url' => 'https://tiktok.com/@pupcrawl',
                'x_url' => 'https://x.com/pupcrawl',
            ]
        );

        // 3. İngilizce Destek Verisi
        Support::updateOrCreate(
            ['language_code' => 'en'],
            [
                'title' => 'Help & Support',
                'description' => 'If you have any questions, suggestions, or encounter any issues, you can reach us here.',
                'email' => 'support@pupcrawl.app',
                'phone' => null,
                'address' => null,
                'website_url' => 'https://pupcrawl.app/en',
                'instagram_url' => 'https://instagram.com/pupcrawl',
                'tiktok_url' => 'https://tiktok.com/@pupcrawl',
                'x_url' => 'https://x.com/pupcrawl',
            ]
        );
    }
}
