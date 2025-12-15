<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\Friendship;
use App\Models\PupProfile;
use App\Models\PupProfileAnswer;
use Exception;

class PupMatchmakingService
{
    public function getMatchDetail(
        int $pupProfileId,
        int $authUserId
    ): array {

        // 🔐 Profil var mı
        $profile = PupProfile::with([
            'images',
            'vibe',
            'breed',
            'ageRange',
            'travelRadius',

        ])->find($pupProfileId);

        if (!$profile) {
            throw new \Exception('Profile not found', 404);
        }

        /* ============================
       FRIEND (MATCH) KONTROLÜ
    ============================ */
        $isMatch = Friendship::where('status', 'accepted')
            ->where(function ($q) use ($authUserId, $profile) {
                $q->where('sender_id', $authUserId)
                    ->where('receiver_id', $profile->user_id);
            })
            ->orWhere(function ($q) use ($authUserId, $profile) {
                $q->where('sender_id', $profile->user_id)
                    ->where('receiver_id', $authUserId);
            })
            ->exists();

        /* ============================
       FAVORİ KONTROLÜ
    ============================ */
        $isFavorite = Favorite::where('user_id', $authUserId)
            ->where('favorite_id', $profile->id)
            ->exists();

        return [
            'pup_profile_id' => $profile->id,
            'name'           => $profile->name,
            'biography'      => $profile->biography,
            'sex'            => $profile->sex,

            'breed'         => $profile->breed->translate('name'),
            'age'           => $profile->ageRange->translate('name'),
            'travel_radius' => $profile->travelRadius->translate('name'),

            'images' => $profile->images->map(fn($img) => [
                'id'   => $img->id,
                'path' => $img->path,
            ]),

            'vibe' => $profile->vibe->map(fn($v) => [
                'id'   => $v->id,
                'name' => $v->translate('name'),
            ]),

            // 🔥 FLAGS
            'is_favorite' => $isFavorite,
            'is_match'    => $isMatch,
        ];
    }

    /**
     * PupProfile'ın tüm cevaplarını getirir.
     * Format:
     * [
     *   1 => [4,1,7,2,6],   // question_id => ordered option ids
     *   2 => [12,11,10,13],
     *   3 => [...],
     *   4 => [...],
     *   5 => [...]
     * ]
     */
    public function getPupAnswers(int $pupProfileId): array
    {
        $answers = PupProfileAnswer::where('pup_profile_id', $pupProfileId)
            ->orderBy('question_id')
            ->orderBy('order_index')
            ->get()
            ->groupBy('question_id');

        $formatted = [];

        foreach ($answers as $qId => $rows) {
            $formatted[$qId] = $rows->pluck('option_id')->toArray();
        }

        return $formatted;
    }


    /**
     * Match tipini hesaplar – güncel kurallar:
     *
     * 💘 Perfect Match
     *   - 5 sorunun tamamında ilk 2 seçenek eşleşmeli (sırası önemsiz)
     *
     * 💪 Strong Match
     *   - İlk soru ilk 2 seçenek eşleşmeli (FIX)
     *   - Toplam 5 sorudan en az 3 tanesinde ilk 2 eşleşmeli
     *
     * 🙂 Good Match
     *   - İlk soru ilk 3 seçenek eşleşmeli (FIX)
     *   - Toplam 5 sorudan en az 3 tanesinde ilk 3 eşleşmeli
     *
     * 🤔 No Match
     *   - Diğer tüm durumlar
     */
    public function getMatchType(array $a, array $b): string
    {
        $perfect = true;
        $strongCount = 0;
        $goodCount   = 0;

        foreach ($a as $qId => $aAns) {

            if (!isset($b[$qId])) {
                $perfect = false;
                continue;
            }

            $bAns = $b[$qId];

            $a2 = collect($aAns)->take(2)->sort()->values();
            $b2 = collect($bAns)->take(2)->sort()->values();

            $a3 = collect($aAns)->take(3)->sort()->values();
            $b3 = collect($bAns)->take(3)->sort()->values();

            // perfect → İlk 2 seçenek tüm sorularda eşleşmeli
            if ($a2->toJson() !== $b2->toJson()) {
                $perfect = false;
            }

            // Strong → ilk 2 eşleşmesi
            if ($a2->toJson() === $b2->toJson()) {
                $strongCount++;
            }

            // Good → ilk 3 eşleşmesi
            if ($a3->toJson() === $b3->toJson()) {
                $goodCount++;
            }
        }

        /**
         * 💘 Perfect Match
         */
        if ($perfect) {
            return 'Perfect';
        }


        /**
         * 💪 Strong Match
         *
         * 1. Soru FIX → İlk 2 seçenek eşleşmeli
         * + StrongCount >= 3
         */
        $first2_Q1_A = collect($a[1])->take(2)->sort()->values()->toJson();
        $first2_Q1_B = collect($b[1] ?? [])->take(2)->sort()->values()->toJson();

        $q1StrongFix = ($first2_Q1_A === $first2_Q1_B);

        if ($q1StrongFix && $strongCount >= 3) {
            return 'Strong';
        }


        /**
         * 🙂 Good Match
         *
         * 1. Soru FIX → İlk 3 seçenek eşleşmeli
         * + GoodCount >= 3
         */
        $first3_Q1_A = collect($a[1])->take(3)->sort()->values()->toJson();
        $first3_Q1_B = collect($b[1] ?? [])->take(3)->sort()->values()->toJson();

        $q1GoodFix = ($first3_Q1_A === $first3_Q1_B);

        if ($q1GoodFix && $goodCount >= 3) {
            return 'Good';
        }


        /**
         * 🤔 No Match
         */
        return 'No Match';
    }


