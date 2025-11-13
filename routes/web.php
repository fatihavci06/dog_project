<?php

use App\Http\Controllers\AgeRangeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvailabilityForMeetupController;
use App\Http\Controllers\BreadController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DogController;
use App\Http\Controllers\HealthInfoController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\LookingForController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MobilAppPageInfoController;
use App\Http\Controllers\MobileAppInformationsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TravelRadiusController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VibeController;
use App\Http\Controllers\GenericCrudController;
use App\Http\Controllers\MobileAppStepInfoController;
use App\Http\Controllers\WebAnnouncmentController;
use App\Http\Controllers\WebAuthController;
use App\Http\Middleware\AdminMiddleware;
use App\Models\MobileAppInformation;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
Route::get('/reset-password', [WebAuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [WebAuthController::class, 'resetPasswordSubmit'])->name('password.update');

Route::middleware([AdminMiddleware::class])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::get('/dog-list', [DogController::class, 'dogList'])->name('dogs');
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/questionnaire/{id}', [UserController::class, 'questionnaireShow'])->name('questionnaire.show');
    Route::get('/messages/latest', [ChatController::class, 'latest'])
        ->name('messages.latest');
    Route::prefix('announcements')->name('announcements.')->group(function () {
        Route::get('list', [WebAnnouncmentController::class, 'index'])->name('index');
        Route::get('show', [WebAnnouncmentController::class, 'show'])->name('show');
        Route::post('store', [WebAnnouncmentController::class, 'store'])->name('store');
        Route::put('update/{announcement}', [WebAnnouncmentController::class, 'update'])->name('update');
        Route::delete('delete/{announcement}', [WebAnnouncmentController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('locations')->name('locations.')->group(function () {
        Route::get('/add', [LocationController::class, 'create'])->name('create');
        Route::post('/', [LocationController::class, 'store'])->name('store');
        Route::delete('/{location}', [LocationController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/create', [NotificationController::class, 'create'])->name('notifications.create');
        Route::post('/store', [NotificationController::class, 'store'])->name('notifications.store');
    });

    Route::prefix('mobile-app-information')->group(function () {
        Route::get('/page-info', [MobilAppPageInfoController::class, 'pageInfo'])->name('pageInfo.index');
        Route::post('/page-info/{id}', [MobilAppPageInfoController::class, 'update'])->name('pageInfo.update');
        Route::get('/mobile-steps', [MobileAppStepInfoController::class, 'index'])->name('mobileSteps.index');
        Route::post('/mobile-steps/{id}', [MobileAppStepInfoController::class, 'update'])->name('mobileSteps.update');
    });


    Route::prefix('messages')->group(function () {
        Route::post('messages/start', [ChatController::class, 'start'])->name('messages.start');

        Route::get('/', [ChatController::class, 'index'])->name('messages.index');
        Route::get('/{conversation}', [ChatController::class, 'show'])->name('messages.show');
        Route::post('/{conversation}', [ChatController::class, 'store'])->name('messages.store');
        Route::post('conversations/{id}/mark-read', [ChatController::class, 'markRead'])->name('message.markRead');
        Route::get('/{conversation}/load-more', [ChatController::class, 'loadMore'])->name('messages.loadMore');
        Route::get('/users/get-others', [ChatController::class, 'getOtherUsers'])->name('users.getOtherUsers');
    });
    Route::get('/{model}', [GenericCrudController::class, 'index'])->name('generic.index');
    Route::post('/{model}', [GenericCrudController::class, 'store'])->name('generic.store');
    Route::post('/{model}/{id}', [GenericCrudController::class, 'update'])->name('generic.update');
    Route::delete('/{model}/{id}', [GenericCrudController::class, 'destroy'])->name('generic.destroy');
});
