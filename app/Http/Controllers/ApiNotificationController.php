<?php

namespace App\Http\Controllers;

use App\Http\Requests\SetOneSignalPlayerIdRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class ApiNotificationController extends ApiController
{
    //
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function setOneSignalPlayerId(SetOneSignalPlayerIdRequest $request)
    {

        return $this->notificationService->setOneSignalPlayerId($request->all());
    }
    public function changeNotificationStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        return $this->notificationService->changeNotificationStatus($request->user_id, $request->status);
    }
    public function markAsRead(Request $request, int $id)
    {
        // Mevcut kullanıcının ID'sini al
        $userId = $request->user_id;

        return $this->notificationService->markAsRead($userId, $id);
    }

    public function getSettings(Request $request)
    {
        return $this->notificationService->getNotificationSettings($request->user_id);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'required|boolean'
        ]);

        $this->notificationService->updateNotificationSettings($request->user_id, $request->settings);

        // ApiController'ın sarmalamasını atlamak ve kendi mesajınızı basmak için:
        return response()->json([
            'success' => true,
            'message' => __('messages.notification_settings_updated'), // Veya istediğiniz bir metin
            'data' => null
        ]);
    }


    public function notificationsList(Request $request)
    {
        $request->validate([
            'type' => 'nullable|string|in:announcement,date_request,date_response,friend_accepted,friend_request,info,message',
        ]);

        return $this->notificationService
            ->getUserNotifications(
                $request->user_id,
                $request->role_id,
                $request->is_read,
                $request->page ?? 1,
                $request->per_page ?? 15,
                false,
                $request->type
            );
    }
}
