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
use App\Http\Controllers\WebAnnouncmentController;
use App\Http\Controllers\WebAuthController;
use App\Http\Middleware\AdminMiddleware;
use App\Models\MobileAppInformation;
use Illuminate\Support\Facades\Route;



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
    Route::prefix('breads')->group(function () {
        Route::get('/', [BreadController::class, 'index'])->name('breads.index');
        Route::get('/create', [BreadController::class, 'create'])->name('breads.create');
        Route::post('/store', [BreadController::class, 'store'])->name('breads.store');
        Route::delete('/delete/{id}', [BreadController::class, 'delete'])->name('breads.delete');
        Route::put('/update/{id}', [BreadController::class, 'update'])->name('breads.update');
    });
    Route::prefix('age-range')->group(function () {
        Route::get('/', [AgeRangeController::class, 'index'])->name('ageRange.index');
        Route::get('/create', [AgeRangeController::class, 'create'])->name('ageRange.create');
        Route::post('/store', [AgeRangeController::class, 'store'])->name('ageRange.store');
        Route::delete('/delete/{id}', [AgeRangeController::class, 'delete'])->name('ageRange.delete');
        Route::put('/update/{id}', [AgeRangeController::class, 'update'])->name('ageRange.update');
    });
    Route::prefix('looking-for')->group(function () {
        Route::get('/', [LookingForController::class, 'index'])->name('lookingFor.index');
        Route::get('/create', [LookingForController::class, 'create'])->name('lookingFor.create');
        Route::post('/store', [LookingForController::class, 'store'])->name('lookingFor.store');
        Route::delete('/delete/{id}', [LookingForController::class, 'delete'])->name('lookingFor.delete');
        Route::put('/update/{id}', [LookingForController::class, 'update'])->name('lookingFor.update');
    });
    Route::prefix('vibe')->group(function () {
        Route::get('/', [VibeController::class, 'index'])->name('vibe.index');
        Route::get('/create', [VibeController::class, 'create'])->name('vibe.create');
        Route::post('/store', [VibeController::class, 'store'])->name('vibe.store');
        Route::delete('/delete/{id}', [VibeController::class, 'delete'])->name('vibe.delete');
        Route::put('/update/{id}', [VibeController::class, 'update'])->name('vibe.update');
    });
     Route::prefix('health-info')->group(function () {
        Route::get('/', [HealthInfoController::class, 'index'])->name('healthInfo.index');
        Route::get('/create', [HealthInfoController::class, 'create'])->name('healthInfo.create');
        Route::post('/store', [HealthInfoController::class, 'store'])->name('healthInfo.store');
        Route::delete('/delete/{id}', [HealthInfoController::class, 'delete'])->name('healthInfo.delete');
        Route::put('/update/{id}', [HealthInfoController::class, 'update'])->name('healthInfo.update');
    });
    Route::prefix('travel-radius')->group(function () {
        Route::get('/', [TravelRadiusController::class, 'index'])->name('travelRadius.index');
        Route::get('/create', [TravelRadiusController::class, 'create'])->name('travelRadius.create');
        Route::post('/store', [TravelRadiusController::class, 'store'])->name('travelRadius.store');
        Route::delete('/delete/{id}', [TravelRadiusController::class, 'delete'])->name('travelRadius.delete');
        Route::put('/update/{id}', [TravelRadiusController::class, 'update'])->name('travelRadius.update');
    });
    Route::prefix('availability-for-meetups')->group(function () {
        Route::get('/', [AvailabilityForMeetupController::class, 'index'])->name('availabilityForMeetups.index');
        Route::get('/create', [AvailabilityForMeetupController::class, 'create'])->name('availabilityForMeetups.create');
        Route::post('/store', [AvailabilityForMeetupController::class, 'store'])->name('availabilityForMeetups.store');
        Route::delete('/delete/{id}', [AvailabilityForMeetupController::class, 'delete'])->name('availabilityForMeetups.delete');
        Route::put('/update/{id}', [AvailabilityForMeetupController::class, 'update'])->name('availabilityForMeetups.update');
    });
    Route::prefix('mobile-app-information')->group(function () {
        Route::get('/', [MobileAppInformationsController::class, 'index'])->name('mobileAppInformation.index');
        Route::post('/', [MobileAppInformationsController::class, 'update'])->name('mobileAppInformation.update');

        Route::get('/page-info', [MobilAppPageInfoController::class, 'pageInfo'])->name('mobileAppInformation.pageInfo');
        Route::post('/page-info', [MobilAppPageInfoController::class, 'pageInfoUpdate'])->name('mobileAppInformation.pageInfoUpdate');
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
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
Route::get('/reset-password', [WebAuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [WebAuthController::class, 'resetPasswordSubmit'])->name('password.update');
