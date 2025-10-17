<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Jobs\SendOneSignalNotification;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
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
        $player = $to->onesignal_player_id ? [$to->onesignal_player_id] : [];
        if ($player) {
            dispatch(new SendOneSignalNotification(
                $player,
                "Yeni mesaj",
                mb_strimwidth($message->body ?? 'Yeni mesaj', 0, 100),
                [
                    'conversation_id' => $conv->id,
                    'sender_id' => $user->id,
                    'type' => 'message'
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
                        'avatar' => $otherUser->avatar ?? null,
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
}
