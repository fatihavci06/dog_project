<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }
    public function start(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|integer|exists:users,id|different:user_id',
            'body' => 'required|string',
        ]);

        $senderId = Auth::id();
        $receiverId = $request->recipient_id;

        // 1. Mevcut sohbeti kontrol et
        $conversation = Conversation::where(function ($q) use ($senderId, $receiverId) {
            $q->where('user_one_id', $senderId)->where('user_two_id', $receiverId);
        })->orWhere(function ($q) use ($senderId, $receiverId) {
            $q->where('user_one_id', $receiverId)->where('user_two_id', $senderId);
        })->first();

        if (!$conversation) {
            // 2. Sohbet yoksa, yeni oluştur
            $conversation = Conversation::create([
                'user_one_id' => $senderId,
                'user_two_id' => $receiverId,
            ]);
        }

        // 3. Mesajı kaydet
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'body' => $request->body,
        ]);

        // Son mesajı güncelle (Conversation modelinde last_message_id varsa)
        // $conversation->update(['last_message_id' => $message->id]);

        // 4. JavaScript'in beklediği JSON yanıtını döndür
        return response()->json([
            'success' => true,
            'message' => $message,
            'conversation_id' => $conversation->id,
        ]);
    }
    /**
     * Conversation listesi + ilk mesajlar (index sayfası)
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $perPage = 15; // Sayfada gösterilecek kullanıcı sayısı

        // 1. Mevcut Sohbetler (Değişmedi)
        $conversations = Conversation::where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->with(['messages' => fn($q) => $q->latest()])
            ->get();

        // 2. Tüm Diğer Kullanıcılar (Artık SAYFALANDIRILIYOR)
        $allOtherUsers = User::where('id', '!=', $userId)
            ->orderBy('name', 'asc')
            ->paginate($perPage); // Sayfalandırılmış veriyi alır

        $selectedConversation = null;
        $messages = collect();

        if ($request->has('selected')) {
            // ... (Seçili konuşma mantığı değişmedi)
            $selectedConversation = $request->input('selected');
            $messages = Message::where('conversation_id', $selectedConversation)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        // view() metoduna artık sayfalandırılmış koleksiyon gönderiyoruz
        return view('messages.index', compact('conversations', 'selectedConversation', 'messages', 'allOtherUsers'));
    }

    // YENİ METOT: New Chat Listesini AJAX ile almak için.
    // Bu metodu bir route'a bağlamanız gerekecek (Örn: Route::get('users/get-others', [MessageController::class, 'getOtherUsers'])->name('users.getOtherUsers');)
    public function getOtherUsers(Request $request)
    {
        $userId = Auth::id();
        $query = $request->input('query');
        $perPage = 15;

        $usersQuery = User::where('id', '!=', $userId)->orderBy('name', 'asc');

        if ($query) {
            $usersQuery->where('name', 'like', '%' . $query . '%');
        }

        $users = $usersQuery->paginate($perPage);

        // Blade'e sadece kullanıcı listesi render edilir
        return view('messages.partials.user_list_items', ['allOtherUsers' => $users])->render();
    }

    /**
     * Seçilen conversation'un mesajları
     */
    public function show($conversationId)
    {
        $messages = Message::where('conversation_id', $conversationId)
            ->with('sender:id,name')
            ->orderBy('created_at', 'asc')
            ->take(50)
            ->get();

        // Ajax isteği ise sadece partial dön
        if (request()->ajax()) {
            return view('layouts.partials.message_list', compact('messages'))->render();
        }
    }

   public function store(Request $request, $conversationId)
    {
        $request->validate([
            'body' => 'required|string|max:5000'
        ]);

        $conversation = Conversation::findOrFail($conversationId);
        $senderId = Auth::id();

        // Alıcı ID'sini burada hesaplamaya gerek yok, Service zaten Conversation'a bakarak yapabilir.
        // Ancak Servis metodu (sendMessage) senderId ve receiverId istiyor, bu yüzden receiverId'yi bulmalıyız.
        $receiverId = $conversation->user_one_id === $senderId ? $conversation->user_two_id : $conversation->user_one_id;

        try {
            // !!! Mesaj gönderme işlemini ChatService'e delege et !!!
            // Not: ChatService::sendMessage, Conversation'ı bulmak/oluşturmak yerine
            // sadece mesajı gönderme işini yapmalıydı. Ancak mevcut Service yapınız
            // Conversation'ı da bulduğu için bu şekilde kullanacağız.
            // Biz Conversation ID'sini bildiğimiz için bu kısım biraz zorlama oluyor.

            // Eğer Service'i kullanacaksak, Service'i sadece senderId ve receiverId ile tetiklemek daha mantıklı.
            // Bu, Service'in Conversation bulma/oluşturma mantığını kullanmasını sağlar.

            // Mevcut Conversation ID'sini kullanarak Service'i çağırmak için,
            // ChatService'e Conversation ID'si alan yeni bir metot ekleyebiliriz.
            // VEYA daha basit bir yol: Yeni sohbet başlatma rotasına gitmek yerine,
            // varolan Service metodunu çağırmak için sender ve receiver kullanırız.

            $message = $this->chatService->sendMessage(
                $senderId,
                $receiverId, // conversation'dan bulduğumuz receiverId
                $request->body
            );

            // NOT: $message'ın conversation_id'si $conversationId ile aynı olacaktır.

        } catch (\Exception $e) {
            // Hata durumunda uygun bir yanıt dönün
            return response()->json([
                'success' => false,
                'message' => 'Message could not be sent: ' . $e->getMessage()
            ], 500);
        }

        // Başarılı Ajax JSON yanıtı
        return response()->json([
            'success' => true,
            'message' => $message // Service'den dönen Message objesi
        ]);
    }


    /**
     * Read more / eski mesajları yükle
     */
    public function loadMore(Request $request, $conversationId)
    {
        $lastMessageId = $request->query('last_message_id');

        $messages = Message::where('conversation_id', $conversationId)
            ->where('id', '<', $lastMessageId)
            ->with('sender:id,name')
            ->orderByDesc('created_at')
            ->take(20)
            ->get()
            ->reverse() // eski mesajları başa ekle
            ->values();

        return view('layouts.partials.message_list', compact('messages'))->render();
    }
    public function markRead($conversationId)
    {
        return $this->chatService->markRead($conversationId, Auth::id());
    }
}