    /**
     * Match tipini puana dönüştürür (sıralama için)
     */
    public function matchScore(string $matchType): int
    {
        return match ($matchType) {
            'Perfect' => 4,
            'Strong'  => 3,
            'Good'    => 2,
            default   => 1,
        };
    }
    /**
     * İki koordinat arasındaki mesafeyi hesaplar (KM cinsinden).
     */
    /**
     * İki koordinat arasındaki mesafeyi hesaplar (KM cinsinden).
     * Koordinatlar eksikse null döner.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2): ?float
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


    /**
     * Tüm PupProfile’lar ile eşleşme listesi döner.
     * Kendi user'a ait PupProfile'lar HARİÇ!
     */
    public function getMatchesPaginated(
        int $pupProfileId,
        int $authUserId,
        int $page = 1,
        int $perPage = 10
    ): array {

        // 1) Profil verisini çek (Sadece varlık kontrolü değil, lat/long verisi için objeyi alıyoruz)
        $currentProfile = PupProfile::where('id', $pupProfileId)
            ->where('user_id', $authUserId)
            ->first();

        if (!$currentProfile) {
            throw new Exception('Not found', 404);
        }

        // 2) Kullanıcının arkadaş ID’lerini çek (accepted)
        $friendIds = Friendship::where(function ($q) use ($authUserId) {
            $q->where('sender_id', $authUserId)
                ->where('status', 'accepted');
        })
            ->orWhere(function ($q) use ($authUserId) {
                $q->where('receiver_id', $authUserId)
                    ->where('status', 'accepted');
            })
            ->get()
            ->map(fn($f) => $f->sender_id == $authUserId ? $f->receiver_id : $f->sender_id)
            ->toArray();

        // Arkadaşların pup profile ID’leri
        $friendProfileIds = PupProfile::whereIn('user_id', $friendIds)
            ->pluck('id')
            ->toArray();

        // Kullanıcının FAVORİ pup profile ID’leri
        $favoriteProfileIds = Favorite::where('user_id', $authUserId)
            ->pluck('favorite_id')
            ->toArray();

        // 3) Ana profilin cevapları
        $mainAnswers = $this->getPupAnswers($pupProfileId);

        // 4) Diğer profiller
        // NOT: Eğer veritabanınızda on binlerce kayıt varsa, lat/long filtrelemesini
        // burada SQL içinde (scopeDistance gibi) yapmanız performans için daha iyi olur.
        // Şimdilik mevcut yapınızı bozmadan PHP tarafında hesaplıyoruz.
        $otherProfiles = PupProfile::with(['images', 'vibe', 'breed', 'ageRange', 'travelRadius'])
            ->where('id', '!=', $pupProfileId)
            ->where('name', '!=', null)
            ->where('user_id', '!=', $authUserId)
            ->whereNotIn('id', $friendProfileIds)
            ->get();

        $result = [];

        // 5) Eşleşmeleri ve Mesafeyi hesapla
        foreach ($otherProfiles as $profile) {

            $otherAnswers = $this->getPupAnswers($profile->id);
            $matchType = $this->getMatchType($mainAnswers, $otherAnswers);
            $score     = $this->matchScore($matchType);

            // 🔥 MESAFE HESAPLAMA ÇAĞRISI
            // Veritabanında sütun adlarınızın 'lat' ve 'long' (veya 'lng') olduğundan emin olun.
            $distanceKm = $this->calculateDistance(
    $currentProfile->lat,
    $currentProfile->long,
    $profile->lat,
    $profile->long
);

            $result[] = [
                'pup_profile_id' => $profile->id,
                'name'           => $profile->name,
                'photo'          => $profile->images[0]->path ?? null,
                'user' => [
                    'id'   => $profile->user->id,
                    'name' => $profile->user->name,
                ],
                'biography'      => $profile->biography,

                'vibe' => $profile->vibe->map(fn($v) => [
                    'id'   => $v->id,
                    'name' => $v->translate('name'),
                ]),

                'sex'           => $profile->sex,
                'breed'         => $profile->breed->translate('name'),
                'age'           => $profile->ageRange->translate('name'),
                'travel_radius' => $profile->travelRadius->translate('name'),

                'is_favorite'   => in_array($profile->id, $favoriteProfileIds),
                'is_match'      => in_array($profile->id, $friendProfileIds),

                'match_type'    => $matchType,
                'match_score'   => $score,

                // 🔥 YENİ EKLENEN MESAFE ALANI
                'distance_km'   => $distanceKm,
            ];
        }

        // 6) Score’a göre sırala (İsterseniz mesafeye göre de ikincil sıralama yapabilirsiniz)
        $sorted = collect($result)->sortByDesc('match_score')->values();

        // 7) Pagination
        $total    = $sorted->count();
        $lastPage = (int) ceil($total / $perPage);
        $offset   = ($page - 1) * $perPage;

        $paged = $sorted->slice($offset, $perPage)->values()->toArray();

        return [
            'current_page' => $page,
            'per_page'     => $perPage,
            'total'        => $total,
            'last_page'    => $lastPage,
            'data'         => $paged,
        ];
    }
}
