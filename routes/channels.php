<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('conversation.{conversationId}', function ($userPayload, $conversationId) {
    Log::info('channels.php');
    $userId = $userPayload->user_id ?? null;
    if (!$userId) return false;

    $conv = \App\Models\Conversation::find($conversationId);
    if (!$conv) return false;

    return $conv->user_one_id === $userId || $conv->user_two_id === $userId;
});



