<?php

namespace App\Services;

use App\Helper\MatchClass;
use App\Models\Favorite;
use App\Models\Friendship;
use App\Models\PupProfile;
use Exception;

class FavoriteService extends BaseService
{
    public function add(int $userId, int $favoriteId)
    {
        if ($userId == $favoriteId) {
            throw new Exception("You cannot add yourself as a favorite.", 400);
        }

        // Zaten favori mi?
        $exists = Favorite::where('user_id', $userId)
            ->where('favorite_id', $favoriteId)
            ->first();

        if ($exists) {
            throw new Exception("This user is already in your favorites.", 400);
        }
        Favorite::create([
            'user_id'     => $userId,
            'favorite_id' => $favoriteId
        ]);
    }


    public function remove(int $userId, int $favoriteId)
    {
        $fav = Favorite::where('user_id', $userId)
            ->where('favorite_id', $favoriteId)
            ->first();

        if (!$fav) {
            throw new Exception("Not in the favorites.", 404);
        }

        $fav->delete();
        return true;
    }


    public function list(int $userId, int $page = 1, int $perPage = 10)
{
    /* ----------------------------------
     1) Kullanıcının sahip olduğu pup_profile_id’ler
    ---------------------------------- */
    $myProfileIds = PupProfile::where('user_id', $userId)
        ->pluck('id')
        ->toArray();

    if (empty($myProfileIds)) {
        return [
            'current_page' => $page,
            'per_page'     => $perPage,
            'total'        => 0,
            'last_page'    => 0,
            'data'         => [],
        ];
    }

    /* ----------------------------------
     2) Match (accepted friendship) → KARŞI TARAF pup_profile_id’ler
    ---------------------------------- */
    $matchedProfileIds = Friendship::where('status', 'accepted')
        ->where(function ($q) use ($myProfileIds) {
            $q->whereIn('sender_id', $myProfileIds)
              ->orWhereIn('receiver_id', $myProfileIds);
        })
        ->get()
        ->map(function ($f) use ($myProfileIds) {
            return in_array($f->sender_id, $myProfileIds)
                ? $f->receiver_id
                : $f->sender_id;
        })
        ->unique()
        ->values()
        ->toArray();

    /* ----------------------------------
     3) Favoriler (MATCH OLANLAR HARİÇ)
    ---------------------------------- */
    $favorites = Favorite::with([
            'favoritePupProfile.images',
            'favoritePupProfile.breed',
            'favoritePupProfile.vibe',
            'favoritePupProfile.ageRange',
            'favoritePupProfile.travelRadius',
            'favoritePupProfile.user',
            'favoritePupProfile.answers',
        ])
        ->where('user_id', $userId)
        ->whereNotIn('favorite_id', $matchedProfileIds) // 🔥 KRİTİK KURAL
        ->get();

    $favoriteIds = $favorites->pluck('favorite_id')->toArray();

    /* ----------------------------------
     4) Mesafe için referans profil
     (ilk profil yeterli)
    ---------------------------------- */
    $authProfile = PupProfile::whereIn('id', $myProfileIds)->first();

    /* ----------------------------------
     5) MAP
    ---------------------------------- */
    $mapped = $favorites->map(function ($fav) use (
        $favoriteIds,
        $matchedProfileIds,
        $authProfile
    ) {

        $pup = $fav->favoritePupProfile;

        $distanceKm = null;
        if ($authProfile && $pup?->lat && $pup?->long) {
            $distanceKm = $this->calculateDistance(
                $authProfile->lat,
                $authProfile->long,
                $pup->lat,
                $pup->long
            );
        }

        return [
            'pup_profile_id' => $pup->id,
            'name'           => $pup->name,
            'breed'          => $pup->breed?->translate('name'),
            'vibe'           => $pup->vibe->map(fn ($v) => [
                'id'   => $v->id,
                'name' => $v->translate('name'),
            ]),
            'user' => [
                'id'   => $pup->user->id,
                'name' => $pup->user->name,
            ],
            'age_range'     => $pup->ageRange?->translate('name'),
            'travel_radius' => $pup->travelRadius?->translate('name'),
            'sex'           => $pup->sex,
            'photo'         => $pup->images[0]->path ?? null,
            'biography'     => $pup->biography,

            // 🔥 FLAG’LER
            'is_favorite' => in_array($pup->id, $favoriteIds) ? 1 : 0,
            'is_match'    => 0, // zaten match olanlar listeye hiç girmiyor
            'distance_km' => $distanceKm,

            'match_type' => MatchClass::getMatchType(
                $pup->answers->toArray(),
                $authProfile ? $authProfile->answers->toArray() : []
            ),
        ];
    });

    /* ----------------------------------
     6) Custom Pagination
    ---------------------------------- */
    $total    = $mapped->count();
    $lastPage = (int) ceil($total / $perPage);
    $offset   = ($page - 1) * $perPage;

    $paged = $mapped->slice($offset, $perPage)->values();

    return [
        'current_page' => $page,
        'per_page'     => $perPage,
        'total'        => $total,
        'last_page'    => $lastPage,
        'data'         => $paged,
    ];
}

}
