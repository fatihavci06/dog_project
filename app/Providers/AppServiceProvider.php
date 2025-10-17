<?php

namespace App\Providers;

use App\Services\QuestionService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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

        //
    }
}
