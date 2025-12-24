<?php

namespace App\Services;

use App\Helper\MatchClass;
use App\Models\Conversation;
use App\Models\Date;
use App\Models\Favorite;
use App\Models\Friendship;
use App\Models\PupProfile;
use App\Models\PupProfileAnswer;
use Exception;

class PupMatchmakingService extends BaseService
{
    public function getMatchDetail(
        int $pupProfileId,
        int $authUserId
    ): array {

        // 1) Hedef Profili ve Sahibini (User) Çek
        $profile = PupProfile::with([
            'user', // 🔥 User bilgisi için eklendi
            'images',
            'vibe',
            'breed',
            'ageRange',
            'travelRadius',
        ])->find($pupProfileId);

        if (!$profile) {
            throw new \Exception('Profile not found', 404);
        }

        // 2) Giriş Yapan Kullanıcının (Auth User) Profilini Çek (Koordinatlar için)
        // Not: Eğer kullanıcının birden fazla köpeği varsa, aktif olanı seçmek için
        // logic gerekebilir. Şimdilik kullanıcının ilk/tek profilini alıyoruz.
        $authProfile = PupProfile::where('user_id', $authUserId)->first();

        // 3) Mesafe Hesaplama
        // authProfile yoksa (henüz profil oluşturmamışsa) mesafe null döner.
        $distanceKm = null;
        if ($authProfile) {
            $distanceKm = $this->calculateDistance(
                $authProfile->lat,
                $authProfile->long,
                $profile->lat, // Hedef profilin lat
                $profile->long // Hedef profilin long
            );
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

            // 🔥 USER BİLGİSİ
            'user' => [
                'id'   => $profile->user->id,
                'name' => $profile->user->name,
                // İsterseniz avatar vb. ekleyebilirsiniz
            ],

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
                'icon_path' => $v->icon_path,
            ]),
            'looking_for' => $profile->lookingFor->map(fn($v) => [
                'id'   => $v->id,
                'name' => $v->translate('name'),

            ]),
            'availability_for_meetup' => $profile->availabilityForMeetup->map(fn($v) => [
                'id'   => $v->id,
                'name' => $v->translate('name'),

            ]),

            // 🔥 FLAGS & MESAFE
            'city'        => $profile->city,
            'district'    => $profile->district,
            'is_favorite' => $isFavorite,
            'is_match'    => $isMatch,
            'distance_km' => $distanceKm, // Null veya float döner (örn: 12.5)
            'match_type' => MatchClass::getMatchType(
                $this->getPupAnswers($authProfile->id ?? 0),
                $this->getPupAnswers($profile->id)
            ),
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
            '❤️ Pawfect' => 4,
            '💪 Strong'  => 3,
            '🙂 Good'    => 2,
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

    $currentProfile = PupProfile::where('id', $pupProfileId)
        ->where('user_id', $authUserId)
        ->first();

    if (!$currentProfile) {
        throw new Exception('Not found', 404);
    }

    // 1️⃣ Arkadaş user_id’leri
    $friendUserIds = Friendship::where('status', 'accepted')
        ->where(function ($q) use ($authUserId) {
            $q->where('sender_id', $authUserId)
              ->orWhere('receiver_id', $authUserId);
        })
        ->get()
        ->map(fn ($f) =>
            $f->sender_id == $authUserId ? $f->receiver_id : $f->sender_id
        )
        ->toArray();

    // Arkadaş pup_profile_id’leri
    $friendProfileIds = PupProfile::whereIn('user_id', $friendUserIds)
        ->pluck('id')
        ->toArray();

    // Favoriler
    $favoriteProfileIds = Favorite::where('user_id', $authUserId)
        ->pluck('favorite_id')
        ->toArray();

    // Ana cevaplar
    $mainAnswers = $this->getPupAnswers($pupProfileId);

    // Kullanıcının kendi profilleri
    $myProfileIds = PupProfile::where('user_id', $authUserId)->pluck('id')->toArray();

    // 2️⃣ Diğer profiller
    $otherProfiles = PupProfile::with([
            'images',
            'vibe',
            'breed',
            'ageRange',
            'travelRadius',
            'user'
        ])
        ->whereNotIn('id', $myProfileIds)
        ->whereNotIn('id', $friendProfileIds)
        ->whereNotNull('name')
        ->get();

    $result = [];

    foreach ($otherProfiles as $profile) {

        $otherAnswers = $this->getPupAnswers($profile->id);
        $matchType    = MatchClass::getMatchType($mainAnswers, $otherAnswers);
        $score        = $this->matchScore($matchType);

        $distanceKm = $this->calculateDistance(
            $currentProfile->lat,
            $currentProfile->long,
            $profile->lat,
            $profile->long
        );

        // 🔥 conversation_id
        $conversationId = Conversation::where(function ($q) use ($authUserId, $profile) {
                $q->where('user_one_id', $authUserId)
                  ->where('user_two_id', $profile->user_id);
            })
            ->orWhere(function ($q) use ($authUserId, $profile) {
                $q->where('user_one_id', $profile->user_id)
                  ->where('user_two_id', $authUserId);
            })
            ->value('id');

        // 🔥 date_id (pending / accepted varsa)
        $date = Date::whereIn('status', ['pending', 'accepted'])
            ->where(function ($q) use ($authUserId, $profile) {
                $q->where('sender_id', $authUserId)
                  ->where('receiver_id', $profile->user_id);
            })
            ->orWhere(function ($q) use ($authUserId, $profile) {
                $q->where('sender_id', $profile->user_id)
                  ->where('receiver_id', $authUserId);
            })
            ->orderByDesc('created_at')->first();

        $result[] = [
            'pup_profile_id' => $profile->id,
            'name'           => $profile->name,
            'photo'          => $profile->images[0]->path ?? null,

            'user' => [
                'id'   => $profile->user->id,
                'name' => $profile->user->name,
            ],

            'biography' => $profile->biography,

            'vibe' => $profile->vibe->map(fn ($v) => [
                'id'   => $v->id,
                'name' => $v->translate('name'),
            ]),

            'sex'           => $profile->sex,
            'breed'         => $profile->breed->translate('name'),
            'age'           => $profile->ageRange->translate('name'),
            'travel_radius' => $profile->travelRadius->translate('name'),

            'is_favorite' => in_array($profile->id, $favoriteProfileIds),
            'is_match'    => in_array($profile->id, $friendProfileIds),

            'match_type'  => $matchType,
            'match_score' => $score,
            'distance_km' => $distanceKm,

            // ✅ YENİ EKLENENLER
            'conversation_id' => $conversationId,
            'date'         => $date,
        ];
    }

    // 3️⃣ Skora göre sırala
    $sorted = collect($result)->sortByDesc('match_score')->values();

    // 4️⃣ Pagination
    $total    = $sorted->count();
    $lastPage = (int) ceil($total / $perPage);
    $offset   = ($page - 1) * $perPage;

    return [
        'current_page' => $page,
        'per_page'     => $perPage,
        'total'        => $total,
        'last_page'    => $lastPage,
        'data'         => $sorted->slice($offset, $perPage)->values()->toArray(),
    ];
}

}
