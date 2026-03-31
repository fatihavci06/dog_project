<?php

namespace App\Services;

use App\Helper\MatchClass;
use App\Models\Conversation;
use App\Models\Date;
use App\Models\DiscoverBlackList;
use App\Models\Favorite;
use App\Models\Friendship;
use App\Models\PupProfile;
use App\Models\PupProfileAnswer;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PupMatchmakingService extends BaseService
{
    public function getMatchDetail(
        int $pupProfileId,
        int $authUserId
    ): array {

        /*
    |--------------------------------------------------------------------------
    | 1️⃣ Hedef Pup Profile
    |--------------------------------------------------------------------------
    */
        $profile = PupProfile::with([
            'user',
            'images',
            'vibe',
            'breed',
            'ageRange',
            'travelRadius',
            'lookingFor',
            'friendsOfMine', // Eklendi
            'friendOf',      // Eklendi
            'availabilityForMeetup',

        ])->find($pupProfileId);

        if (!$profile) {
            throw new Exception('Profile not found', 404);
        }

        /*
    |--------------------------------------------------------------------------
    | 2️⃣ Auth kullanıcının pup profile’ı
    |--------------------------------------------------------------------------
    */
        $authProfile = PupProfile::where('user_id', $authUserId)->first();
        $pupProfileIds = PupProfile::where('user_id', $authUserId)
            ->pluck('id')
            ->toArray();

        /*
    |--------------------------------------------------------------------------
    | 3️⃣ Mesafe
    |--------------------------------------------------------------------------
    */
        $distanceKm = null;
        if ($authProfile) {
            $distanceKm = $this->calculateDistance(
                $authProfile->lat,
                $authProfile->long,
                $profile->lat,
                $profile->long
            );
        }


        if ($authProfile) {
            // İki profil arasındaki tek kaydı bul (kimin gönderdiğine bakmaksızın)
            $friendship = Friendship::where(function ($q) use ($authProfile, $profile) {
                $q->where('sender_id', $authProfile->id)->where('receiver_id', $profile->id);
            })
                ->orWhere(function ($q) use ($authProfile, $profile) {
                    $q->where('sender_id', $profile->id)->where('receiver_id', $authProfile->id);
                })
                ->first();

            // Varsa status (accepted, pending vb.), yoksa null dönecek
            $friendshipStatus = $friendship ? $friendship->status : null;
        }

        /*
    |--------------------------------------------------------------------------
    | 4️⃣ MATCH (Friendship) – pup_profile_id bazlı
    |--------------------------------------------------------------------------
    */
        $isMatch = false;

        if ($authProfile) {
            $isMatch = Friendship::where('status', 'accepted')
                ->where(function ($q) use ($authProfile, $profile) {
                    $q->where(function ($sub) use ($authProfile, $profile) {
                        $sub->where('sender_id', $authProfile->id)
                            ->where('receiver_id', $profile->id);
                    })
                        ->orWhere(function ($sub) use ($authProfile, $profile) {
                            $sub->where('sender_id', $profile->id)
                                ->where('receiver_id', $authProfile->id);
                        });
                })
                ->exists();
        }

        /*
    |--------------------------------------------------------------------------
    | 5️⃣ FAVORİ
    |--------------------------------------------------------------------------
    */
        $isFavorite = Favorite::where('user_id', $authUserId)
            ->where('favorite_id', $profile->id)
            ->exists();

        /*
    |--------------------------------------------------------------------------
    | 5.5️⃣ BLACKLIST
    |--------------------------------------------------------------------------
    */
        $isBlacklisted = DiscoverBlackList::where('user_id', $authUserId)
            ->where('pup_profile_id', $profile->id)
            ->exists();

        if ($isBlacklisted) {
            $isFavorite = false;
        }

        /*
    |--------------------------------------------------------------------------
    | 6️⃣ CONVERSATION (user_id bazlı – DOĞRU)
    |--------------------------------------------------------------------------
    */
        $conversationId = Conversation::query()
            ->where(function ($q) use ($authUserId, $profile) {
                $q->where('user_one_id', $authUserId)
                    ->where('user_two_id', $profile->user->id);
            })
            ->orWhere(function ($q) use ($authUserId, $profile) {
                $q->where('user_one_id', $profile->user->id)
                    ->where('user_two_id', $authUserId);
            })
            ->value('id');

        /*
    |--------------------------------------------------------------------------
    | 7️⃣ DATE (pending / accepted) – pup_profile_id bazlı
    |--------------------------------------------------------------------------
    */
        $date = null;


        if (!empty($pupProfileIds)) {

            $date = Date::where('status', 'accepted')
                ->where(function ($q) use ($profile, $pupProfileIds) {
                    $q->where(function ($q2) use ($profile, $pupProfileIds) {
                        $q2->whereIn('sender_id', $pupProfileIds)
                            ->where('receiver_id', $profile->id);
                    })
                        ->orWhere(function ($q2) use ($profile, $pupProfileIds) {
                            $q2->where('sender_id', $profile->id)
                                ->whereIn('receiver_id', $pupProfileIds);
                        });
                })
                ->whereIn('created_at', function ($sub) {
                    $sub->select(DB::raw('MAX(created_at)'))
                        ->from('dates')
                        ->where('status', 'accepted')
                        ->groupBy(
                            DB::raw('LEAST(sender_id, receiver_id)'),
                            DB::raw('GREATEST(sender_id, receiver_id)')
                        );
                })
                ->get();
        }



        /*
    |--------------------------------------------------------------------------
    | 8️⃣ RESPONSE
    |--------------------------------------------------------------------------
    */

        return [
            'pup_profile_id' => $profile->id,
            'friendship' => $friendshipStatus,
            'name' => $profile->name,
            'biography' => $profile->biography,
            'sex' => __('app.' . $profile->sex),

            'user' => [
                'id' => $profile->user->id,
                'name' => $profile->user->name,
                'role_id' => $profile->user->role_id
            ],

            'breed' => $profile->breed?->translate('name'),
            'age' => $profile->ageRange?->translate('name'),
            'travel_radius' => $profile->travelRadius?->translate('name'),

            'images' => $profile->images->map(fn($img) => [
                'id' => $img->id,
                'path' => $img->path,
            ]),

            'vibe' => $profile->vibe->map(fn($v) => [
                'id' => $v->id,
                'name' => $v->translate('name'),
                'icon_path' => $v->icon_path,
            ]),

            'looking_for' => $profile->lookingFor->map(fn($v) => [
                'id' => $v->id,
                'name' => $v->translate('name'),
            ]),

            'availability_for_meetup' => $profile->availabilityForMeetup->map(fn($v) => [
                'id' => $v->id,
                'name' => $v->translate('name'),
            ]),

            'city' => $profile->city,
            'district' => $profile->district,
            'is_favorite' => $isFavorite,
            'is_blacklisted' => $isBlacklisted,
            'is_match' => $isMatch,
            'distance_km' => $distanceKm,

            'match_type' => MatchClass::getMatchType(
                $this->getPupAnswers($authProfile->id ?? 0),
                $this->getPupAnswers($profile->id)
            ),

            // 🔥 YENİ ALANLAR
            'conversation_id' => $conversationId,
            'date' => $date,
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
        $goodCount = 0;

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
            '💪 Strong' => 3,
            '🙂 Good' => 2,
            default => 1,
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
        $currentProfile = PupProfile::with('travelRadius.translations')->where('id', $pupProfileId)
            ->where('user_id', $authUserId)
            ->first();

        // Önce profil var mı kontrol et (Sıralamayı yukarı çekmelisin)
        if (!$currentProfile) {
            throw new Exception('Not found', 404);
        }

        // Opsiyonel zincirleme (Optional Chaining) veya null kontrolü kullan
        $travelRadiusKm = 0; // Varsayılan değer
        if ($currentProfile->travelRadius && isset($currentProfile->travelRadius->translations[0])) {
            $travelRadiusKm = (int) Str::numbers($currentProfile->travelRadius->translations[0]->value);
        }

        // 1️⃣ Arkadaş user_id’leri




        // Favoriler
        $favoriteProfileIds = Favorite::where('user_id', $authUserId)
            ->pluck('favorite_id')
            ->toArray();

        // Ana cevaplar
        $mainAnswers = $this->getPupAnswers($pupProfileId);

        // Kullanıcının kendi profilleri
        $myProfileIds = PupProfile::where('user_id', $authUserId)->pluck('id')->toArray();

        $friendProfileIds = Friendship::where('status', 'accepted')
            ->where(function ($query) use ($pupProfileId) {
                $query->where('sender_id', $pupProfileId)
                    ->orWhere('receiver_id', $pupProfileId);
            })
            ->get()
            ->map(function ($friendship) use ($pupProfileId) {
                // Kendisi dışındaki ID'yi (arkadaşının ID'sini) seç
                return $friendship->sender_id == $pupProfileId
                    ? $friendship->receiver_id
                    : $friendship->sender_id;
            })
            ->toArray();

        $iBlockedPupIds = DiscoverBlackList::where('user_id', $authUserId)
            ->pluck('pup_profile_id')
            ->toArray();

        // B. Beni engelleyenleri bul:
        // Benim köpek profillerimi ($myProfileIds) kara listeye alan 'user_id' leri bul
        $usersWhoBlockedMe = DiscoverBlackList::whereIn('pup_profile_id', $myProfileIds)
            ->pluck('user_id')
            ->toArray();

        // Beni engelleyen kullanıcıların tüm köpek profillerini bul
        $pupsOfUsersWhoBlockedMe = [];
        if (!empty($usersWhoBlockedMe)) {
            $pupsOfUsersWhoBlockedMe = PupProfile::whereIn('user_id', $usersWhoBlockedMe)
                ->pluck('id')
                ->toArray();
        }

        // C. İki listeyi birleştir (Benim engellediğim köpekler + Beni engelleyenlerin köpekleri)
        $blackListProfileIds = array_unique(array_merge($iBlockedPupIds, $pupsOfUsersWhoBlockedMe));


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
            ->whereNotIn('id', $blackListProfileIds)
            ->where(function ($q) {
                $q->whereNotNull('name')
                    ->orWhereHas('user', function ($qu) {
                        $qu->where('role_id', 4);
                    });
            })
            ->get();

        $result = [];

        foreach ($otherProfiles as $profile) {

            $otherAnswers = $this->getPupAnswers($profile->id);
            $matchType = MatchClass::getMatchType($mainAnswers, $otherAnswers);
            $score = $this->matchScore($matchType);

            $distanceKm = $this->calculateDistance(
                $currentProfile->lat,
                $currentProfile->long,
                $profile->lat,
                $profile->long
            );
            if ($distanceKm > $travelRadiusKm) {
                continue; // Eğer mesafe belirlenen km'den büyükse bu profili atla ve listeye ekleme
            }
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


            $result[] = [
                'pup_profile_id' => $profile->id,
                'name' => ($profile->user->role_id == 4 && !$profile->name) ? $profile->user->name : $profile->name,
                'photo' => ($profile->user->role_id == 4) ? ($profile->user->photo_url ?? null) : ($profile->images[0]->path ?? null),

                'user' => [
                    'id' => $profile->user->id,
                    'name' => $profile->user->name,
                    'role_id' => $profile->user->role_id
                ],

                'biography' => $profile->biography,

                'vibe' => $profile->vibe->map(fn($v) => [
                    'id' => $v->id,
                    'name' => $v->translate('name'),
                ]),

                'sex' => $profile->sex,
                'breed' => $profile->breed?->translate('name'),
                'age' => $profile->ageRange?->translate('name'),
                'travel_radius' => $profile->travelRadius?->translate('name'),

                'is_favorite' => in_array($profile->id, $favoriteProfileIds),
                'is_match' => in_array($profile->id, $friendProfileIds),

                'match_type' => $matchType,
                'match_score' => $score,
                'distance_km' => $distanceKm,

                // ✅ YENİ EKLENENLER
                'conversation_id' => $conversationId,

            ];
        }

        // 3️⃣ Skora göre sırala
        $sorted = collect($result)->sortByDesc('match_score')->values();

        // 4️⃣ Pagination
        $total = $sorted->count();
        $lastPage = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        return [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
            'data' => $sorted->slice($offset, $perPage)->values()->toArray(),
        ];
    }
}
