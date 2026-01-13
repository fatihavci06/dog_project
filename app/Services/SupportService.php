<?php
namespace App\Services;

use App\Models\Language;
use App\Models\Support;

class SupportService
{
    /**
     * Belirtilen dile göre destek verisini döner.
     */
    public function getSupportByLanguage(string $langCode): ?array
    {
        // 1. Dilin aktif olup olmadığını kontrol et
        $isActive = Language::where('code', $langCode)->where('is_active', true)->exists();

        if (!$isActive) {
            return null;
        }

        // 2. Veriyi çek
        $data = Support::where('language_code', $langCode)->first();

        if (!$data) {
            return null;
        }

        // 3. Tek bir dilin objesini dön
        return [
            'support' => [
                'title' => $data->title,
                'description' => $data->description,
                'contact' => [
                    'email' => $data->email,
                    'phone' => $data->phone,
                    'address' => $data->address,
                ],
                'website_url' => $data->website_url,
                'social_urls' => [
                    'instagram_url' => $data->instagram_url,
                    'tiktok_url' => $data->tiktok_url,
                    'x_url' => $data->x_url,
                ]
            ]
        ];
    }
}
