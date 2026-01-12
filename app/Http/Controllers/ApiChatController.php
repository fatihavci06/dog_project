<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatSendRequest;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class ApiChatController extends ApiController
{
    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function messages($conversationId, Request $request)
    {
        return $this->chatService->getMessages($conversationId, $request->user_id);
    }

    public function send(ChatSendRequest $request)
    {


        return  $this->chatService->sendMessage(
            $request->user_id,
            $request->to_user_id,
            $request->body
        );
    }

    public function inbox(Request $request)
    {
        return  $this->chatService->getInbox($request->user_id);
    }

    public function markRead($conversationId, Request $request)
    {
        return $this->chatService->markRead($conversationId, $request->user_id);
    }

    public function allMessages($conversationId, Request $request)
    {
        $perPage = $request->get('per_page', 50);
        $page = $request->get('page', 1);

        return $this->chatService->getAllMessages($conversationId, $request->user_id, $perPage, $page);
    }
    public function userPupProfileList(Request $request)
    {
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 10);

        return $this->chatService->getUserPupProfileList(
            $request->user_id,
            $page,
            $perPage
        );
    }
}
