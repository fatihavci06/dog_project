<?php

namespace App\Services;

use App\Helper\MatchClass;
use App\Models\Favorite;
use App\Models\Friendship;
use App\Models\PupProfile;
use Exception;

class FriendshipService extends BaseService
{
    public function send(int $myPupProfileId, int $targetPupProfileId)
    {
        if ($myPupProfileId == $targetPupProfileId) {
            throw new Exception("You cannot send requests to yourself.", 400);
        }

        // Zaten ilişki var mı?
        $exists = Friendship::where(function ($q) use ($myPupProfileId, $targetPupProfileId) {
            $q->where('sender_id', $myPupProfileId)
                ->where('receiver_id', $targetPupProfileId);
        })
            ->orWhere(function ($q) use ($myPupProfileId, $targetPupProfileId) {
                $q->where('sender_id', $targetPupProfileId)
                    ->where('receiver_id', $myPupProfileId);
            })
            ->first();

        if ($exists) {
            throw new Exception("A relationship with this person already exists.", 400);
        }

        return Friendship::create([
            'sender_id' => $myPupProfileId,
            'receiver_id' => $targetPupProfileId,
            'status' => 'pending'
        ]);
    }


    public function accept(int $authUserId, int $friendId)
    {
        $req = Friendship::where('id', $friendId)
            ->where('status', 'pending')
            ->first();

        if (!$req) {
            throw new Exception("There are no pending requests.", 404);
        }

        $req->update(['status' => 'accepted']);
        return $req;
    }


    public function reject(int $authUserId, int $friendId)
    {
        $req = Friendship::where('id', $friendId)
            ->where('status', 'pending')
            ->first();


        if (!$req) {
            throw new Exception("There are no pending requests.", 404);
        }

        $req->update(['status' => 'rejected']);
        return $req;
    }


    public function listFriends(int $userId, int $page = 1, int $perPage = 10)
    {
        $myProfileIds = PupProfile::where('user_id', $userId)->pluck('id')->toArray();

        $favoriteIds = Favorite::where('user_id', $userId)
            ->pluck('favorite_id')
            ->toArray();

        $friends = Friendship::where(function ($q) use ($myProfileIds) {
            $q->whereIn('sender_id', $myProfileIds)
                ->orWhereIn('receiver_id', $myProfileIds);
        })
            ->where('status', 'accepted')
            ->with(['sender.user', 'receiver.user', 'sender.vibe', 'sender.images', 'sender.answers', 'receiver.answers'])
            ->get()
            ->map(function ($req) use ($myProfileIds, $favoriteIds) {

                // 🔥 KARŞI TARAFI BUL
                $friend = in_array($req->sender_id, $myProfileIds)
                    ? $req->receiver
                    : $req->sender;

                $me = in_array($req->sender_id, $myProfileIds)
                    ? $req->sender
                    : $req->receiver;

                return [
                    'id'              => $req->id,
                    'pup_profile_id'  => $friend->id,
                    'name'            => $friend->name,
                    'status'          => $req->status,
                    'sent_at'         => optional($req->created_at)->format('d-m-Y H:i'),

                    'last_chat_at' => MessageService::getLastChatDateBetweenProfiles(
                        $me->id,
                        $friend->id
                    ),

                    'vibe' => $friend->vibe->map(fn($v) => [
                        'id'   => $v->id,
                        'name' => $v->translate('name'),
                    ]),

                    'user' => [
                        'id'   => $friend->user->id,
                        'name' => $friend->user->name,
                    ],

                    'age_range'     => $friend->ageRange?->translate('name'),
                    'travel_radius' => $friend->travelRadius?->translate('name'),
                    'sex'           => $friend->sex,
                    'photo'         => $friend->images[0]->path ?? null,
                    'biography'     => $friend->biography,

                    'is_favorite' => in_array($friend->id, $favoriteIds) ? 1 : 0,

                    'match_type' => MatchClass::getMatchType(
                        $me->answers->toArray(),
                        $friend->answers->toArray()
                    ),

                    'distance_km' => $this->calculateDistance(
                        $me->lat ?? 0,
                        $me->long ?? 0,
                        $friend->lat ?? 0,
                        $friend->long ?? 0
                    ),
                ];
            });

        // Pagination
        $total    = $friends->count();
        $lastPage = (int) ceil($total / $perPage);
        $offset   = ($page - 1) * $perPage;

        return [
            'current_page' => $page,
            'per_page'     => $perPage,
            'total'        => $total,
            'last_page'    => $lastPage,
            'data'         => $friends->slice($offset, $perPage)->values(),
        ];
    }




