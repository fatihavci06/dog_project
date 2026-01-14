<?php

namespace App\Services;

use App\Helper\MatchClass;
use App\Models\Conversation;
use App\Models\Date;
use App\Models\Favorite;
use App\Models\Friendship;
use App\Models\PupProfile;
use Exception;

class FriendshipService extends BaseService
{
    public function send(int $myPupProfileId, int $targetPupProfileId)
    {
        $myProfile     = PupProfile::findOrFail($myPupProfileId);
        $targetProfile = PupProfile::findOrFail($targetPupProfileId);

        // 🚫 Kendi pup profile’larından birine istek atamaz
        if ($myProfile->user_id === $targetProfile->user_id) {
            throw new Exception(__('errors.cannot_send_request_to_self'), 400);
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

        $friendship = Friendship::create([
            'sender_id' => $myPupProfileId,
            'receiver_id' => $targetPupProfileId,
            'status' => 'pending'
        ]);

        // 2. --- BİLDİRİM GÖNDERME ---

        // İstek atan ve alan profilleri çekelim
        $senderProfile = \App\Models\PupProfile::find($myPupProfileId);
        $targetProfile = \App\Models\PupProfile::find($targetPupProfileId);

        // Alıcı kullanıcının User modeline ulaşalım
        $targetUser = $targetProfile ? $targetProfile->user : null;
        $currentLocale = app()->getLocale();

        // Hedef kullanıcının tercih ettiği dili set et
        if (!empty($targetUser->preferred_language)) {
            app()->setLocale($targetUser->preferred_language);
        }
        if ($targetUser && !empty($targetUser->onesignal_player_id)) {




            dispatch(new \App\Jobs\SendOneSignalNotification(
                [$targetUser->onesignal_player_id],
                __('notifications.friend_request_title'),
                __('notifications.friend_request_body', [
                    'name' => $senderProfile->name
                ]),
                [
                    'friendship_id' => $friendship->id,
                    'sender_id'     => $myPupProfileId,
                    'type'          => 'friend_request',
                    'url'           => "pupcrawl://profile/{$myPupProfileId}",

                ]
            ));
        }
        app()->setLocale($currentLocale);

        return $friendship;
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
        $senderProfile = \App\Models\PupProfile::find($req->sender_id);
        $targetUser = $senderProfile ? $senderProfile->user : null;

        // Kabul eden kişi (şu anki kullanıcı): receiver_id
        $acceptorProfile = \App\Models\PupProfile::find($req->receiver_id);
        $acceptorName = $acceptorProfile ? $acceptorProfile->name : __('notifications.unknown_user');
$currentLocale = app()->getLocale();

        // Hedef kullanıcının tercih ettiği dili set et
        if (!empty($targetUser->preferred_language)) {
            app()->setLocale($targetUser->preferred_language);
        }
        if ($targetUser && !empty($targetUser->onesignal_player_id)) {




            dispatch(new \App\Jobs\SendOneSignalNotification(
                [$targetUser->onesignal_player_id],
                __('notifications.friend_accepted_title'),
                __('notifications.friend_accepted_body', [
                    'name' => $acceptorName
                ]),
                [
                    'friendship_id' => $req->id,
                    'type'          => 'friend_accepted',
                    'url'           => "pupcrawl://profile/{$req->receiver_id}"
                ]
            ));
        }
        app()->setLocale($currentLocale);


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


    public function listFriends(int $userId, int $page = 1, int $perPage = 10, int $pupProfileId = null): array
    {
        /*
    |--------------------------------------------------------------------------
    | 1️⃣ Kullanıcının Pup Profile ID'leri
    |--------------------------------------------------------------------------
    */
        if ($pupProfileId) {

            // Güvenlik: Bu pup profile kullanıcıya mı ait?
            $ownsProfile = PupProfile::where('id', $pupProfileId)
                ->where('user_id', $userId)
                ->exists();



            $myProfileIds = [$pupProfileId];
        } else {
            // Aksi halde kullanıcının tüm pup profilleri
            $myProfileIds = PupProfile::where('user_id', $userId)
                ->pluck('id')
                ->toArray();
        }

        /*
    |--------------------------------------------------------------------------
    | 2️⃣ Favoriler (tek sorgu)
    |--------------------------------------------------------------------------
    */
        $favoriteIds = Favorite::where('user_id', $userId)
            ->pluck('favorite_id')
            ->toArray();

        /*
    |--------------------------------------------------------------------------
    | 3️⃣ Arkadaşlıklar (paginate + eager loading)
    |--------------------------------------------------------------------------
    */
        $friendships = Friendship::query()
            ->where('status', 'accepted')
            ->where(function ($q) use ($myProfileIds) {
                $q->whereIn('sender_id', $myProfileIds)
                    ->orWhereIn('receiver_id', $myProfileIds);
            })
            ->with([
                'sender.user',
                'sender.vibe',
                'sender.images',
                'sender.answers',
                'sender.breed',
                'sender.ageRange',
                'sender.travelRadius',

                'receiver.user',
                'receiver.vibe',
                'receiver.images',
                'receiver.answers',
                'receiver.breed',
                'receiver.ageRange',
                'receiver.travelRadius',
            ])
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        /*
    |--------------------------------------------------------------------------
    | 4️⃣ Bu sayfadaki TÜM Pup Profile ID'leri
    |--------------------------------------------------------------------------
    */
        $profileIdsOnPage = collect();

        foreach ($friendships as $f) {
            if ($f->sender)   $profileIdsOnPage->push($f->sender->id);
            if ($f->receiver) $profileIdsOnPage->push($f->receiver->id);
        }

        $uniqueProfileIds = $profileIdsOnPage->unique()->values()->toArray();

        /*
    |--------------------------------------------------------------------------
    | 5️⃣ Accepted Date'leri TEK sorguda çek (pup_profile_id bazlı)
    |--------------------------------------------------------------------------
    */
        $allDates = Date::query()
            ->where('status', 'accepted')
            ->where(function ($q) use ($uniqueProfileIds) {
                $q->whereIn('sender_id', $uniqueProfileIds)
                    ->orWhereIn('receiver_id', $uniqueProfileIds);
            })
            ->orderByDesc('meeting_date')
            ->get();

        /*
    |--------------------------------------------------------------------------
    | 6️⃣ Conversation'ları TEK sorguda çek (user_id bazlı)
    |--------------------------------------------------------------------------
    */
        $allConversations = Conversation::query()
            ->where(function ($q) {
                $q->whereColumn('user_one_id', '<>', 'user_two_id');
            })
            ->get();

        /*
    |--------------------------------------------------------------------------
    | 7️⃣ DATA MAPPING
    |--------------------------------------------------------------------------
    */
        $data = $friendships->getCollection()->map(function ($req) use (
            $myProfileIds,
            $favoriteIds,
            $userId,
            $allDates,
            $allConversations
        ) {

            // Ben gönderici miyim?
            $isSenderMe = in_array($req->sender_id, $myProfileIds);

            $friend = $isSenderMe ? $req->receiver : $req->sender;
            $me     = $isSenderMe ? $req->sender   : $req->receiver;


            // Pup Profile ID'ler
            $friendProfileId = $friend->id;
            $meProfileId     = $me->id;

            // User ID'ler (chat & conversation)
            $friendUserId = $friend->user->id;
            $meUserId     = $me->user->id;

            /*
        |--------------------------------------------------------------------------
        | Son Accepted Date (pup_profile_id bazlı)
        |--------------------------------------------------------------------------
        */
            $lastDate = $allDates->first(function ($date) use ($meProfileId, $friendProfileId) {
                return (
                    ($date->sender_id == $meProfileId && $date->receiver_id == $friendProfileId) ||
                    ($date->sender_id == $friendProfileId && $date->receiver_id == $meProfileId)
                );
            });

            /*
        |--------------------------------------------------------------------------
        | Conversation (user_id bazlı)
        |--------------------------------------------------------------------------
        */
            $conversation = $allConversations->first(function ($c) use ($meUserId, $friendUserId) {
                return (
                    ($c->user_one_id == $meUserId && $c->user_two_id == $friendUserId) ||
                    ($c->user_one_id == $friendUserId && $c->user_two_id == $meUserId)
                );
            });

            return [
                'id'             => $req->id,
                'pup_profile_id' => $friend->id,
                'name'           => $friend->name,
                'status'         => $req->status,
                'sent_at'        => optional($req->created_at)->format('d-m-Y H:i'),

                'last_chat_at' => MessageService::getLastChatDateBetweenProfiles(
                    $userId,
                    $friendUserId
                ),

                'vibe' => $friend->vibe->map(fn($v) => [
                    'id'   => $v->id,
                    'name' => $v->translate('name'),
                ]),

                'user' => [
                    'id'   => $friendUserId,
                    'name' => $friend->user->name,
                    'role_id' => $friend->user->role_id,
                ],

                'breed'         => $friend->breed?->translate('name'),
                'age_range'     => $friend->ageRange?->translate('name'),
                'travel_radius' => $friend->travelRadius?->translate('name'),
                'sex'           => $friend->sex,
                'photo'         => $friend->images->first()->path ?? null,
                'biography'     => $friend->biography,

                'is_favorite' => in_array($friend->id, $favoriteIds) ? 1 : 0,

                'match_type' => MatchClass::getMatchType(
                    MatchClass::normalize($me->answers->toArray()),
                    MatchClass::normalize($friend->answers->toArray())
                ),

                'distance_km' => $this->calculateDistance(
                    $me->lat ?? 0,
                    $me->long ?? 0,
                    $friend->lat ?? 0,
                    $friend->long ?? 0
                ),

                // Pup profile bazlı date
                'date' => $lastDate,

                // User bazlı chat
                'conversation_id' => $conversation?->id,
            ];
        });

        /*
    |--------------------------------------------------------------------------
    | 8️⃣ Pagination Response
    |--------------------------------------------------------------------------
    */
        return [
            'current_page' => $friendships->currentPage(),
            'per_page'     => $friendships->perPage(),
            'total'        => $friendships->total(),
            'last_page'    => $friendships->lastPage(),
            'data'         => $data,
        ];
    }





    public function incomingRequests(int $userId, int $pupProfileId = null)
    {
        if ($pupProfileId) {

            // Güvenlik: Bu pup profile kullanıcıya mı ait?
            $ownsProfile = PupProfile::where('id', $pupProfileId)
                ->where('user_id', $userId)
                ->exists();


            $pupProfileIds = [$pupProfileId];
        } else {
            // Aksi halde kullanıcının tüm pup profilleri
            $pupProfileIds = PupProfile::where('user_id', $userId)
                ->pluck('id')
                ->toArray();
        }
        $favoriteIds = Favorite::where('user_id', $userId)
            ->pluck('favorite_id')
            ->toArray();

        return Friendship::where('status', 'pending')
            ->has('sender')
            ->has('receiver')
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
                        'name'     => $req->sender->user->name,
                        'role_id'  => $req->sender->user->role_id
                    ],
                    'age_range'      => $req->sender->ageRange?->translate('name'),
                    'travel_radius'  => $req->sender->travelRadius?->translate('name'),
                    'breed'          => $req->sender->breed?->translate('name'),
                    'sex'            => $req->sender->sex,
                    'photo'          => $req->sender->images[0]->path ?? null,
                    'biography'      => $req->sender->biography,

                    'is_favorite' => in_array($req->sender->id, $favoriteIds) ? 1 : 0,
                    'match_type'   => MatchClass::getMatchType(
                        MatchClass::normalize($req->sender->answers->toArray()),
                        MatchClass::normalize($req->receiver->answers->toArray())
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
    public function outgoingRequests(int $userId, int $pupProfileId = null)
    {
        if ($pupProfileId) {

            // Güvenlik: Bu pup profile kullanıcıya mı ait?
            $ownsProfile = PupProfile::where('id', $pupProfileId)
                ->where('user_id', $userId)
                ->exists();



            $pupProfileIds = [$pupProfileId];
        } else {
            // Aksi halde kullanıcının tüm pup profilleri
            $pupProfileIds = PupProfile::where('user_id', $userId)
                ->pluck('id')
                ->toArray();
        }
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
                        'name'     => $req->receiver->user->name,
                        'role_id'  => $req->receiver->user->role_id
                    ],
                    'age_range'      => $req->receiver->ageRange?->translate('name'),
                    'breed'          => $req->receiver->breed?->translate('name'),
                    'travel_radius'  => $req->receiver->travelRadius?->translate('name'),
                    'sex'            => $req->receiver->sex,
                    'photo'          => $req->receiver->images[0]->path ?? null,
                    'biography'      => $req->receiver->biography,
                    'is_favorite' => in_array($req->receiver->id, $favoriteIds) ? 1 : 0,
                    'match_type'   => MatchClass::getMatchType(
                        MatchClass::normalize($req->sender->answers->toArray()),
                        MatchClass::normalize($req->receiver->answers->toArray())
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
    public function totalMatchAndChats(int $userId, int $pupProfileId = null)
    {
        $pupProfileIds = $pupProfileId
            ? [$pupProfileId]
            : PupProfile::where('user_id', $userId)->pluck('id')->toArray();

        // ✅ MATCH
        $totalMatches = Friendship::where('status', 'accepted')
            ->where(function ($q) use ($pupProfileIds) {
                $q->whereIn('sender_id', $pupProfileIds)
                    ->orWhereIn('receiver_id', $pupProfileIds);
            })
            ->count();

        // ⚠️ CHAT (user bazlıysa)
        $totalChats = Conversation::where(function ($q) use ($userId) {
            $q->where('user_one_id', $userId)
                ->orWhere('user_two_id', $userId);
        })->count();

        return [
            'total_matches' => $totalMatches,
            'total_chats'   => $totalChats,
        ];
    }
}
