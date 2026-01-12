<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Jobs\SendOneSignalNotification;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public function getMessages(int $conversationId, int $userId)
    {
        $user = User::findOrFail($userId);
        $conversation = Conversation::findOrFail($conversationId);

        if (!in_array($user->id, [$conversation->user_one_id, $conversation->user_two_id])) {
            throw new \Exception('forbidden');
        }

        $messages = Message::with('sender')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $messages->map(function ($msg) {
            return [
                'sender_id' => $msg->sender_id,
                'receiver_id' => $msg->receiver_id,
                'sender_name' => $msg->sender->name,
                'created_at' => $msg->created_at,
                'body' => $msg->body,
            ];
        });
    }

    public function sendMessage(int $fromUserId, int $toUserId, ?string $body)
    {
        $user = User::findOrFail($fromUserId);
        $to = User::findOrFail($toUserId);

        // conversation bul veya oluştur
        [$a, $b] = [$user->id, $to->id];
        if ($a > $b) [$a, $b] = [$b, $a];

        $conv = Conversation::firstOrCreate([
            'user_one_id' => $a,
            'user_two_id' => $b
        ]);

        $message = Message::create([
            'conversation_id' => $conv->id,
            'sender_id' => $user->id,
            'receiver_id' => $to->id,
            'body' => $body,
            'status' => 'sent'
        ]);

        // Broadcast
        event(new MessageSent($message));

        // OneSignal push
        $player = !empty($to->onesignal_player_id) ? [$to->onesignal_player_id] : [];
        if (!empty($player)) {
            dispatch(new SendOneSignalNotification(
                $player,
                "Yeni Mesaj",
                mb_strimwidth($body, 0, 100),
                [
                    'conversation_id' => $conv->id,
                    'type' => 'message',
                    'url' => "pupcrawl://chat/{$conv->id}" // Dinamik link
                ]
            ));
        }


        return $message;
    }

    public function getInbox(int $userId)
    {
        $user = User::findOrFail($userId);

        $conversations = Conversation::where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->with(['messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($conv) use ($user) {
                $otherUserId = $conv->user_one_id === $user->id ? $conv->user_two_id : $conv->user_one_id;
                $otherUser = User::find($otherUserId);

                $unreadCount = Message::where('conversation_id', $conv->id)
                    ->where('receiver_id', $user->id)
                    ->whereNull('read_at')
                    ->count();

                $lastMessage = $conv->messages->first();

                return [
                    'conversation_id' => $conv->id,
                    'user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'avatar' => $otherUser->photo_url ?? null,
                    ],
                    'last_message' => $lastMessage ? [
                        'id' => $lastMessage->id,
                        'conversation_id' => $lastMessage->conversation_id,
                        'sender_id' => $lastMessage->sender_id,
                        'receiver_id' => $lastMessage->receiver_id,
                        'body' => $lastMessage->body,
                        'status' => $lastMessage->status,
                        'created_at' => $lastMessage->created_at,
                        'updated_at' => $lastMessage->updated_at,
                    ] : null,
                    'unread_count' => $unreadCount,
                    'updated_at' => $conv->updated_at,
                ];
            })
            ->sortByDesc('updated_at')
            ->values();

        return $conversations;
    }

    public function markRead(int $conversationId, int $userId)
    {
        $conv = Conversation::findOrFail($conversationId);

        if (!in_array($userId, [$conv->user_one_id, $conv->user_two_id])) {
            throw new \Exception('forbidden');
        }

        Message::where('conversation_id', $conversationId)
            ->where('receiver_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'status' => 'read']);

        return true;
    }

    public function getAllMessages(int $conversationId, int $userId, int $perPage = 50, int $page = 1)
    {
        $user = User::findOrFail($userId);
        $conversation = Conversation::findOrFail($conversationId);

        if (!in_array($user->id, [$conversation->user_one_id, $conversation->user_two_id]) && !$user->is_admin) {
            throw new \Exception('forbidden');
        }

        return Message::with('sender', 'receiver')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);
    }
    public function getLatestMessages()
    {
        $userId = Auth::id();

        if (!$userId) {
            return [
                'error' => 'Unauthorized',
                'status' => 401,
            ];
        }

        $messages = Message::with('sender')
            ->where('receiver_id', $userId)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'body' => $msg->body,
                    'conversation_id' => $msg->conversation_id,
                    'sender' => [
                        'name' => $msg->sender->name ?? 'Unknown',
                        'profile_photo_url' => $msg->sender->profile_photo_url ?? asset('storage/profile.jpg'),
                    ],
                    'created_at' => $msg->created_at->toDateTimeString(),
                ];
            });

        $unreadCount = Message::where('receiver_id', $userId)
            ->whereNull('read_at')
            ->count();

        return [
            'messages' => $messages,
            'unreadCount' => $unreadCount,
            'status' => 200,
        ];
    }
    public function show(int $conversationId)
    {
        return Message::where('conversation_id', $conversationId)
            ->with('sender:id,name')
            ->orderBy('created_at', 'asc')
            ->take(50)
            ->get();
    }
    public function startConversation(array $data)
    {
        $senderId = Auth::id();
        $receiverId = $data['recipient_id'];
        $body = $data['body'];

        // 1️⃣ Mevcut sohbeti kontrol et
        $conversation = Conversation::where(function ($q) use ($senderId, $receiverId) {
            $q->where('user_one_id', $senderId)
                ->where('user_two_id', $receiverId);
        })->orWhere(function ($q) use ($senderId, $receiverId) {
            $q->where('user_one_id', $receiverId)
                ->where('user_two_id', $senderId);
        })->first();

        // 2️⃣ Sohbet yoksa, yeni oluştur
        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one_id' => $senderId,
                'user_two_id' => $receiverId,
            ]);
        }

        // 3️⃣ Mesajı oluştur
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $senderId,
            'receiver_id'     => $receiverId,
            'body'            => $body,
        ]);

        // 4️⃣ JSON formatında döndür
        return [
            'success' => true,
            'message' => $message,
            'conversation_id' => $conversation->id,
        ];
    }
    public function getChatIndexData(?int $selectedConversationId = null, int $perPage = 15)
    {
        $userId = Auth::id();

        // 1️⃣ Kullanıcının mevcut sohbetleri
        $conversations = Conversation::where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->with(['messages' => fn($q) => $q->latest()])
            ->get();

        // 2️⃣ Diğer kullanıcılar (sayfalı)
        $allOtherUsers = User::where('id', '!=', $userId)
            ->orderBy('name', 'asc')
            ->paginate($perPage);

        // 3️⃣ Seçili sohbet varsa mesajları al
        $selectedConversation = null;
        $messages = collect();

        if ($selectedConversationId) {
            $selectedConversation = $selectedConversationId;
            $messages = Message::where('conversation_id', $selectedConversation)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return compact('conversations', 'selectedConversation', 'messages', 'allOtherUsers');
    }
    public function loadMoreMessages(int $conversationId, int $lastMessageId, int $take = 20)
    {
        return Message::where('conversation_id', $conversationId)
            ->where('id', '<', $lastMessageId)
            ->with('sender:id,name')
            ->orderByDesc('created_at')
            ->take($take)
            ->get()
            ->reverse() // eski mesajları başa ekle
            ->values();
    }
    public function getUserPupProfileList(int $userId, int $page = 1, int $perPage = 10)
{
    $chatUserIds = Message::where(function ($q) use ($userId) {
            $q->where('sender_id', $userId)
              ->orWhere('receiver_id', $userId);
        })
        ->get()
        ->map(function ($message) use ($userId) {
            return $message->sender_id == $userId
                ? $message->receiver_id
                : $message->sender_id;
        })
        ->unique()
        ->values();

    $mapped = User::with([
            'pupProfiles' => function ($q) {
                $q->select('id', 'user_id', 'name')
                  ->with([
                      'images' => function ($q) {
                          $q->select('id', 'pup_profile_id', 'path');
                      }
                  ]);
            }
        ])
        ->whereIn('id', $chatUserIds)
        ->select('id', 'name', 'photo')
        ->get()
        ->map(function ($user) {

            // user_id dışa al
            $user->user_id = $user->id;

            // photo kaldır
            $user->makeHidden('photo');

            $user->pupProfiles->each(function ($pup) {

                // pup_profiles.user_id kaldır
                $pup->makeHidden('user_id');

                // images sadeleştir
                $pup->images->each(function ($image) {
                    $image->makeHidden(['id', 'pup_profile_id']);
                });
            });

            return $user;
        });

    // 🔹 MANUEL PAGINATION
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
