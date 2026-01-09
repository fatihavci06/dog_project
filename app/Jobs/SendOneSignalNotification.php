<?php

namespace App\Jobs;

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

        $appId = config('services.onesignal.app_id') ?? env('ONESIGNAL_APP_ID');
        $apiKey = config('services.onesignal.api_key') ?? env('ONESIGNAL_API_KEY');

        // Deep Link URL'ini data içinden alıyoruz
        $targetUrl = $this->data['url'] ?? null;

        $client = new \GuzzleHttp\Client();

        $client->post('https://onesignal.com/api/v1/notifications', [
            'headers' => [
                'Authorization' => 'Basic ' . $apiKey,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'app_id' => $appId,
                'include_player_ids' => $this->playerIds,
                'headings' => ['en' => $this->title],
                'contents' => ['en' => $this->body],

                // 1. Yöntem: Standart uygulama açma URL'i
                'app_url' => $targetUrl,

                // 2. Yöntem: Uygulama içinden okumak için data objesi
                'data' => array_merge($this->data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // Eğer Flutter kullanıyorsan gerekebilir
                    'url' => $targetUrl
                ]),

                'android_channel_id' => null,
                'ios_attachments' => null, // Gerekirse görsel eklenebilir
            ],
            'timeout' => 10
        ]);
    }
}
