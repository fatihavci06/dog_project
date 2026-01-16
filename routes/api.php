<?php

use App\Http\Controllers\ApiPlanController;
use App\Http\Controllers\ApiSupportController;
use App\Http\Controllers\ApiFeedBackController;
use App\Http\Controllers\ApiAnnouncmentController;
use App\Http\Controllers\ApiNotificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiCalendarController;
use App\Http\Controllers\ApiChatController;
use App\Http\Controllers\ApiDateController;
use App\Http\Controllers\ApiFavoriteController;
use App\Http\Controllers\ApiFriendshipController;
use App\Http\Controllers\ApiLocationController;
use App\Http\Controllers\ApiLanguageController;
use App\Http\Controllers\ApiMobilAppRegisterInformationController;
use App\Http\Controllers\ApiProfileShareController;
use App\Http\Controllers\ApiPupMatchController;
use App\Http\Controllers\ApiPupProfileController;
use App\Http\Controllers\ApiScreenController;
use App\Http\Controllers\DogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PupProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ScreenController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'loginApi']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->name('verification.verify');
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::get('/support/{lang}', [ApiSupportController::class, 'index']);
Route::post('/logout', [AuthController::class, 'logoutApi']);
Route::prefix('question')->group(function () {
    Route::get('list/{language?}', [QuestionController::class, 'index']);
    Route::post('answer-save', [QuestionController::class, 'userQuestionAnswerUpdateOrCreate']);
});
Route::get('/screen/{id}/{language?}', [ApiScreenController::class, 'getScreen']);
Route::middleware([JwtMiddleware::class])->group(function () {

    Route::post('/feedback/send', [ApiFeedBackController::class, 'store']);
    Route::get('/feedback/list', [ApiFeedBackController::class, 'index']);
    Route::get('/pup/{pupProfileId}/discover', [ApiPupMatchController::class, 'matches']);
    Route::get('/discover/show/{id}', [ApiPupMatchController::class, 'showProfile']); //id pup profile id

    Route::post('/friend/send',   [ApiFriendshipController::class, 'send']);
    Route::post('/friend/accept', [ApiFriendshipController::class, 'accept']);
    Route::post('/friend/reject', [ApiFriendshipController::class, 'reject']);
    Route::get('/friends',        [ApiFriendshipController::class, 'friends']);
    Route::get('/friend/incoming', [ApiFriendshipController::class, 'incoming']);
    Route::get('/friend/outgoing', [ApiFriendshipController::class, 'outgoing']);
    Route::post('/friend/unfriend', [ApiFriendshipController::class, 'unfriend']);
    Route::post('/friend/cancel-friend-request', [ApiFriendshipController::class, 'cancelFriendRequest']);
    Route::get('/friend/info', [ApiFriendshipController::class, 'totalMatchAndChats']);
    // Favorites
    Route::post('/favorite/add',    [ApiFavoriteController::class, 'add']);
    Route::post('/favorite/remove', [ApiFavoriteController::class, 'remove']);
    Route::get('/favorite/list',    [ApiFavoriteController::class, 'list']);

    Route::get('users', [AuthController::class, 'index']);
    Route::post('my-profile/change-password', [AuthController::class, 'changePassword']);
    Route::get('my-profile', [AuthController::class, 'myProfile']);
    Route::delete('my-profile/delete', [AuthController::class, 'deleteProfile']);
    Route::put('my-profile/update', [AuthController::class, 'myProfileUpdate']);
    Route::get('/survey/{pupProfile}', [ApiPupProfileController::class, 'questionsWithAnswers']);
    Route::get('/my-pups', [ApiPupProfileController::class, 'myPups']);
    Route::get('/my-pup/show/{id}', [ApiPupProfileController::class, 'myPupShow']);
    Route::get('pup/{pupId}/survey-answers', [ApiPupProfileController::class, 'getAnswers']);
    Route::put('pup/{pupId}/survey/update', [ApiPupProfileController::class, 'updateSurvey']);
    Route::delete('pup/delete/{pupId}', [ApiPupProfileController::class, 'destroy']);
    Route::post('pup/create', [ApiPupProfileController::class, 'store']);
    Route::put('pup/update/{id}', [ApiPupProfileController::class, 'update']);
    Route::get('/profile/share-qr', [ApiProfileShareController::class, 'generate']);
    Route::prefix('dates')->group(function () {
        // Listeleme
        Route::get('incoming', [ApiDateController::class, 'incoming']); // Bana gelenler
        Route::get('outgoing', [ApiDateController::class, 'outgoing']); // Benim gönderdiklerim
        Route::get('approved-list', [ApiDateController::class, 'list']); // Benim gönderdiklerim
        Route::get('approved-detail', [ApiDateController::class, 'getApprovedDateById']);
        // İşlemler
        Route::post('send', [ApiDateController::class, 'store']);      // Yeni teklif gönder
        Route::post('cancel', [ApiDateController::class, 'cancel']);   // İptal et (Sender)
        Route::post('approve', [ApiDateController::class, 'approve']); // Onayla (Receiver)
        Route::post('reject', [ApiDateController::class, 'reject']);   // Reddet (Receiver)

        Route::get('/edit', [ApiDateController::class, 'edit']);

        Route::put('/update', [ApiDateController::class, 'update']);

        Route::delete('/delete', [ApiDateController::class, 'delete']);
    });
    Route::prefix('test')->group(function () {
        Route::get('get/{test_id}', [QuestionController::class, 'testGet']);
        Route::post('update/{test_id}', [QuestionController::class, 'userQuestionAnswerUpdateOrCreate'])->name('test.update');
    });
    Route::prefix('announcments')->group(function () {
        Route::get('list', [ApiAnnouncmentController::class, 'index']);
        Route::get('show/{id}', [ApiAnnouncmentController::class, 'show']);
    });

    Route::prefix('plans')->group(function () {
        Route::get('list', [ApiPlanController::class, 'index']);
        Route::post('create', [ApiPlanController::class, 'store']);
        Route::put('update/{id}', [ApiPlanController::class, 'update']);
        Route::delete('delete/{id}', [ApiPlanController::class, 'destroy']);
        Route::get('show/{id}', [ApiPlanController::class, 'show']);
    });
    Route::prefix('location')->group(function () {
        Route::get('list', [ApiLocationController::class, 'index']);
    });
    Route::prefix('chat')->group(function () {

        Route::get('conversations/{id}/messages', [ApiChatController::class, 'messages']);
        Route::post('messages/send', [ApiChatController::class, 'send']);
        Route::post('conversations/{id}/mark-read', [ApiChatController::class, 'markRead'])->name('chat.markRead');
        Route::get('/inbox', [ApiChatController::class, 'inbox']);
        Route::get('user-pup-profile/list', [ApiChatController::class, 'userPupProfileList']);
    });




    Route::post('onesignal-playerid/set', [ApiNotificationController::class, 'setOneSignalPlayerId']);
    Route::get('notification/list', [ApiNotificationController::class, 'notificationsList']);
    Route::post('notification-status/change', [ApiNotificationController::class, 'changeNotificationStatus']);
    Route::put('/notifications/{id}/read', [ApiNotificationController::class, 'markAsRead']);
    Route::post('language/change', [ApiLanguageController::class, 'changeLanguageStatus']);
});



Route::prefix('mobile-app-informations')->group(function () {

    Route::get('/step-by-step-info/{language?}', [ApiMobilAppRegisterInformationController::class, 'stepByStepInfo'])
        ->name('mobile_app_informations.index');
    Route::get('/page-info/{language?}', [ApiMobilAppRegisterInformationController::class, 'pageInfo'])
        ->name('mobile_app_informations.pageInfo');
    Route::get('/basic-info/{language?}', [ApiMobilAppRegisterInformationController::class, 'basicInfo'])
        ->name('mobile_app_informations.basicInfo');
});


Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