    public function incomingRequests(int $userId)
    {
        $pupProfileIds = PupProfile::where('user_id', $userId)->pluck('id');
        $favoriteIds = Favorite::where('user_id', $userId)
            ->pluck('favorite_id')
            ->toArray();

        return Friendship::where('status', 'pending')
            ->with('receiver', 'sender')
            ->whereIn('receiver_id', $pupProfileIds) // 🔥 user’a ait pup'lar
            ->get()
            ->map(function ($req) use ($favoriteIds) {
                return [
                    'id' => $req->id,
                    'pup_profile_id' => $req->sender_id,
                    'name'        => $req->sender->name ?? null,
                    'status'      => $req->status,
                    'sent_at' => $req->created_at ? $req->created_at->format('d-m-Y H:i') : null,
                    'vibe' => $req->sender->vibe->map(fn($v) => [
                        'id'   => $v->id,
                        'name' => $v->translate('name'),
                    ]),
                    'user'           => [
                        'id'       => $req->sender->user->id,
                        'name'     => $req->sender->user->name
                    ],
                    'age_range'      => $req->sender->ageRange?->translate('name'),
                    'travel_radius'  => $req->sender->travelRadius?->translate('name'),
                    'breed'          => $req->sender->breed?->translate('name'),
                    'sex'            => $req->sender->sex,
                    'photo'          => $req->sender->images[0]->path ?? null,
                    'biography'      => $req->sender->biography,

                    'is_favorite' => in_array($req->sender->id, $favoriteIds) ? 1 : 0,
                    'match_type'   => MatchClass::getMatchType(
                        $req->sender->answers->toArray(),
                        $req->receiver->answers->toArray()
                    ),

                    'distance_km' => $this->calculateDistance(
                        $req->receiver->lat ?? 0,
                        $req->receiver->long ?? 0,
                        $req->sender->lat ?? 0, // Hedef profilin lat
                        $req->sender->long ?? 0 // Hedef profilin long
                    ),

                ];
            });
    }
    public function outgoingRequests(int $userId)
    {
        $pupProfileIds = PupProfile::where('user_id', $userId)->pluck('id');
        $favoriteIds = Favorite::where('user_id', $userId)
            ->pluck('favorite_id')
            ->toArray();

        return Friendship::where('status', 'pending')
            ->with('receiver', 'sender')
            ->whereIn('sender_id', $pupProfileIds) // 🔥 user’a ait pup'lar
            ->get()
            ->map(function ($req) use ($favoriteIds) {
                return [
                    'id' => $req->id,
                    'pup_profile_id' => $req->receiver_id,
                    'name'        => $req->receiver->name ?? null,
                    'status'      => $req->status,
                    'sent_at' => $req->created_at ? $req->created_at->format('d-m-Y H:i') : null,
                    'vibe' => $req->receiver->vibe->map(fn($v) => [
                        'id'   => $v->id,
                        'name' => $v->translate('name'),
                    ]),
                    'user'           => [
                        'id'       => $req->receiver->user->id,
                        'name'     => $req->receiver->user->name
                    ],
                    'age_range'      => $req->receiver->ageRange?->translate('name'),
                    'breed'          => $req->receiver->breed?->translate('name'),
                    'travel_radius'  => $req->receiver->travelRadius?->translate('name'),
                    'sex'            => $req->receiver->sex,
                    'photo'          => $req->receiver->images[0]->path ?? null,
                    'biography'      => $req->receiver->biography,
                    'is_favorite' => in_array($req->receiver->id, $favoriteIds) ? 1 : 0,
                    'match_type'   => MatchClass::getMatchType(
                        $req->sender->answers->toArray(),
                        $req->receiver->answers->toArray()
                    ),

                    'distance_km' => $this->calculateDistance(
                        $req->receiver->lat,
                        $req->receiver->long,
                        $req->sender->lat, // Hedef profilin lat
                        $req->sender->long // Hedef profilin long
                    ),

                ];
            });
    }

    public function unfriend(int $userId, int $friendPupId)
    {
        // 1. Kullanıcının kendi pup profillerini bul (Güvenlik için)
        Friendship::where('id', $friendPupId)->delete();
    }
    public function cancelFriendRequest(int $userId, int $friendPupId)
    {
        // 1. Kullanıcının kendi pup profillerini bul (Güvenlik için)
        Friendship::where('id', $friendPupId)->delete();
    }
    public function totalMatchAndChats(int $userId)
    {
        $pupProfileIds = PupProfile::where('user_id', $userId)->pluck('id');

        $totalMatches = Friendship::where(function ($q) use ($pupProfileIds) {
            $q->whereIn('sender_id', $pupProfileIds)
                ->where('status', 'accepted');
        })
            ->orWhere(function ($q) use ($pupProfileIds) {
                $q->whereIn('receiver_id', $pupProfileIds)
                    ->where('status', 'accepted');
            })
            ->count();

        // Toplam chat sayısını hesapla
        $totalChats = \App\Models\Conversation::where(function ($q) use ($pupProfileIds) {
            $q->whereIn('user_one_id', $pupProfileIds)
                ->orWhereIn('user_two_id', $pupProfileIds);
        })->count();

        return [
            'total_matches' => $totalMatches,
            'total_chats'   => $totalChats,
        ];
    }
}
