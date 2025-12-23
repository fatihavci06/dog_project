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
        // 1. QR Payload
        $payload = [
            'app'    => 'pup_app',
            'type'   => 'profile_share',
            'data'   => [
                'id'   => $pup->id,
                'name' => $pup->name,
            ]
        ];

        // Köpek logosunun yolu (Örnek: public klasöründe 'images/pup-logo.png' olduğunu varsayalım)
        // Eğer görsel bir URL ise veya storage'da ise ona göre path verilmeli.
        $logoPath = public_path('pup-logo.png');

        // 2. QR Görselini Oluştur
        $qrImage = QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->errorCorrection('H') // Logo olduğu için H (High) şart
            // MERGE KISMI BURASI:
            // 1. parametre: Resim yolu
            // 2. parametre: Logonun kaplayacağı alan oranı (.3 = %30)
            // 3. parametre: Absolute path kullanımı (genelde true daha sağlıklı çalışır)
            ->merge($logoPath, 0.3, true)
            ->generate(json_encode($payload));

        // 3. Base64'e çevir
        $base64 = 'data:image/png;base64,' . base64_encode($qrImage);

        return [
            'qr_image' => $base64,
            'payload'  => $payload
        ];
    }
}
