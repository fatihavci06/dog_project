<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DogController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebAnnouncmentController;
use App\Http\Controllers\WebAuthController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;



Route::middleware([AdminMiddleware::class])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::get('/dog-list', [DogController::class, 'dogList'])->name('dogs');
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/questionnaire/{id}', [UserController::class, 'questionnaireShow'])->name('questionnaire.show');

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
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
Route::get('/reset-password', [WebAuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [WebAuthController::class, 'resetPasswordSubmit'])->name('password.update');
