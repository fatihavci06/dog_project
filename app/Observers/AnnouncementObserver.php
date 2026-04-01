<?php

namespace App\Observers;

use App\Jobs\SendOneSignalNotification;
use App\Models\Announcement;
use App\Models\User;

class AnnouncementObserver
{
    /**
     * Yeni bir duyuru oluşturulduğunda çalışır.
     */
    public function created(Announcement $announcement): void
    {
        $this->sendPushNotification($announcement, 'created');
    }

    /**
     * Bir duyuru güncellendiğinde çalışır.
     */
    public function updated(Announcement $announcement): void
    {
        $this->sendPushNotification($announcement, 'updated');
    }

    /**
     * Duyuruya ait kullanıcıları bulup push notification gönderir.
     */
    private function sendPushNotification(Announcement $announcement, string $action): void
    {
        // OneSignal player ID'si olan kullanıcıları getir
        $query = User::whereNotNull('onesignal_player_id');

        if (!is_null($announcement->role_id)) {
            $query->where('role_id', $announcement->role_id);
        }

        // Kullanıcıları dil tercihlerine göre gruplayalım
        $users = $query->get(['onesignal_player_id', 'preferred_language']);

        if ($users->isEmpty()) {
            return;
        }

        $groupedUsers = $users->groupBy(function ($user) {
            return $user->preferred_language ?? 'tr';
        });

        foreach ($groupedUsers as $lang => $langUsers) {
            $playerIds = $langUsers->pluck('onesignal_player_id')->filter()->values()->toArray();

            if (empty($playerIds)) {
                continue;
            }

            // Dil bazlı başlıkları belirle
            $titleKey = $action === 'created' 
                ? 'notifications.announcement_created_title' 
                : 'notifications.announcement_updated_title';
            
            // Laravel'in çeviri sistemini kullanarak başlığı al (belirtilen dilde)
            $title = __($titleKey, [], $lang);

            SendOneSignalNotification::dispatch(
                $playerIds,
                $title,
                $announcement->title,
                [
                    'type'            => 'announcement',
                    'announcement_id' => $announcement->id,
                    'url'             => 'pupcrawl://announcement/' . $announcement->id,
                ]
            );
        }
    }
}
