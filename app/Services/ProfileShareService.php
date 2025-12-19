<?php

namespace App\Services;

use App\Models\PupProfile;
use App\Models\User;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ProfileShareService
{
    /**
     * Verilen kullanıcı için Profil Paylaşım QR kodunu Base64 formatında üretir.
     */
    public function generateProfileQr(PupProfile $pup): array
    {
        // 1. QR Payload: Mobil uygulamanın okuyacağı veri yapısı
        $payload = [
            'app'    => 'pup_app',         // Uygulama imzası (Güvenlik/Kontrol için)
            'type'   => 'profile_share',   // QR'ın türü (Buluşma mı? Profil mi? Ödeme mi?)
            'data'   => [
                'id'       => $pup->id,
                'name' => $pup->name, // İstersen ekranda hemen göstermek için adını da ekleyebilirsin
            ]
        ];

        // 2. QR Görselini Oluştur (PNG formatında)
        // size(300): 300px genişlik
        // margin(2): Kenar boşluğu
        $qrImage = QrCode::format('png')
                         ->size(300)
                         ->margin(2)
                         ->errorCorrection('H') // Yüksek hata düzeltme (Logo koyacaksan gerekli)
                         ->generate(json_encode($payload));

        // 3. Base64'e çevir
        $base64 = 'data:image/png;base64,' . base64_encode($qrImage);

        return [
            'qr_image' => $base64,
            'payload'  => $payload // Test ederken ne veri gittiğini görmen için
        ];
    }
}
