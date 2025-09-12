<?php

use App\Http\Controllers\AuthController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;



Route::middleware([AdminMiddleware::class])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
