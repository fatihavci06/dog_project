<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Eğer giriş yapılmamışsa login sayfasına yönlendir
        if (!$user) {
            return redirect()->route('login');
        }

        // Eğer admin değilse logout ve login sayfasına hata mesajı ile gönder
         $isAdmin = DB::table('role_user')
                ->where('user_id', $user->id)
                ->where('role_id', 1)
                ->exists();

        if (!$isAdmin) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Bu sayfaya erişim yetkiniz yok.'
            ]);
        }

        return $next($request);
    }
}
