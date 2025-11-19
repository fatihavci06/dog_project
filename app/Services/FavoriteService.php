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

        return Favorite::create([
            'user_id' => $userId,
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
        // Arkadaş oldukları favoride görünmesin
        $friendIds = Friendship::where(function ($q) use ($userId) {
            $q->where('sender_id', $userId)
                ->where('status', 'accepted');
        })
            ->orWhere(function ($q) use ($userId) {
                $q->where('receiver_id', $userId)
                    ->where('status', 'accepted');
            })
            ->get()
            ->map(fn($f) => $f->sender_id == $userId ? $f->receiver_id : $f->sender_id)
            ->toArray();

        // FAVORİLERİ GETİR
        $favorites = Favorite::where('user_id', $userId)
            ->whereNotIn('favorite_id', $friendIds)
            ->get();

        // PAGINATION
        $total     = $favorites->count();
        $lastPage  = (int) ceil($total / $perPage);
        $offset    = ($page - 1) * $perPage;

        $paged = $favorites->slice($offset, $perPage)->values();

        return [
            'current_page' => $page,
            'per_page'     => $perPage,
            'total'        => $total,
            'last_page'    => $lastPage,
            'data'         => $paged,
        ];
    }
}
