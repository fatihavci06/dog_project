<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;

class SendOneSignalNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $playerIds;
    public $title;
    public $body;
    public $data;

    public function __construct(array $playerIds, string $title, string $body, array $data = [])
    {
        $this->playerIds = $playerIds;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    public function handle()
    {
        if (empty($this->playerIds)) return;

        $client = new Client();
        $res = $client->post('https://onesignal.com/api/v1/notifications', [
            'headers' => [
                'Authorization' => 'Basic ' . env('ONESIGNAL_API_KEY'),
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'app_id' => env('ONESIGNAL_APP_ID'),
                'include_player_ids' => $this->playerIds,
                'headings' => ['en' => $this->title],
                'contents' => ['en' => $this->body],
                'data' => $this->data,
                'android_channel_id' => null
            ],
            'timeout' => 10
        ]);
       $type = $this->data['type'] ?? 'info';

if ($type !== 'message') {
    // 2. Bildirimi oluştur ve dönen objeyi değişkene ata
    $notification = Notification::create([
        'title'   => $this->title,
        'message' => $this->body,
        'type'    => $type,
        'url'     => $this->data['url'] ?? null,
    ]);

    // 3. Kullanıcıyı bul (last() yerine latest()->first() veya direkt first())
    $user = User::where('onesignal_player_id', $this->playerIds[0])->latest()->first();

    // 4. Eğer her iki obje de varsa pivot kaydını oluştur
    if ($notification && $user) {
        NotificationUser::create([
            'notification_id' => $notification->id, // latest() sorgusuna gerek kalmadı
            'user_id'         => $user->id,
            'is_read'         => false,
            'sent_at'         => now(),
        ]);
    }
}
}
