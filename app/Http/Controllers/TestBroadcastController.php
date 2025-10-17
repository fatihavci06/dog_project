<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\MessageSent;
use App\Models\Message;
use Illuminate\Support\Facades\DB; // Basit bir mesaj oluşturmak için

class TestBroadcastController extends Controller
{
    public function sendTestMessage(Request $request)
    {
        // 1. Veritabanına test mesajı ekleyelim (MessageSent event'ı Message modelini bekliyor)
        $message = Message::create([
            'conversation_id' => $request->input('conversation_id', 123), // Varsayılan bir ID
            'sender_id' => $request->input('sender_id', 1),
            'receiver_id' => $request->input('receiver_id', 2),
            'body' => $request->input('body', 'Bu Postman Test Mesajıdır - ' . now()),
            // Diğer zorunlu alanlar...
        ]);

        // 2. Event'i tetikle
        event(new MessageSent($message));

        // 3. Başarılı yanıt dön
        return response()->json([
            'status' => 'success',
            'message' => 'Event Başarıyla Yayınlandı.',
            'channel' => 'private-conversation.' . $message->conversation_id
        ]);
    }
}
