<?php

namespace App\Providers;

use App\Services\QuestionService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->singleton(QuestionService::class, function ($app) {
            return new QuestionService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive(); // Bootstrap 5 için
        View::composer('layouts.partials.navbar', function ($view) {
        if (Auth::check()) {
            $userId = Auth::id();


            // Son 5 gelen mesaj
            $messages = Message::with('sender')
                ->where('receiver_id', $userId)
                ->latest()
                ->take(5)
                ->get();

            // Okunmamış mesaj sayısı
            $unreadCount = Message::where('receiver_id', $userId)
                ->whereNull('read_at')
                ->count();

            $view->with([
                'messages' => $messages,
                'unreadCount' => $unreadCount,
            ]);
        } else {
            $view->with([
                'messages' => collect(),
                'unreadCount' => 0,
            ]);
        }
    });

        //
    }
}
