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
        // role_id null ise → tüm kullanıcılara gönder
        $query = User::whereNotNull('onesignal_player_id');

        if (!is_null($announcement->role_id)) {
            $query->where('role_id', $announcement->role_id);
        }

        $playerIds = $query->pluck('onesignal_player_id')->filter()->values()->toArray();

        if (empty($playerIds)) {
            return;
        }

        $title = $action === 'created'
            ? '📢 Yeni Duyuru'
            : '🔄 Duyuru Güncellendi';

        SendOneSignalNotification::dispatch(
            $playerIds,
            $title,
            $announcement->title,
            [
                'type'            => 'announcement',
                'announcement_id' => $announcement->id,
            ]
        );
    }
}
