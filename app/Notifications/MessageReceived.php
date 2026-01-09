<?php
namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

// ShouldQueue arayüzü bildirimin arka planda (queue) çalışmasını sağlar
class MessageReceived extends Notification implements ShouldQueue
{
    use Queueable;

    protected $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    // Bildirimin hangi kanallarla gönderileceğini seçiyoruz
    public function via($notifiable)
    {
        // Veritabanı ve OneSignal (veya diğer) kanallarını buraya ekleyebilirsin
        return ['database'];
    }

    // Veritabanına kaydedilecek içerik
    public function toArray($notifiable)
    {
        return [
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'body' => $this->message->body,
            'type' => 'message'
        ];
    }
}
