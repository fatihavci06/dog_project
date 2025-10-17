<?php


use App\Http\Controllers\ApiAnnouncmentController;
use App\Http\Controllers\ApiNotificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiCalendarController;
use App\Http\Controllers\ApiChatController;
use App\Http\Controllers\ApiLocationController;
use App\Http\Controllers\ApiMessageController;
use App\Http\Controllers\DogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QuestionController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'loginApi']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->name('verification.verify');
Route::post('/refresh', [AuthController::class, 'refresh']);

Route::post('/logout', [AuthController::class, 'logoutApi']);

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::get('users', [AuthController::class, 'index']);
    Route::get('my-profile', [AuthController::class, 'myProfile']);
    Route::post('my-profile/update', [AuthController::class, 'myProfileUpdate']);
    Route::prefix('question')->group(function () {
        Route::get('list', [QuestionController::class, 'index']);
        Route::post('answer-save', [QuestionController::class, 'userQuestionAnswerUpdateOrCreate']);
    });
    Route::prefix('dog')->group(function () {
        Route::get('mylist', [DogController::class, 'myList']);
        Route::delete('delete/{dog_id}', [DogController::class, 'delete']);
        Route::get('show/{dog_id}', [DogController::class, 'show']);

        Route::post('update/{dog_id}', [DogController::class, 'update']);
    });
    Route::prefix('test')->group(function () {
        Route::get('get/{test_id}', [QuestionController::class, 'testGet']);
        Route::post('update/{test_id}', [QuestionController::class, 'userQuestionAnswerUpdateOrCreate'])->name('test.update');
    });
    Route::prefix('announcments')->group(function () {
        Route::get('list', [ApiAnnouncmentController::class, 'index']);
        Route::get('show/{id}', [ApiAnnouncmentController::class, 'show']);
    });

    Route::prefix('calendar')->group(function () {
        Route::get('list', [ApiCalendarController::class, 'index']);
        Route::post('create', [ApiCalendarController::class, 'store']);
        Route::put('update/{id}', [ApiCalendarController::class, 'update']);
        Route::delete('delete/{id}', [ApiCalendarController::class, 'destroy']);
        Route::get('show/{id}', [ApiCalendarController::class, 'show']);
    });
    Route::prefix('location')->group(function () {
        Route::get('list', [ApiLocationController::class, 'index']);
    });
    Route::prefix('chat')->group(function () {

        Route::get('conversations/{id}/messages', [ApiChatController::class, 'messages']);
        Route::post('messages/send', [ApiChatController::class, 'send']);
        Route::post('conversations/{id}/mark-read', [ApiChatController::class, 'markRead']);
        Route::get('/inbox', [ApiChatController::class, 'inbox']);

    });

Route::post('/test/broadcast-message', [TestBroadcastController::class, 'sendTestMessage']);


    Route::post('onesignal-playerid/set', [ApiNotificationController::class, 'setOneSignalPlayerId']);
});


Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
