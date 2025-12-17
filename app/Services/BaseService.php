<?php

namespace App\Services;



class BaseService
{
    public function calculateDistance($lat1, $lon1, $lat2, $lon2): ?float
    {
        // 1) Herhangi bir değer NULL veya boş string ise hesaplama yapma, null dön.
        // Not: '===' yerine 'empty' kullanmıyoruz çünkü 0.0 koordinatı geçerli bir yerdir.
        if (is_null($lat1) || is_null($lon1) || is_null($lat2) || is_null($lon2)) {
            return null;
        }

        // Değerlerin sayısal olduğundan emin olalım (String '41.00' gelebilir)
        $lat1 = (float) $lat1;
        $lon1 = (float) $lon1;
        $lat2 = (float) $lat2;
        $lon2 = (float) $lon2;

        $earthRadius = 6371; // Dünya yarıçapı (km)

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return round($distance, 1);
    }
}
