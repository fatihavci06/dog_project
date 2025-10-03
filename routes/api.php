<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DogController;
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
});


Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
