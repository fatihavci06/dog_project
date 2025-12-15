<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\Friendship;
use Exception;

class FavoriteService
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
    /* -------------------------------
       1) Match (accepted friendship) ID’leri
    --------------------------------*/
    $friendIds = Friendship::where('status', 'accepted')
        ->where(function ($q) use ($userId) {
            $q->where('sender_id', $userId)
              ->orWhere('receiver_id', $userId);
        })
        ->get()
        ->map(fn ($f) => $f->sender_id == $userId ? $f->receiver_id : $f->sender_id)
        ->unique()
        ->values()
        ->toArray();


    /* -------------------------------
       2) Favori ID’leri
    --------------------------------*/
    $favoriteIds = Favorite::where('user_id', $userId)
        ->pluck('favorite_id')
        ->toArray();


    /* -------------------------------
       3) Favorileri çek (match olsa bile gelsin)
    --------------------------------*/
    $favorites = Favorite::with([
            'favoritePupProfile.images',
            'favoritePupProfile.breed',
            'favoritePupProfile.ageRange',
            'favoritePupProfile.travelRadius',
        ])
        ->where('user_id', $userId)
        ->get();


    /* -------------------------------
       4) Map
    --------------------------------*/
    $mapped = $favorites->map(function ($fav) use ($favoriteIds, $friendIds) {

        $pup = $fav->favoritePupProfile;

        return [
            'pup_profile_id' => $pup->id,
            'name'           => $pup->name,
            'breed'          => $pup->breed?->translate('name'),
            'age_range'      => $pup->ageRange?->translate('name'),
            'travel_radius'  => $pup->travelRadius?->translate('name'),
            'sex'            => $pup->sex,
            'photo'          => $pup->images[0]->path ?? null,
            'biography'      => $pup->biography,

            // 🔥 FLAG’LER
            'is_favorite' => in_array($pup->id, $favoriteIds) ? 1 : 0,
            'is_match'    => in_array($pup->id, $friendIds) ? 1 : 0,
        ];
    });


    /* -------------------------------
       5) Custom Pagination
    --------------------------------*/
    $total     = $mapped->count();
    $lastPage  = (int) ceil($total / $perPage);
    $offset    = ($page - 1) * $perPage;

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
