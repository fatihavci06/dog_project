<?php

namespace App\Services;

use App\Models\Friendship;
use Exception;

class FriendshipService
{
    public function send(int $authUserId, int $receiverId)
    {
        if ($authUserId == $receiverId) {
            throw new Exception("You cannot send requests to yourself.", 400);
        }

        // Zaten ilişki var mı?
        $exists = Friendship::where(function ($q) use ($authUserId, $receiverId) {
            $q->where('sender_id', $authUserId)
                ->where('receiver_id', $receiverId);
        })
            ->orWhere(function ($q) use ($authUserId, $receiverId) {
                $q->where('sender_id', $receiverId)
                    ->where('receiver_id', $authUserId);
            })
            ->first();

        if ($exists) {
            throw new Exception("A relationship with this person already exists.", 400);
        }

        return Friendship::create([
            'sender_id' => $authUserId,
            'receiver_id' => $receiverId,
            'status' => 'pending'
        ]);
    }


    public function accept(int $authUserId, int $senderId)
    {
        $req = Friendship::where('sender_id', $senderId)
            ->where('receiver_id', $authUserId)
            ->where('status', 'pending')
            ->first();

        if (!$req) {
            throw new Exception("There are no pending requests.", 404);
        }

        $req->update(['status' => 'accepted']);
        return $req;
    }


    public function reject(int $authUserId, int $senderId)
    {
        $req = Friendship::where('sender_id', $senderId)
            ->where('receiver_id', $authUserId)
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
        // 1) Arkadaşları getir
        $friends = Friendship::where(function ($q) use ($userId) {
            $q->where('sender_id', $userId)
                ->where('status', 'accepted');
        })
            ->orWhere(function ($q) use ($userId) {
                $q->where('receiver_id', $userId)
                    ->where('status', 'accepted');
            })
            ->get()
            ->map(function ($f) use ($userId) {

                $friendId = $f->sender_id == $userId
                    ? $f->receiver_id
                    : $f->sender_id;

                // 2) Pup profile bilgilerini çek
                $pup = \App\Models\PupProfile::with('images')
                    ->where('user_id', $friendId)
                    ->first();

                return [
                    'user_id'   => $friendId,
                    'name'      => $pup->name ?? null,
                    'photo'     => $pup->images[0]->path ?? null,
                    'biography' => $pup->biography ?? null,
                ];
            });

        // 3) Pagination hesapla
        $total     = $friends->count();
        $lastPage  = (int) ceil($total / $perPage);
        $offset    = ($page - 1) * $perPage;

        $paged = $friends->slice($offset, $perPage)->values();

        return [
            'current_page' => $page,
            'per_page'     => $perPage,
            'total'        => $total,
            'last_page'    => $lastPage,
            'data'         => $paged,
        ];
    }



    public function incomingRequests(int $userId)
    {
        return Friendship::where('receiver_id', $userId)
            ->where('status', 'pending')
            ->with('sender')
            ->get()
            ->map(function ($req) {
                return [
                    'sender_id' => $req->sender_id,
                    'name'      => $req->sender->name ?? null,
                    'status'    => $req->status,
                    'sent_at'   => $req->created_at,
                ];
            });
    }
    public function outgoingRequests(int $userId)
    {
        return Friendship::where('sender_id', $userId)
            ->where('status', 'pending')
            ->with('receiver')
            ->get()
            ->map(function ($req) {
                return [
                    'receiver_id' => $req->receiver_id,
                    'name'        => $req->receiver->name ?? null,
                    'status'      => $req->status,
                    'sent_at'     => $req->created_at,
                ];
            });
    }
    public function unfriend(int $authUserId, int $friendId)
    {
        // Kendini çıkaramazsın
        if ($authUserId == $friendId) {
            throw new \Exception("You can't unfriend yourself.", 400);
        }

        // Arkadaşlık kaydını bul
        $friendship = Friendship::where(function ($q) use ($authUserId, $friendId) {
            $q->where('sender_id', $authUserId)
                ->where('receiver_id', $friendId);
        })
            ->orWhere(function ($q) use ($authUserId, $friendId) {
                $q->where('sender_id', $friendId)
                    ->where('receiver_id', $authUserId);
            })
            ->where('status', 'accepted')
            ->first();

        if (!$friendship) {
            throw new \Exception("You are not friends with this person.", 404);
        }

        // Arkadaşlığı tamamen sil
        $friendship->delete();


    }
}
