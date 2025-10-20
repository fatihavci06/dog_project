<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Log::info('=== BROADCAST SERVICE PROVIDER BOOT ===');

        // Web routes için (CSRF token ile)
        Broadcast::routes([
            'middleware' => ['web'] // CSRF token için web middleware kullan
        ]);

        // API routes için (JWT ile) - isterseniz ekleyin
        Broadcast::routes([
            'prefix' => 'api',
            'middleware' => ['api', \App\Http\Middleware\JwtMiddleware::class]
        ]);

        Log::info('Broadcast routes registered');

        require base_path('routes/channels.php');
    }
}
