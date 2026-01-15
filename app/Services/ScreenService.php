<?php
namespace App\Services;

use App\Models\Screen;

class ScreenService
{
    public function getAll()
    {
        return Screen::all();
    }

    public function getById($id, $language = 'en')
    {
        $screen = Screen::findOrFail($id);

        // 1. DURUM: Admin Paneli (Controller'dan $language null gelirse)
        // Eğer dil parametresi null ise, düzenleme modundayız demektir.
        // Tüm çevirileri (raw data) dönüyoruz.
        if ($language === null) {
            return $screen;
        }

        // 2. DURUM: API / Mobil Uygulama
        // Veritabanındaki çeviri havuzunu alıyoruz
        $translations = $screen->content['translations'] ?? [];

        // MANTIK:
        // - İstenen dil ($language) var mı? Varsa onu al.
        // - Yoksa 'en' (varsayılan) verisini al.
        // - 'en' de yoksa boş dizi döndür.
        $specificContent = $translations[$language] ?? $translations['en'] ?? [];

        // İstenen sade formatı oluşturuyoruz
        return [
            "id" => $screen->id,
            "screen_slug" => $screen->screen_slug,
            "content" => $specificContent
            // Bu 'content' içinde artık sadece o dile ait:
            // layout_type, hero_image, title, subtitle, cta_text var.
        ];
    }
    public function update($id, array $data)
    {
        $screen = Screen::findOrFail($id);
        $screen->content = $data['content'];
        $screen->save();
        return $screen;
    }
}
