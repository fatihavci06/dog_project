<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Role;
use Berkayk\OneSignal\OneSignalFacade;

class NotificationService
{
    public function sendNotification(array $data)
    {
        // 1️⃣ Bildirim oluştur
        $notification = Notification::create([
            'title'   => $data['title'],
            'message' => $data['message'],
            'url'     => $data['url'] ?? null,
        ]);

        $userIds = $data['user_ids'] ?? [];
        $roleIds = $data['role_ids'] ?? [];

        // 2️⃣ Role bazlı kullanıcıları tek sorguda al (Optimization)
        if (!empty($roleIds)) {
            // whereHas ile role sahip user id'lerini direkt çekiyoruz
            $roleUserIds = User::whereHas('roles', function($q) use ($roleIds) {
                $q->whereIn('id', $roleIds);
            })->pluck('id')->toArray();

            $userIds = array_unique(array_merge($userIds, $roleUserIds));
        }

        if (empty($userIds)) {
            return $notification;
        }

        // 3️⃣ notification_user tablosuna TEK SEFERDE kaydet (Bulk Insert)
        // Döngü yerine syncWithoutDetaching veya attach kullanabiliriz.
        $notification->users()->syncWithPivotValues($userIds, ['sent_at' => now()]);

        // 4️⃣ OneSignal push gönder
        // Sadece OneSignal ID'si olanları filtrele
        $players = User::whereIn('id', $userIds)
                       ->whereNotNull('onesignal_player_id')
                       ->pluck('onesignal_player_id')
                       ->toArray();

        if (!empty($players)) {
            // Berkayk kütüphanesinin doğru metod kullanımı:
            OneSignalFacade::sendNotificationCustom([
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
        // Null check eklenmesi iyi olur
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
