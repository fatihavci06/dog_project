<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebAuthController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;



Route::middleware([AdminMiddleware::class])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
Route::get('/reset-password', [WebAuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [WebAuthController::class, 'resetPasswordSubmit'])->name('password.update');
