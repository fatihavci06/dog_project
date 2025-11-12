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
    public function latest()
    {
        $result = $this->chatService->getLatestMessages();

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        return response()->json([
            'messages' => $result['messages'],
            'unreadCount' => $result['unreadCount'],
        ], $result['status']);
    }
    public function start(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|integer|exists:users,id|different:user_id',
            'body' => 'required|string',
        ]);

        $result = $this->chatService->startConversation($request->only(['recipient_id', 'body']));

        return response()->json($result);
    }
    /**
     * Conversation listesi + ilk mesajlar (index sayfası)
     */
    public function index(Request $request)
    {
        $selectedConversationId = $request->input('selected');
        $data = $this->chatService->getChatIndexData($selectedConversationId);

        return view('messages.index', $data);
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
        $messages = $this->chatService->show($conversationId);

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

        $receiverId = $conversation->user_one_id === $senderId ? $conversation->user_two_id : $conversation->user_one_id;

        try {


            $message = $this->chatService->sendMessage(
                $senderId,
                $receiverId,
                $request->body
            );



        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Message could not be sent: ' . $e->getMessage()
            ], 500);
        }


        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }


    /**
     * Read more / eski mesajları yükle
     */
    public function loadMore(Request $request, $conversationId)
    {
        $lastMessageId = $request->query('last_message_id');

        if (!$lastMessageId) {
            return response()->json(['html' => '', 'error' => 'last_message_id is required'], 400);
        }

        $messages = $this->chatService->loadMoreMessages($conversationId, $lastMessageId);

        // View render
        $html = view('layouts.partials.message_list', compact('messages'))->render();

        return response()->json(['html' => $html]);
    }

    public function markRead($conversationId)
    {
        return $this->chatService->markRead($conversationId, Auth::id());
    }
}
