<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MessageService;

class ApiMessageController extends ApiController
{
    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function inbox()
    {
      return $this->messageService->inbox();

    }

    public function conversation($userId)
    {
        return $this->messageService->conversation($userId);

    }

    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        return $this->messageService->send($request->only(['receiver_id', 'message']));


    }
}
