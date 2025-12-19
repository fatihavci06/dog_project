<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        // İlişkileri burada yüklemeyip sadece ham datayı alıyoruz
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        // DÜZELTİLDİ: channels.php'deki 'users.{id}' ile eşleşmesi için 'users.' yapıldı.
        return [
            new PrivateChannel('users.' . $this->message->receiver_id),
        ];
    }
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * React Native tarafına gidecek veri paketi.
     */
    public function broadcastWith(): array
    {
        return [
            'id'              => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id'       => $this->message->sender_id,
            'receiver_id'     => $this->message->receiver_id,
            'body'            => $this->message->body,
            'created_at'      => $this->message->created_at, // İstersen ->toIso8601String() ekleyebilirsin
        ];
    }
}
