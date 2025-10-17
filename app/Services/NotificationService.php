<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Role;
use Berkayk\OneSignal\OneSignalClient;

class NotificationService
{
    protected $oneSignal;

    public function __construct()
    {
        $this->oneSignal = new OneSignalClient(
            env('ONESIGNAL_APP_ID'),
            env('ONESIGNAL_API_KEY'),
            env('ONESIGNAL_USER_AUTH_KEY')
        );
    }

    public function sendNotification(array $data)
    {
        // 1️⃣ Bildirim oluştur
        $notification = Notification::create([
            'title' => $data['title'],
            'message' => $data['message'],
            'url' => $data['url'] ?? null,
        ]);

        $userIds = $data['user_ids'] ?? [];
        $roleIds = $data['role_ids'] ?? [];

        // 2️⃣ Role bazlı kullanıcılar
        if (!empty($roleIds)) {
            $roles = Role::whereIn('id', $roleIds)->get();
            foreach ($roles as $role) {
                $role->users->each(function ($user) use (&$userIds) {
                    if (!in_array($user->id, $userIds)) {
                        $userIds[] = $user->id;
                    }
                });
            }
        }

        // 3️⃣ notification_user tablosuna kaydet
        foreach ($userIds as $userId) {
            $notification->users()->attach($userId, ['sent_at' => now()]);
        }

        // 4️⃣ OneSignal push gönder
        $players = User::whereIn('id', $userIds)->pluck('onesignal_player_id')->filter()->toArray();

        if (!empty($players)) {
            $this->oneSignal->notifications->add([
                'include_player_ids' => $players,
                'headings' => ['en' => $notification->title],
                'contents' => ['en' => $notification->message],
                'url' => $notification->url
            ]);
        }

        return $notification;
    }
    public function setOneSignalPlayerId(array $data)
    {
        User::find($data['user_id'])->update([
            'onesignal_player_id' => $data['onesignal_player_id'],
        ]);
    }
}
