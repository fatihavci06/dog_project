<?php


use App\Http\Controllers\ApiAnnouncmentController;
use App\Http\Controllers\ApiNotificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiCalendarController;
use App\Http\Controllers\ApiChatController;
use App\Http\Controllers\ApiLocationController;
use App\Http\Controllers\ApiMessageController;
use App\Http\Controllers\ApiMobilAppRegisterInformationController;
use App\Http\Controllers\ApiPupProfileController;
use App\Http\Controllers\DogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PupProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'loginApi']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->name('verification.verify');
Route::post('/refresh', [AuthController::class, 'refresh']);

Route::post('/logout', [AuthController::class, 'logoutApi']);
Route::prefix('question')->group(function () {
    Route::get('list/{language?}', [QuestionController::class, 'index']);
    Route::post('answer-save', [QuestionController::class, 'userQuestionAnswerUpdateOrCreate']);
});
Route::middleware([JwtMiddleware::class])->group(function () {
    Route::get('users', [AuthController::class, 'index']);
    Route::post('my-profile/change-password', [AuthController::class, 'changePassword']);
    Route::get('my-profile', [AuthController::class, 'myProfile']);
    Route::delete('my-profile/delete', [AuthController::class, 'deleteProfile']);
    Route::put('my-profile/update', [AuthController::class, 'myProfileUpdate']);
    Route::get('/survey/{pupProfile}', [ApiPupProfileController::class, 'questionsWithAnswers']);
    Route::get('/my-pups/{locale}', [ApiPupProfileController::class, 'myPups']);
    Route::get('pup/{pupId}/survey-answers/{locale}', [ApiPupProfileController::class, 'getAnswers']);
    Route::put('pup/{pupId}/survey/update/{locale}', [ApiPupProfileController::class, 'updateSurvey']);
    Route::delete('pup/delete/{pupId}', [ApiPupProfileController::class, 'destroy']);
    Route::post('pup/create', [ApiPupProfileController::class, 'store']);
    Route::put('pup/update/{id}', [ApiPupProfileController::class, 'update']);
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
        Route::post('conversations/{id}/mark-read', [ApiChatController::class, 'markRead'])->name('chat.markRead');
        Route::get('/inbox', [ApiChatController::class, 'inbox']);
    });




    Route::post('onesignal-playerid/set', [ApiNotificationController::class, 'setOneSignalPlayerId']);
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
