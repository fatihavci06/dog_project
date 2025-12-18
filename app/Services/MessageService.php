<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MessageService
{
    public function getUserConversations(int $userId)
    {

        return Conversation::where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->with(['messages' => function ($q) {
                $q->latest();
            }])
            ->get();
    }

    /**
     * Belirli konuşmanın mesajlarını getir.
     */
    public function getConversationMessages(int $conversationId, int $limit = 30)
    {
        return Message::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->take($limit)
            ->get(['id', 'sender_id', 'receiver_id', 'body', 'created_at']);
    }

    /**
     * Yeni mesaj gönder.
     */
    public function sendMessage(array $data)
    {
        $senderId = Auth::id();
        $receiverId = $data['receiver_id'];

        // Konuşma varsa bul, yoksa oluştur
        $conversation = Conversation::firstOrCreate([
            'user_one_id' => min($senderId, $receiverId),
            'user_two_id' => max($senderId, $receiverId),
        ]);

        // Mesaj oluştur
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'body' => $data['body'],
        ]);

        // Event yayını (örneğin Pusher, WebSocket)
        broadcast(new MessageSent($message))->toOthers();

        return $message->load('sender:id,name');
    }

    /**
     * Daha fazla mesaj (pagination / “read more”) yükle.
     */
    public function loadMoreMessages(int $conversationId, ?int $lastMessageId = null, int $limit = 20)
    {
        $query = Message::where('conversation_id', $conversationId)
            ->orderByDesc('created_at')
            ->take($limit);

        if ($lastMessageId) {
            $query->where('id', '<', $lastMessageId);
        }

        return $query->get()->reverse()->values();
    }
    public static function getLastChatDateBetweenProfiles($profileOneId,  $profileTwoId)
    {

        $conversation = Conversation::where(function ($query) use ($profileOneId, $profileTwoId) {
            $query->where('user_one_id', $profileOneId)
                ->where('user_two_id', $profileTwoId);
        })->orWhere(function ($query) use ($profileOneId, $profileTwoId) {
            $query->where('user_one_id', $profileTwoId)
                ->where('user_two_id', $profileOneId);
        })->first();

        if (!$conversation) {
            return null;
        }

        $lastMessage = Message::where('conversation_id', $conversation->id)
            ->orderByDesc('created_at')
            ->first();

        return $lastMessage
            ? Carbon::parse($lastMessage->updated_at)->format('d-m-Y H:i')
            : null;
    }
}
