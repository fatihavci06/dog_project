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
        // Paylaştığınız sınıfın constructor yapısına göre:
        // __construct($appId, $restApiKey, $userAuthKey, ...)
        $this->oneSignal = new OneSignalClient(
            env('ONESIGNAL_APP_ID'),
            env('ONESIGNAL_API_KEY'),
            env('ONESIGNAL_USER_AUTH_KEY')
        );
    }

    public function sendNotification(array $data)
    {
        // 1️⃣ Bildirim veritabanı kaydı
        $notification = Notification::create([
            'title' => $data['title'],
            'message' => $data['message'],
            'url' => $data['url'] ?? null,
        ]);

        $userIds = $data['user_ids'] ?? [];
        $roleIds = $data['role_ids'] ?? [];

        // 2️⃣ Role bazlı kullanıcıları çekme (SQL hatası düzeltilmiş hali)
        if (!empty($roleIds)) {
            $roleUserIds = User::whereHas('roles', function($q) use ($roleIds) {
                $q->whereIn('roles.id', $roleIds);
            })->pluck('users.id')->toArray();

            $userIds = array_unique(array_merge($userIds, $roleUserIds));
        }

        // 3️⃣ Pivot tabloya kaydetme
        if (!empty($userIds)) {
            $notification->users()->syncWithPivotValues($userIds, ['sent_at' => now()]);
        }

        // 4️⃣ OneSignal Gönderimi (DÜZELTİLEN KISIM)
        $players = User::whereIn('id', $userIds)
                       ->whereNotNull('onesignal_player_id')
                       ->pluck('onesignal_player_id')
                       ->toArray();

        if (!empty($players)) {
            // "notifications->add" YERİNE "sendNotificationCustom" kullanıyoruz.
            // Paylaştığınız sınıfın 324. satırındaki metod.
            $this->oneSignal->sendNotificationCustom([
                'include_player_ids' => $players,
                'headings'           => ['en' => $data['title']],
                'contents'           => ['en' => $data['message']],
                'url'                => $data['url'] ?? null
            ]);
        }

        return $notification;
    }

    public function setOneSignalPlayerId(array $data)
    {
        $user = User::find($data['user_id']);
        if ($user) {
            $user->update([
                'onesignal_player_id' => $data['onesignal_player_id'],
            ]);
        }
    }

    public function changeNotificationStatus($userId, $status)
    {
        $user = User::find($userId);
        if ($user) {
            $user->notification_status = $status;
            $user->save();
        }
    }
}
