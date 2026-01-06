<?php

namespace App\Http\Controllers;

use App\Http\Requests\FriendSendRequest;
use App\Http\Requests\FriendActionRequest;
use App\Services\FriendshipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiFriendshipController extends ApiController
{
    public function send(FriendSendRequest $request, FriendshipService $service)
    {
        return $service->send($request->my_pup_profile_id, $request->target_pup_profile_id);
    }

    public function accept(FriendActionRequest $request, FriendshipService $service)
    {
        return $service->accept($request->user_id, $request->friend_id);
    }

    public function reject(FriendActionRequest $request, FriendshipService $service)
    {
        return $service->reject($request->user_id, $request->friend_id);
    }

    public function friends(Request $request, FriendshipService $service)
    {
        $page     = (int) $request->get('page', 1);
        $perPage  = (int) $request->get('per_page', 10);

        return $service->listFriends($request->user_id, $page, $perPage);
    }

    public function incoming(FriendshipService $service)
    {
        return $service->incomingRequests(request()->user_id);
    }

    public function outgoing(FriendshipService $service)
    {
        return $service->outgoingRequests(request()->user_id);
    }
    public function unfriend(Request $request, FriendshipService $service)
    {
        $request->validate([
            'friend_id' => 'required|integer|exists:friendships,id'
        ]);

        return $service->unfriend($request->user_id, $request->friend_id);
    }
    public function cancelFriendRequest(Request $request, FriendshipService $service)
    {

        $request->validate([
            'friend_id' => 'required|integer|exists:friendships,id'
        ]);
        Log::info('Cancel Friend Request', ['user_id' => $request->user_id, 'friend_id' => $request->friend_id]);
        return $service->cancelFriendRequest($request->user_id, $request->friend_id);
    }
    public function totalMatchAndChats(Request $request, FriendshipService $service)
    {
        return $service->totalMatchAndChats($request->user_id);
    }
}
